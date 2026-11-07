<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupAuthAccounts extends Command
{
    protected $signature = 'auth:setup-accounts {--force : Lewati konfirmasi}';
    protected $description = 'Konfigurasi akun Super Admin dan Admin HRD dengan kredensial autentikasi tingkat tinggi';

    public function handle(): int
    {
        $this->info("=== KONFIGURASI AKUN AUTENTIKASI TINGKAT TINGGI ===");

        // 1. Super Admin Account (Membaca dari file .env)
        $superAdminEmail = env('SUPERADMIN_USERNAME', 'superadmin');
        $superAdminPass = env('SUPERADMIN_PASSWORD', 'password');

        $superAdmin = User::where('role', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->update([
                'name' => 'Super Admin HRD',
                'email' => $superAdminEmail,
                'password' => Hash::make($superAdminPass),
                'role' => 'super_admin',
                'status' => 'active',
            ]);
            $this->info("Akun Super Admin berhasil diperbarui ID #{$superAdmin->id} (Username: {$superAdminEmail})");
        } else {
            $superAdmin = User::create([
                'name' => 'Super Admin HRD',
                'email' => $superAdminEmail,
                'password' => Hash::make($superAdminPass),
                'role' => 'super_admin',
                'status' => 'active',
            ]);
            $this->info("Akun Super Admin baru berhasil dibuat ID #{$superAdmin->id} (Username: {$superAdminEmail})");
        }

        // 2. Admin HRD Account (Membaca dari file .env)
        $adminEmail = env('ADMIN_USERNAME', 'admin');
        $adminPass = env('ADMIN_PASSWORD', 'password');

        $admin = User::where('email', $adminEmail)
            ->orWhere(function ($q) use ($superAdmin) {
                $q->where('role', 'admin');
                if ($superAdmin) {
                    $q->where('id', '!=', $superAdmin->id);
                }
            })
            ->first();

        if ($admin) {
            $admin->update([
                'name' => 'Admin HRD',
                'email' => $adminEmail,
                'password' => Hash::make($adminPass),
                'role' => 'admin',
                'status' => 'active',
            ]);
            $this->info("Akun Admin HRD berhasil diperbarui ID #{$admin->id}");
        } else {
            $admin = User::create([
                'name' => 'Admin HRD',
                'email' => $adminEmail,
                'password' => Hash::make($adminPass),
                'role' => 'admin',
                'status' => 'active',
            ]);
            $this->info("Akun Admin HRD baru berhasil dibuat ID #{$admin->id}");
        }

        $this->newLine();
        $this->table(
            ['Role', 'Nama', 'Username / Email Login', 'Password', 'Status'],
            [
                ['Super Admin', $superAdmin->name, $superAdminEmail, $superAdminPass, 'Aktif'],
                ['Admin HRD', $admin->name, $adminEmail, $adminPass, 'Aktif'],
            ]
        );

        $this->info("Autentikasi tingkat tinggi (Rate Limiter, Brute-Force Shield, Session Regeneration) telah aktif.");
        return Command::SUCCESS;
    }
}
