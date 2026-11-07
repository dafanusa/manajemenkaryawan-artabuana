<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminUser = env('SUPERADMIN_USERNAME', 'superadmin');
        $superAdminPass = env('SUPERADMIN_PASSWORD', 'password');

        $adminUser = env('ADMIN_USERNAME', 'admin');
        $adminPass = env('ADMIN_PASSWORD', 'password');

        // 1. Super Admin Account
        User::updateOrCreate(
            ['role' => 'super_admin'],
            [
                'name' => 'Super Admin HRD',
                'email' => $superAdminUser,
                'password' => Hash::make($superAdminPass),
                'role' => 'super_admin',
                'status' => 'active',
            ]
        );

        // 2. Admin HRD Account
        User::updateOrCreate(
            ['role' => 'admin'],
            [
                'name' => 'Admin HRD',
                'email' => $adminUser,
                'password' => Hash::make($adminPass),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}
