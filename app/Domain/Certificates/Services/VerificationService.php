<?php

namespace App\Domain\Certificates\Services;

use App\Domain\Certificates\Models\Certificate;
use App\Domain\Certificates\Models\DigitalSignature;
use App\Domain\Certificates\Models\SignerCertificate;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Storage;

class VerificationService
{
    public function __construct(
        private KeyManagerService $keys
    ) {}

    public function verifyByPublicToken(string $publicToken): array
    {
        $sig = DigitalSignature::query()
            ->where('public_token', $publicToken)
            ->with(['certificate', 'signerCertificate'])
            ->firstOrFail();

        return $this->verifySignatureRecord($sig);
    }

    public function verifySignatureRecord(DigitalSignature $sig): array
    {
        /** @var Certificate $cert */
        $cert = $sig->certificate;
        /** @var SignerCertificate $signerCert */
        $signerCert = $sig->signerCertificate;

        $pdfAbs = Storage::disk(config('tte.pdf.storage_disk'))->path($cert->pdf_path);

        $currentHash = hash_file(config('tte.security.hash_algo'), $pdfAbs);

        $integrityOk = hash_equals($sig->document_hash, $currentHash);

        $signatureOk = $this->verifyRsaSignature(
            $sig->document_hash,
            $sig->signature_base64,
            $signerCert->public_key_pem
        );

        $tsa = [
            'enabled' => (bool) $sig->tsa_enabled,
            'valid' => null,
            'tsa_at' => $sig->tsa_at?->toAtomString(),
            'tsa_nonce' => $sig->tsa_nonce,
            'tsa_signer_code' => $sig->tsa_signer_code,
        ];

        if ($sig->tsa_enabled) {
            $tsa['valid'] = $this->verifyTsaToken($sig->document_hash, $sig->tsa_nonce, $sig->tsa_at?->toAtomString(), $sig->tsa_signature_base64);
        }

        return [
            'certificate' => [
                'id' => $cert->id,
                'certificate_no' => $cert->certificate_no,
                'title' => $cert->title,
                'owner_name' => $cert->owner_name,
                'owner_identifier_masked' => $this->maskIdentifier($cert->owner_identifier),
                'status' => $cert->status,
                'signed_at' => $sig->signed_at->toAtomString(),
            ],
            'integrity' => [
                'expected_hash' => $sig->document_hash,
                'current_hash' => $currentHash,
                'ok' => $integrityOk,
            ],
            'signature' => [
                'algo' => $sig->signature_algo,
                'signer_cert_code' => $signerCert->code,
                'signer_fingerprint' => $signerCert->private_key_fingerprint,
                'ok' => $signatureOk,
            ],
            'timestamp' => $tsa,
            'overall_valid' => $integrityOk && $signatureOk && ($tsa['enabled'] ? (bool) $tsa['valid'] : true),
        ];
    }

    public function validateQrJwt(string $jwt, string $expectedPublicToken, string $expectedCertId): array
    {
        $secret = config('tte.qr.jwt_secret');
        if (str_starts_with($secret, 'base64:')) {
            $secret = base64_decode(substr($secret, 7));
        }

        $decoded = (array) JWT::decode($jwt, new Key($secret, 'HS256'));

        $ok = isset($decoded['pt'], $decoded['cid'])
            && hash_equals((string)$decoded['pt'], $expectedPublicToken)
            && hash_equals((string)$decoded['cid'], $expectedCertId);

        return ['ok' => $ok, 'claims' => $decoded];
    }

    private function verifyRsaSignature(string $hashHex, string $signatureBase64, string $publicPem): bool
    {
        $sigBin = base64_decode($signatureBase64, true);
        if ($sigBin === false) return false;

        $data = hex2bin($hashHex);
        if ($data === false) return false;

        $res = openssl_verify($data, $sigBin, $publicPem, config('tte.security.signature_algo'));
        return $res === 1;
    }

    private function verifyTsaToken(string $hashHex, ?string $nonce, ?string $tsaAtAtom, ?string $tsaSignatureBase64): bool
    {
        if (!$nonce || !$tsaAtAtom || !$tsaSignatureBase64) return false;

        $tsaCert = $this->keys->getActiveByCode(config('tte.tsa.signer_certificate_code'));

        $payload = json_encode([
            'hash' => $hashHex,
            'nonce' => $nonce,
            'tsa_at' => $tsaAtAtom,
        ], JSON_UNESCAPED_SLASHES);

        $sigBin = base64_decode($tsaSignatureBase64, true);
        if ($sigBin === false) return false;

        $res = openssl_verify($payload, $sigBin, $tsaCert->public_key_pem, OPENSSL_ALGO_SHA256);
        return $res === 1;
    }

    private function maskIdentifier(?string $id): ?string
    {
        if (!$id) return null;
        $len = mb_strlen($id);
        if ($len <= 6) return str_repeat('*', $len);
        return mb_substr($id, 0, 3) . str_repeat('*', $len - 6) . mb_substr($id, -3);
    }
}