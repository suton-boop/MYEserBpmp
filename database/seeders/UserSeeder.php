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
        $users = [
            [
                'role_id' => 1,
                'name' => 'Super Admin',
                'email' => 'superadmin@local.test',
            ],
            [
                'role_id' => 2,
                'name' => 'Admin GM1',
                'email' => 'admingm1@gmail.com',
            ],
            [
                'role_id' => 2,
                'name' => 'Admin GM2',
                'email' => 'admingm2@gmail.com',
            ],
            [
                'role_id' => 2,
                'name' => 'Admin GM3',
                'email' => 'admingm3@gmail.com',
            ],
            [
                'role_id' => 2,
                'name' => 'Admin GM 4',
                'email' => 'admingm4@gmail.com',
            ],
            [
                'role_id' => 2,
                'name' => 'Admin GM 5',
                'email' => 'admingm5@gmail.com',
            ],
            [
                'role_id' => 3,
                'name' => 'Operator GM 1',
                'email' => 'optgm1@gmail.com',
            ],
            [
                'role_id' => 3,
                'name' => 'Operator GM 2',
                'email' => 'optgm2@gmail.com',
            ],
            [
                'role_id' => 3,
                'name' => 'Operator GM 3',
                'email' => 'optgm3@gmail.com',
            ],
            [
                'role_id' => 3,
                'name' => 'Operator GM 4',
                'email' => 'optgm4@gmail.com',
            ],
            [
                'role_id' => 3,
                'name' => 'Operator GM 5',
                'email' => 'optgm5@gmail.com',
            ],
            [
                'role_id' => 3,
                'name' => 'Perpustakaan',
                'email' => 'perpus@gmail.com',
            ],
            [
                'role_id' => 4,
                'name' => 'Soejo Adi Poernomo,S.S.;M.Pd',
                'email' => 'cendana36@gmail.com',
            ],
            [
                'role_id' => 4,
                'name' => 'Dr. Jarwoko,M.Pd',
                'email' => 'jarwoko19@gmai.com',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'role_id' => $userData['role_id'],
                'password' => Hash::make('password16'), // Password default sesuai permintaan
            ]
            );
        }
    }
}
