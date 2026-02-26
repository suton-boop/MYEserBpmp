<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CertificateTemplate;

class CertificateTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Template Penghargaan', 'code' => 'P01'],
            ['name' => 'Template Sertifikat Diklat', 'code' => 'P02'],
            ['name' => 'Template Narasumber', 'code' => 'P03'],
            ['name' => 'Template Panitia', 'code' => 'P04'],
        ];

        foreach ($items as $it) {
            CertificateTemplate::updateOrCreate(
                ['code' => $it['code']],
                [
                    'name' => $it['name'],
                    'is_active' => true,
                    'settings' => null,
                    'created_by' => 1, // atau auth()->id() jika dipanggil manual saat login
                ]
            );
        }
    }
}
