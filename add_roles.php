<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$roles = [
    'Pimpinan',
    'Pemilik Event',
    'Operator'
];

echo "--- MENAMBAHKAN ROLE BARU ---\n";

foreach ($roles as $roleName) {
    $exists = DB::table('roles')->where('name', $roleName)->exists();

    if (!$exists) {
        DB::table('roles')->insert([
            'name' => $roleName,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "[+] Role '$roleName' berhasil ditambahkan.\n";
    }
    else {
        echo "[.] Role '$roleName' sudah ada.\n";
    }
}

echo "--- SELESAI ---\n";
echo "Silakan Refresh halaman Kelola Role anda.\n";
