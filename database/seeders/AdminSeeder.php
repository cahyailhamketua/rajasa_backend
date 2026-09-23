<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed the admin users.
     */
    public function run(): void
    {
        $admins = [
            [
                'username' => 'cahyailham',
                'password' => 'admin123',
                'nama_lengkap' => 'Cahya Ilham',
                'email' => 'cahyailham811@gmail.com',
                'nickname' => 'Ilham',
                'role' => 'admin',
            ],
            [
                'username' => 'fauzulakmal',
                'password' => 'admin123',
                'nama_lengkap' => 'Fauzul Akmal',
                'email' => 'fauzulakmal@gmail.com',
                'nickname' => 'Akmal',
                'role' => 'admin',
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                [
                    'username' => $admin['username'],
                ],
                $admin
            );
        }
    }
}