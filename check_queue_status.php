<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Certificate;
use Illuminate\Support\Facades\DB;

$stats = Certificate::select('status', DB::raw('count(*) as total'))
    ->groupBy('status')
    ->get();

echo "Certificate Status Stats:\n";
foreach ($stats as $stat) {
    echo "- " . $stat->status . ": " . $stat->total . "\n";
}

$jobCount = DB::table('jobs')->count();
echo "\nTotal jobs in queue: " . $jobCount . "\n";
