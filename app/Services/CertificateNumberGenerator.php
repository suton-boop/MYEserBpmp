<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class CertificateNumberGenerator
{
    /**
     * Generate next sequence and certificate number for a single certificate.
     */
    public static function generate(int $year): array
    {
        return DB::transaction(function () use ($year) {
            $reuseDeleted = Setting::getValue('reuse_deleted_numbers', false);
            
            $existingSeqs = Certificate::where('year', $year)
                ->lockForUpdate()
                ->pluck('sequence')
                ->toArray();
                
            if (!$reuseDeleted) {
                $maxSeq = empty($existingSeqs) ? 0 : max($existingSeqs);
                $existingSeqs = $maxSeq > 0 ? range(1, $maxSeq) : [];
            }
            
            $takenSeqs = array_flip($existingSeqs);
            
            $seq = 1;
            while (isset($takenSeqs[$seq])) {
                $seq++;
            }
            
            $prefix = str_pad((string)$seq, 5, '0', STR_PAD_LEFT);
            $number = "{$prefix}/Sertifikat/BPMPKALTIM/{$year}";
            
            return [
                'certificate_number' => $number,
                'year' => $year,
                'sequence' => $seq,
            ];
        });
    }

    /**
     * Generate sequences and certificate numbers for a batch of certificates.
     * Modifies/Updates the certificates directly inside a transaction.
     */
    public static function approveBatch($certs, int $year, int $approvedById): void
    {
        DB::transaction(function () use ($certs, $year, $approvedById) {
            $reuseDeleted = Setting::getValue('reuse_deleted_numbers', false);
            
            $existingSeqs = Certificate::where('year', $year)
                ->lockForUpdate()
                ->pluck('sequence')
                ->toArray();
                
            if (!$reuseDeleted) {
                $maxSeq = empty($existingSeqs) ? 0 : max($existingSeqs);
                $existingSeqs = $maxSeq > 0 ? range(1, $maxSeq) : [];
            }
            
            $takenSeqs = array_flip($existingSeqs);
            
            $currentSearch = 1;
            foreach ($certs as $c) {
                while (isset($takenSeqs[$currentSearch])) {
                    $currentSearch++;
                }
                
                $seq = $currentSearch;
                $takenSeqs[$seq] = true;
                
                $prefix = str_pad((string)$seq, 5, '0', STR_PAD_LEFT);
                $number = "{$prefix}/Sertifikat/BPMPKALTIM/{$year}";
                
                $c->update([
                    'status' => Certificate::STATUS_APPROVED,
                    'year' => $year,
                    'sequence' => $seq,
                    'certificate_number' => $number,
                    'approved_at' => now(),
                    'approved_by' => $approvedById,
                ]);
            }
        });
    }
}
