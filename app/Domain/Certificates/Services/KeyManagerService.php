<?php

namespace App\Domain\Certificates\Services;

use App\Domain\Certificates\Models\SignerCertificate;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class KeyManagerService
{
    public function createSignerCertificate(
        string $code,
        string $name,
        string $publicKeyPem,
        string $privateKeyPem,
        int $createdBy,
        ?string $validFrom = null,
        ?string $validTo = null,
        ?string $rotatedFromId = null
    ): SignerCertificate {
        // SECURITY: Simpan private key terenkripsi (APP_KEY) di DB.
        // Untuk level lebih tinggi, simpan di HSM/Vault; interface ini bisa dipertahankan.
        $encrypted = Crypt::encryptString($privateKeyPem);

        $fingerprint = hash('sha256', $this->normalizePem($publicKeyPem));

        return SignerCertificate::query()->create([
            'id' => (string) Str::uuid(),
            'code' => $code,
            'name' => $name,
            'public_key_pem' => $publicKeyPem,
            'private_key_encrypted' => $encrypted,
            'private_key_fingerprint' => $fingerprint,
            'is_active' => true,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'rotated_from_id' => $rotatedFromId,
            'created_by' => $createdBy,
        ]);
    }

    public function deactivate(string $signerCertId): void
    {
        SignerCertificate::query()->whereKey($signerCertId)->update(['is_active' => false]);
    }

    public function getActiveByCode(string $code): SignerCertificate
    {
        return SignerCertificate::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function loadPrivateKeyPem(SignerCertificate $cert): string
    {
        // SECURITY: decrypt only in memory, jangan log.
        return Crypt::decryptString($cert->private_key_encrypted);
    }

    public function rotate(
        string $oldCertCode,
        string $newCode,
        string $newName,
        string $newPublicPem,
        string $newPrivatePem,
        int $createdBy
    ): SignerCertificate {
        $old = $this->getActiveByCode($oldCertCode);
        $this->deactivate($old->id);

        return $this->createSignerCertificate(
            $newCode,
            $newName,
            $newPublicPem,
            $newPrivatePem,
            $createdBy,
            rotatedFromId: $old->id
        );
    }

    private function normalizePem(string $pem): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", $pem));
    }
}