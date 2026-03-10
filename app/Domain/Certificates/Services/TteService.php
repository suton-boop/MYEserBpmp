<?php

namespace App\Domain\Certificates\Services;

use App\Domain\Certificates\DTO\SignResult;
use App\Models\Certificate;
use App\Models\DigitalSignature;
use App\Models\SignerCertificate;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TCPDF;
use setasign\Fpdi\Tcpdf\Fpdi;

class TteService
{
    public function __construct(private
        KeyManagerService $keys, private
        AuditLogger $audit
        )
    {
    }

    public function signCertificate(
        Certificate $certificate,
        SignerCertificate $signerCert,
        int $signedBy,
        ?string $ip,
        ?string $userAgent
        ): DigitalSignature
    {
        if (!$certificate->isReadyForSigning()) {
            throw new \RuntimeException('Certificate not ready for signing.');
        }

        return DB::transaction(function () use ($certificate, $signerCert, $signedBy, $ip, $userAgent) {
            $pdfAbsolutePath = $this->absolutePdfPath($certificate->pdf_path);

            // SECURITY: hash PDF binary untuk anti manipulasi / replacement.
            $hashHex = hash_file(config('tte.security.hash_algo'), $pdfAbsolutePath);

            $privatePem = $this->keys->loadPrivateKeyPem($signerCert);

            $signatureBinary = '';
            $ok = openssl_sign(hex2bin($hashHex), $signatureBinary, $privatePem, config('tte.security.signature_algo'));
            if (!$ok) {
                throw new \RuntimeException('OpenSSL sign failed.');
            }
            $signatureBase64 = base64_encode($signatureBinary);

            $publicToken = Str::random(64); // token publik (bukan JWT)

            $tsaEnabled = (bool)config('tte.tsa.enabled');
            $tsaNonce = null;
            $tsaSigB64 = null;
            $tsaAt = null;

            if ($tsaEnabled) {
                [$tsaNonce, $tsaSigB64, $tsaAt] = $this->issueTsaToken($hashHex);
            }

            // Stamp PDF: QR + visible signature + metadata (detached signature reference).
            $stampedPdfPath = $this->stampPdfIfEnabled($certificate, $hashHex, $publicToken, $signatureBase64);

            // Update certificate
            $certificate->update([
                'pdf_path' => $stampedPdfPath,
                'pdf_checksum' => hash_file('sha256', $this->absolutePdfPath($stampedPdfPath)),
                'status' => 'signed',
                'signed_at' => now(),
            ]);

            $sig = DigitalSignature::query()->create([
                'id' => (string)Str::uuid(),
                'certificate_id' => $certificate->id,
                'signer_certificate_id' => $signerCert->id,
                'document_hash' => $hashHex,
                'signature_base64' => $signatureBase64,
                'signature_algo' => 'RSA-SHA256',
                'tsa_enabled' => $tsaEnabled,
                'tsa_at' => $tsaAt ?\Illuminate\Support\Carbon::instance(\DateTimeImmutable::createFromInterface($tsaAt)) : null,
                'tsa_nonce' => $tsaNonce,
                'tsa_signature_base64' => $tsaSigB64,
                'tsa_signer_code' => $tsaEnabled ? config('tte.tsa.signer_certificate_code') : null,
                'public_token' => $publicToken,
                'signed_at' => now(),
                'signed_by' => $signedBy,
                'signed_ip' => $ip,
                'signed_user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
            ]);

            $this->audit->log(
                'certificate.signed',
                $certificate->id,
                Certificate::class ,
            [
                'document_hash' => $hashHex,
                'signer_cert_code' => $signerCert->code,
                'public_token' => $publicToken,
                'tsa_enabled' => $tsaEnabled,
            ],
                $signedBy,
                $ip,
                $userAgent
            );

            return $sig;
        });
    }

    public function makeQrJwt(string $publicToken, string $certificateId): string
    {
        $now = time();
        $payload = [
            'iss' => config('tte.qr.issuer'),
            'iat' => $now,
            'exp' => $now + (int)config('tte.qr.jwt_ttl_seconds'),
            'jti' => Str::uuid()->toString(),
            'pt' => $publicToken,
            'cid' => $certificateId,
        ];

        $secret = $this->jwtSecret();
        return JWT::encode($payload, $secret, 'HS256');
    }

