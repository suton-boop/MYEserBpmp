<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Roles
        $superadmin   = Role::firstOrCreate(['name' => 'superadmin']);
        $pemilikEvent = Role::firstOrCreate(['name' => 'pemilik_event']);
        $operator     = Role::firstOrCreate(['name' => 'operator']);

        // 2) Permissions (update + 5 menu baru)
        $permNames = [
            'dashboard-read',
            'event-manage',
            'participant-manage',
            'template-manage',
            'certificate-generate',
            'certificate-send',
            'report-read',
            'user-manage',
            'role-manage',
            'permission-manage',

            // ✅ tambahan baru
            'certificate-approve',
            'tte-manage',
            'monitoring-read',
            'audit-read',
        ];

        $perms = [];
        foreach ($permNames as $name) {
            $perms[$name] = Permission::firstOrCreate(['name' => $name]);
        }

        // 3) Assign permissions to roles
        // superadmin: all permissions
        $superadmin->permissions()->sync(collect($perms)->pluck('id')->all());

        // pemilik_event: semua fitur e-sertifikat
        $pemilikEvent->permissions()->sync([
            $perms['dashboard-read']->id,
            $perms['event-manage']->id,
            $perms['participant-manage']->id,
            $perms['template-manage']->id,
            $perms['certificate-generate']->id,
            $perms['certificate-send']->id,
            $perms['report-read']->id,

            // ✅ tambahan baru
            $perms['certificate-approve']->id,
            $perms['tte-manage']->id,
            $perms['monitoring-read']->id,
            $perms['audit-read']->id,
        ]);

        // operator: input peserta + generate/kirim
        $operator->permissions()->sync([
            $perms['dashboard-read']->id,
            $perms['participant-manage']->id,
            $perms['certificate-generate']->id,
            $perms['certificate-send']->id,

            // monitoring opsional
            // $perms['monitoring-read']->id,
        ]);

        // 4) Buat user superadmin default
        User::firstOrCreate(
            ['email' => 'superadmin@local.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role_id' => $superadmin->id,
            ]
        );
    }
}
