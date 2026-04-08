<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$job = Illuminate\Support\Facades\DB::table('jobs')->first();
if($job) {
    echo "ID: " . $job->id . "\n";
    echo "Queue: " . $job->queue . "\n";
    echo "Attempts: " . $job->attempts . "\n";
    echo "Reserved: " . ($job->reserved_at ? date('Y-m-d H:i:s', $job->reserved_at) : 'No') . "\n";
    echo "Available At: " . date('Y-m-d H:i:s', $job->available_at) . "\n";
    echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
    echo "Payload: " . substr($job->payload, 0, 200) . "...\n";
} else {
    echo "No jobs found\n";
}
