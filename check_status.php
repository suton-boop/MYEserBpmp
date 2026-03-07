<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$certs = \App\Models\Certificate::get(['id', 'status', 'pdf_path', 'signed_pdf_path']);
foreach ($certs as $c) {
    echo "ID: {$c->id} | Status: {$c->status} | PDF: " . ($c->pdf_path ? 'Y' : 'N') . " | Signed: " . ($c->signed_pdf_path ? 'Y' : 'N') . PHP_EOL;
}
