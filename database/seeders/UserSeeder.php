<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Email dan password bisa Anda sesuaikan sendiri di bawah ini
        User::updateOrCreate(
        ['email' => 'superadmin@esertifikat.id'],
        [
            'name' => 'Super Admin',
            'password' => Hash::make('admin123'),
            'role_id' => 1, // ID 1 adalah role 'superadmin' di database Anda
        ]
        );
    }
}
