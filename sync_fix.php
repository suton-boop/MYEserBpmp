<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Certificate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

DB::connection()->disableQueryLog();

$totalFixed = 0;
DB::transaction(function () use (&$totalFixed) {
    $year = 2026;
    $certs = Certificate::where('year', $year)
        ->whereNotNull('sequence')
        ->orderBy('sequence')
        ->get();

    $newSeq = 1;
    // Kita gunakan format konsisten: 5 digit / "Sertifikat/BPMP.Kaltim/2026"
    $basePrefix = 'Sertifikat/BPMP.Kaltim/' . $year;

    foreach ($certs as $c) {
        $formattedSeq = str_pad((string)$newSeq, 5, '0', STR_PAD_LEFT);
        $newNo = "{$formattedSeq}/{$basePrefix}";

        if ($c->sequence != $newSeq || $c->certificate_number != $newNo) {
            echo "Syncing ID {$c->id} (Sequence: {$c->sequence} -> {$newSeq} | Number: {$c->certificate_number} -> {$newNo})\n";
            $c->sequence = $newSeq;
            $c->certificate_number = $newNo;
            $c->save();
            $totalFixed++;
        }
        $newSeq++;
    }
});

echo "Total certificates renumbered/synced: {$totalFixed}\n";

// Trigger PDF Generation for those that don't have it but are already 'approved' or 'signed'
// We might want to re-generate ALL if the number changed, but let's at least fix the pending ones.
$pending = Certificate::whereIn('status', ['approved', 'final_generated', 'signed'])
    ->where(function ($q) {
        $q->whereNull('pdf_path')
            ->orWhereNull('signed_pdf_path');
    })
    ->get();

foreach ($pending as $p) {
    if (!$p->pdf_path && ($p->status == 'approved' || $p->status == 'final_generated')) {
        echo "Re-triggering PDF generation for ID {$p->id}\n";
        \App\Jobs\GenerateCertificatePdfJob::dispatch($p);
    }
}

echo "Synchronization complete.\n";
