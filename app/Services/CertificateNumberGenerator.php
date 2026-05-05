<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Support\Facades\DB;

class CertificateNumberGenerator
{
    public static function generate(int $year): array
    {
        return DB::transaction(function () use ($year) {
            $reuseDeleted = \App\Models\Setting::getValue('reuse_deleted_numbers', false);
            $nextSeq = null;

            if ($reuseDeleted) {
                $existingSeqs = Certificate::where('year', $year)
                    ->lockForUpdate()
                    ->pluck('sequence')
                    ->toArray();

                $maxSeq = empty($existingSeqs) ? 0 : max($existingSeqs);

                // Find the first gap in the sequences
                for ($i = 1; $i <= $maxSeq; $i++) {
                    if (!in_array($i, $existingSeqs)) {
                        $nextSeq = $i;
                        break;
                    }
                }
            }

            if ($nextSeq === null) {
                // Default behavior
                $lastSeq = Certificate::where('year', $year)
                    ->lockForUpdate()
                    ->max('sequence');
                $nextSeq = ($lastSeq ?? 0) + 1;
            }

            $prefix = str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

            $number = "{$prefix}/Sertifikat/BPMPKALTIM/{$year}";

            return [
                'certificate_number' => $number,
                'year' => $year,
                'sequence' => $nextSeq,
            ];
        });
    }
}
