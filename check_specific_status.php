<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Certificate;

$approvedCount = Certificate::where('status', 'approved')->count();
echo "Total Approved certificates: " . $approvedCount . "\n";

$generatingCount = Certificate::where('status', 'generating')->count();
echo "Total Generating (stuck) certificates: " . $generatingCount . "\n";
