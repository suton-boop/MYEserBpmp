<?php

namespace App\Services\Tte;

use App\Models\Certificate;
use App\Models\SignerCertificate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

class TteService
{
    /**
     * Sign certificate by embedding QR into PDF (generate new signed pdf file).
     * Return: ['signed_pdf_path' => ..., 'token' => ...]
     */
    public function signCertificate(
        int $certificateId,
        string $signerCertCode,
        int $signedBy,
        string $ip,
        string $userAgent,
        array $appearance = []
    ): array {
        $cert = Certificate::query()->findOrFail($certificateId);

        if (!$cert->pdf_path) {
            throw new \RuntimeException("PDF belum ada (pdf_path kosong). Generate PDF dulu.");
        }

        if (!Storage::disk('public')->exists($cert->pdf_path)) {
            throw new \RuntimeException("File PDF tidak ditemukan di storage public: {$cert->pdf_path}");
        }

        $signer = SignerCertificate::query()
            ->where('code', $signerCertCode)
            ->where('is_active', true)
            ->first();

        if (!$signer) {
            throw new \RuntimeException("Signer tidak ditemukan / tidak aktif: {$signerCertCode}");
        }

        // URL verifikasi publik
        $verifyUrl = route('public.verify.show', ['code' => $cert->verify_token]);

        // 1) Generate QR PNG (BINARY) via chillerlan
        $pngBinary = $this->makeQrPngBinary($verifyUrl, (int)($appearance['qr_scale'] ?? 5), $cert->id);

        // 2) Simpan PNG sementara
        $qrTmpPath = "tmp/qr/{$cert->id}-" . Str::random(8) . ".png";
        Storage::disk('public')->put($qrTmpPath, $pngBinary);

        // 3) Tentukan input/output PDF
        $inputPdfAbs  = Storage::disk('public')->path($cert->pdf_path);
        $signedRel    = $this->buildSignedPath($cert->pdf_path);
        $outputPdfAbs = Storage::disk('public')->path($signedRel);

        $outDir = dirname($outputPdfAbs);
        if (!is_dir($outDir)) {
            @mkdir($outDir, 0777, true);
        }

        // 4) Stamp QR ke PDF menggunakan Multi-Placement
        $placements = !empty($appearance['placements']) ? $appearance['placements'] : [
            [
                'page' => (int)($appearance['page'] ?? 1),
                'x' => (float)($appearance['x'] ?? 20),
                'y' => (float)($appearance['y'] ?? 160),
                'w' => (float)($appearance['w'] ?? 35),
                'h' => (float)($appearance['h'] ?? 35),
                'tte_visible' => (bool)($appearance['tte_visible'] ?? true),
                'barcode_visible' => (bool)($appearance['barcode_visible'] ?? true),
            ]
        ];

        $this->stampPdfWithQrMulti(
            $inputPdfAbs,
            $outputPdfAbs,
            Storage::disk('public')->path($qrTmpPath),
            $placements,
            $signer->name ?? $signer->code
        );

        // 5) Hapus QR temp
        Storage::disk('public')->delete($qrTmpPath);

        // 6) Update sertifikat
        $token = (string) Str::uuid();

        $cert->update([
            'signed_pdf_path' => $signedRel,
            'signed_at'       => now(),
            'status'          => 'signed',
        ]);

        Log::info('TTE_SIGN_OK', [
            'cert_id' => $cert->id,
            'signed_pdf_path' => $signedRel,
        ]);

        return [
            'signed_pdf_path' => $signedRel,
            'token' => $token,
        ];
    }

