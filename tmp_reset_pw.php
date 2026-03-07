<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = 'superadmin@esertifikat.id'; // Ganti jika perlu
$newPassword = 'password'; // Password baru yang akan diset

$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    $user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
    $user->save();
    echo "Password untuk user {$email} berhasil direset ke: {$newPassword}" . PHP_EOL;
}
else {
    echo "User dengan email {$email} tidak ditemukan." . PHP_EOL;
}
