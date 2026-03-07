<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$certs = \App\Models\Certificate::whereNotNull('sequence')
    ->orderBy('sequence')
    ->get(['id', 'sequence', 'status', 'pdf_path', 'signed_pdf_path']);

echo "ID | Sequence | Status | PDF | Signed\n";
echo str_repeat('-', 40) . "\n";
foreach ($certs as $c) {
    echo "{$c->id} | {$c->sequence} | {$c->status} | " . ($c->pdf_path ? 'Yes' : 'No') . " | " . ($c->signed_pdf_path ? 'Yes' : 'No') . "\n";
}

echo "\nTotal count with sequence: " . $certs->count() . "\n";
echo "Max sequence: " . \App\Models\Certificate::max('sequence') . "\n";
echo "Missing sequences: ";
$used = $certs->pluck('sequence')->toArray();
$max = max($used ?: [0]);
for ($i = 1; $i <= $max; $i++) {
    if (!in_array($i, $used)) {
        echo "$i ";
    }
}
echo "\n";
