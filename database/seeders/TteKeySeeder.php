<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Certificates\Services\KeyManagerService;
use Illuminate\Support\Facades\File;

class TteKeySeeder extends Seeder
{
    public function run(KeyManagerService $keys): void
    {
        $adminId = 1;

        $keys->createSignerCertificate(
            code: 'SIGNER-001',
            name: 'Signer Utama',
            publicKeyPem: File::get(storage_path('app/tte/keys/signer_public.pem')),
            privateKeyPem: File::get(storage_path('app/tte/keys/signer_private.pem')),
            createdBy: $adminId
        );

        $keys->createSignerCertificate(
            code: 'TSA-001',
            name: 'Internal TSA',
            publicKeyPem: File::get(storage_path('app/tte/keys/tsa_public.pem')),
            privateKeyPem: File::get(storage_path('app/tte/keys/tsa_private.pem')),
            createdBy: $adminId
        );
    }
}