    private function stampPdfIfEnabled(Certificate $certificate, string $hashHex, string $publicToken, string $signatureBase64): string
    {
        if (!config('tte.pdf.stamp.enabled')) {
            return $certificate->pdf_path;
        }

        $src = $this->absolutePdfPath($certificate->pdf_path);

        $qrJwt = $this->makeQrJwt($publicToken, $certificate->id);
        $verifyUrl = \URL::signedRoute(
            config('tte.qr.verify_route_name'),
        ['token' => $publicToken, 'jwt' => $qrJwt],
            now()->addMinutes(config('tte.security.signed_url_ttl_minutes'))
        );

        // Generate QR PNG in memory (Endroid)
        $qrPngBinary = (new \Endroid\QrCode\QrCode($verifyUrl))
            ->setSize(220)
            ->setMargin(10)
            ->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh())
            ->setRoundBlockSizeMode(new \Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin())
            ->setForegroundColor(new \Endroid\QrCode\Color\Color(0, 0, 0))
            ->setBackgroundColor(new \Endroid\QrCode\Color\Color(255, 255, 255));

        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $qrResult = $writer->write($qrPngBinary);
        $qrBytes = $qrResult->getString();

        $outRelPath = $this->outputStampedPath($certificate->id);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($src);

        // SECURITY: Metadata berisi fingerprint & token referensi (detached signature)
        $pdf->SetCreator('TTE Internal');
        $pdf->SetAuthor('TTE Internal');
        $pdf->SetTitle($certificate->title);
        $pdf->SetSubject('Digitally signed (detached)');

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tpl = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tpl);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            if ($pageNo === 1) {
                // QR at bottom-right
                $tmpQr = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
                file_put_contents($tmpQr, $qrBytes);

                $w = 35;
                $h = 35;
                $x = $size['width'] - $w - 10;
                $y = $size['height'] - $h - 10;

                $pdf->Image($tmpQr, $x, $y, $w, $h, 'PNG');
                @unlink($tmpQr);

                // Visible signature image (optional)
                $sigImg = config('tte.pdf.stamp.signature_image_path');
                if ($sigImg && is_file($sigImg)) {
                    $pdf->Image($sigImg, 10, $size['height'] - 30, 50, 18);
                }

                // Visible text stamp
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetXY(10, $size['height'] - 10);
                $pdf->Cell(0, 5, 'Signed by TTE Internal | Hash: ' . substr($hashHex, 0, 16) . 'â€¦', 0, 1);

                // SECURITY: Invisible marker via hidden text (tiny white) â€“ bantu deteksi replacement.
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', '', 1);
                $pdf->Text(1, 1, 'TTE|' . $certificate->id . '|' . $publicToken . '|' . substr($signatureBase64, 0, 24));
                $pdf->SetTextColor(0, 0, 0);
            }
        }

        // Output
        $outAbs = $this->absolutePdfPath($outRelPath);
        @mkdir(dirname($outAbs), 0775, true);
        $pdf->Output($outAbs, 'F');

        return $outRelPath;
    }

    private function issueTsaToken(string $hashHex): array
    {
        $tsaCert = $this->keys->getActiveByCode(config('tte.tsa.signer_certificate_code'));
        $tsaPrivPem = $this->keys->loadPrivateKeyPem($tsaCert);

        $nonce = Str::random(32);
        $tsaAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $payload = json_encode([
            'hash' => $hashHex,
            'nonce' => $nonce,
            'tsa_at' => $tsaAt->format(DATE_ATOM),
        ], JSON_UNESCAPED_SLASHES);

        $sigBin = '';
        $ok = openssl_sign($payload, $sigBin, $tsaPrivPem, OPENSSL_ALGO_SHA256);
        if (!$ok) {
            throw new \RuntimeException('TSA signing failed.');
        }

        return [$nonce, base64_encode($sigBin), $tsaAt];
    }

    private function absolutePdfPath(string $relativePath): string
    {
        $disk = Storage::disk(config('tte.pdf.storage_disk'));
        // For local disk, path() is available.
        return $disk->path($relativePath);
    }

    private function outputStampedPath(string $certificateId): string
    {
        $root = trim(config('tte.pdf.pdf_root'), '/');
        return $root . '/signed/' . $certificateId . '-' . now()->format('YmdHis') . '.pdf';
    }

    private function jwtSecret(): string
    {
        $secret = config('tte.qr.jwt_secret');
        if (str_starts_with($secret, 'base64:')) {
            return base64_decode(substr($secret, 7));
        }
        return $secret;
    }
}