    private function makeQrPngBinary(string $text, int $scale, int $certId): string
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException("ext-gd tidak aktif. Aktifkan GD untuk generate PNG QR.");
        }

        $options = new QROptions([
            'outputType'      => QRCode::OUTPUT_IMAGE_PNG,
            'outputInterface' => QRGdImagePNG::class,
            'scale'           => max(2, $scale),
            'returnResource'  => false,
            'imageBase64'     => false,
        ]);

        $raw = (new QRCode($options))->render($text);

        // Normalisasi output menjadi BINARY PNG
        if (is_string($raw) && str_starts_with($raw, 'data:image/png;base64,')) {
            $raw = base64_decode(substr($raw, strlen('data:image/png;base64,')), true);
        }

        if (is_string($raw) && !str_starts_with($raw, "\x89PNG\r\n\x1a\n")) {
            $decoded = base64_decode($raw, true);
            if (is_string($decoded) && str_starts_with($decoded, "\x89PNG\r\n\x1a\n")) {
                $raw = $decoded;
            }
        }

        if (!is_string($raw) || !str_starts_with($raw, "\x89PNG\r\n\x1a\n")) {
            $debugPath = 'tmp/qr/debug-raw-' . $certId . '-' . Str::random(6) . '.txt';
            Storage::disk('public')->put($debugPath, is_string($raw) ? $raw : json_encode($raw));
            throw new \RuntimeException("QR generator tidak menghasilkan PNG valid. Debug: storage/app/public/{$debugPath}");
        }

        return $raw;
    }

    private function buildSignedPath(string $originalPdfPath): string
    {
        $ext  = pathinfo($originalPdfPath, PATHINFO_EXTENSION);
        $base = substr($originalPdfPath, 0, -(strlen($ext) + 1));
        return $base . '-signed.' . $ext;
    }

    private function stampPdfWithQr(
        string $inputPdfAbs,
        string $outputPdfAbs,
        string $qrPngAbs,
        bool $barcodeVisible,
        bool $tteVisible,
        array $appearance,
        string $signerName
    ): void {
        // 4) Stamp QR ke PDF menggunakan Multi-Placement
        $placements = !empty($appearance['placements']) ? $appearance['placements'] : [
            [
                'page' => (int)($appearance['page'] ?? 1),
                'x' => (float)($appearance['x'] ?? 20),
                'y' => (float)($appearance['y'] ?? 160),
                'w' => (float)($appearance['w'] ?? 35),
                'h' => (float)($appearance['h'] ?? 35),
                'tte_visible' => (bool)($appearance['tte_visible'] ?? true),
                'barcode_visible' => (bool)($appearance['barcode_visible'] ?? true),
            ]
        ];

        $this->stampPdfWithQrMulti(
            $inputPdfAbs,
            $outputPdfAbs,
            $qrPngAbs,
            $placements,
            $signerName
        );
    }

    private function stampPdfWithQrMulti(
        string $inputPdfAbs,
        string $outputPdfAbs,
        string $qrPngAbs,
        array $placements,
        string $signerName
    ): void {
        if (!class_exists(Fpdi::class)) {
            throw new \RuntimeException("FPDI belum terpasang. Install: composer require setasign/fpdi setasign/fpdf");
        }

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pageCount = $pdf->setSourceFile($inputPdfAbs);

        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl  = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            foreach ($placements as $placement) {
                $pPage = (int)($placement['page'] ?? 1);
                if ($i === $pPage) {
                    $xMm = (float)($placement['x'] ?? 20);
                    $yMm = (float)($placement['y'] ?? 160);
                    $wMm = (float)($placement['w'] ?? 35);
                    $hMm = (float)($placement['h'] ?? 35);
                    $barcodeVisible = (bool)($placement['barcode_visible'] ?? true);
                    $tteVisible = (bool)($placement['tte_visible'] ?? true);

                    if ($barcodeVisible) {
                        $pdf->Image($qrPngAbs, $xMm, $yMm, $wMm, $hMm);
                    }

                    if ($tteVisible) {
                        $pdf->SetFont('Helvetica', '', 8);
                        $pdf->SetXY($xMm, $yMm + $hMm + 2);
                        $pdf->Cell($wMm, 4, "TTE: {$signerName}", 0, 0, 'C');
                    }
                }
            }
        }

        $pdf->Output('F', $outputPdfAbs);
    }
}
