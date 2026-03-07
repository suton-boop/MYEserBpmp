<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// List permission yang ada di config/menu.php
$permissionsArr = [
    'dashboard-read',
    'event-manage',
    'participant-manage',
    'template-manage',
    'certificate-generate',
    'certificate-send',
    'report-read',
    'certificate-approve',
    'tte-manage',
    'monitoring-read',
    'audit-read',
    'user-manage',
    'role-manage',
    'permission-manage',
];

echo "--- PROSES FIX PERMISSION SUPERADMIN (role_id=1) ---\n";

foreach ($permissionsArr as $name) {
    // 1. Pastikan permission ada di tabel 'permissions'
    DB::table('permissions')->updateOrInsert(
    ['name' => $name],
    ['created_at' => now(), 'updated_at' => now()]
    );

    $id = DB::table('permissions')->where('name', $name)->value('id');

    // 2. Hubungkan ke role_id 1 (Superadmin) di tabel 'role_permission'
    // Cek apakah sudah ada relasi
    $exists = DB::table('role_permission')
        ->where('role_id', 1)
        ->where('permission_id', $id)
        ->exists();

    if (!$exists) {
        // TABEL role_permission TIDAK PUNYA TIMESTAMPS BERDASARKAN MIGRASI
        DB::table('role_permission')->insert([
            'role_id' => 1,
            'permission_id' => $id
        ]);
        echo "[+] Permission '$name' diberikan ke Superadmin.\n";
    }
    else {
        echo "[.] Permission '$name' sudah ada.\n";
    }
}

echo "--- SELESAI ---\n";
echo "Silakan Refresh halaman admin anda.\n";
