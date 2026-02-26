<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Support\Facades\DB;

class CertificateNumberGenerator
{
    public static function generate(int $year): array
    {
        return DB::transaction(function () use ($year) {

            // Ambil sequence terakhir di tahun tersebut
            $lastSeq = Certificate::where('year', $year)
                ->lockForUpdate()
                ->max('sequence');

            $nextSeq = ($lastSeq ?? 0) + 1;

            $prefix = str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

            $number = "{$prefix}/Sertifikat/BPMP.Kaltim/{$year}";

            return [
                'certificate_number' => $number,
                'year' => $year,
                'sequence' => $nextSeq,
            ];
        });
    }
}
