<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$stats = [
    'total' => \App\Models\Certificate::count(),
    'approved' => \App\Models\Certificate::where('status', 'approved')->count(),
    'final_generated' => \App\Models\Certificate::where('status', 'final_generated')->count(),
    'signed' => \App\Models\Certificate::where('status', 'signed')->count(),
    'pdf_not_null' => \App\Models\Certificate::whereNotNull('pdf_path')->count(),
    'signed_not_null' => \App\Models\Certificate::whereNotNull('signed_pdf_path')->count(),
    'max_sequence' => \App\Models\Certificate::max('sequence'),
];

print_r($stats);

$certificates = \App\Models\Certificate::orderBy('sequence')->get(['id', 'sequence', 'status', 'pdf_path', 'signed_pdf_path']);
foreach ($certificates as $c) {
    echo "ID: {$c->id} | Seq: {$c->sequence} | Status: {$c->status} | PDF: " . ($c->pdf_path ? 'Y' : 'N') . " | Signed: " . ($c->signed_pdf_path ? 'Y' : 'N') . PHP_EOL;
}
