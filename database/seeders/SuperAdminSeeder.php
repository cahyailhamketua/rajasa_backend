<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed the super admin users.
     */
    public function run(): void
    {
        $superAdmins = [
            [
                'username' => 'superadmin1',
                'password' => 'superadmin123',
                'nama_lengkap' => 'Super Administrator 1',
                'email' => 'superadmin1@rajasa.test',
                'nickname' => 'Super Admin 1',
                'role' => 'super_admin',
            ],
            [
                'username' => 'superadmin2',
                'password' => 'superadmin123',
                'nama_lengkap' => 'Super Administrator 2',
                'email' => 'superadmin2@rajasa.test',
                'nickname' => 'Super Admin 2',
                'role' => 'super_admin',
            ],
        ];

        foreach ($superAdmins as $superAdmin) {
            User::updateOrCreate(
                [
                    'username' => $superAdmin['username'],
                ],
                $superAdmin
            );
        }
    }
}