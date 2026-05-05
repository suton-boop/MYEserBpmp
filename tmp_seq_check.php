<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use App\Models\Certificate;

$seqs = Certificate::where('year', date('Y'))->pluck('sequence')->toArray();
var_dump($seqs);

$settings = Setting::all()->toArray();
var_dump($settings);
