<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$template = \App\Models\CertificateTemplate::find(3);
if ($template) {
    echo "ID: " . $template->id . "\n";
    echo "Name: " . $template->name . "\n";
    echo "File Path: " . $template->file_path . "\n";
} else {
    echo "Template not found\n";
}
