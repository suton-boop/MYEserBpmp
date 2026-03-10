<?php

namespace App\Domain\Certificates\Services;

use App\Models\SignerCertificate;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class KeyManagerService
{
    public function createSignerCertificate(
        string \,
        string \,
        string \,
        string \,
        int \,
        ?string \ = null,
        ?string \ = null,
        ?string \ = null
    ): SignerCertificate {
        \ = Crypt::encryptString(\);

        \ = hash('sha256', \->normalizePem(\));

        return SignerCertificate::query()->create([
            'id' => (string) Str::uuid(),
            'code' => \,
            'name' => \,
            'public_key_pem' => \,
            'private_key_encrypted' => \,
            'private_key_fingerprint' => \,
            'is_active' => true,
            'valid_from' => \,
            'valid_to' => \,
            'rotated_from_id' => \,
            'created_by' => \,
        ]);
    }

    public function deactivate(string \): void
    {
        SignerCertificate::query()->whereKey(\)->update(['is_active' => false]);
    }

    public function getActiveByCode(string \): SignerCertificate
    {
        return SignerCertificate::query()
            ->where('code', \)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function loadPrivateKeyPem(SignerCertificate \): string
    {
        return Crypt::decryptString(\->private_key_encrypted);
    }

    public function rotate(
        string \,
        string \,
        string \,
        string \,
        string \,
        int \
    ): SignerCertificate {
        \ = \->getActiveByCode(\);
        \->deactivate(\->id);

        return \->createSignerCertificate(
            \,
            \,
            \,
            \,
            \,
            rotatedFromId: \->id
        );
    }

    private function normalizePem(string \): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", \));
    }
}