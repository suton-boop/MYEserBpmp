<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::with('role')->get();
foreach ($users as $u) {
    echo "ID: {$u->id} | Email: {$u->email} | Role: " . ($u->role->name ?? 'None') . PHP_EOL;
}
