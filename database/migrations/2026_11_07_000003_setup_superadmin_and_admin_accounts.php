<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Super Admin Account (Membaca dari file .env)
        $superAdminUser = env('SUPERADMIN_USERNAME', 'superadmin');
        $superAdminPass = env('SUPERADMIN_PASSWORD', 'password');

        $superAdmin = User::where('role', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->update([
                'name' => 'Super Admin HRD',
                'email' => $superAdminUser,
                'password' => Hash::make($superAdminPass),
                'role' => 'super_admin',
                'status' => 'active',
            ]);
        } else {
            User::create([
                'name' => 'Super Admin HRD',
                'email' => $superAdminUser,
                'password' => Hash::make($superAdminPass),
                'role' => 'super_admin',
                'status' => 'active',
            ]);
        }

        // 2. Admin HRD Account (Membaca dari file .env)
        $adminUser = env('ADMIN_USERNAME', 'admin');
        $adminPass = env('ADMIN_PASSWORD', 'password');

        $admin = User::where('email', $adminUser)
            ->orWhere('role', 'admin')
            ->first();

        if ($admin) {
            $admin->update([
                'name' => 'Admin HRD',
                'email' => $adminUser,
                'password' => Hash::make($adminPass),
                'role' => 'admin',
                'status' => 'active',
            ]);
        } else {
            User::create([
                'name' => 'Admin HRD',
                'email' => $adminUser,
                'password' => Hash::make($adminPass),
                'role' => 'admin',
                'status' => 'active',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kept for account safety
    }
};
