<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\SystemSetting;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(GoogleDriveService $gdrive)
    {
        $settings = SystemSetting::pluck('value', 'key')->all();
        $gdriveRootId = config('services.google.drive_root_folder_id', env('GOOGLE_DRIVE_ROOT_FOLDER_ID'));
        $isOAuthConnected = $gdrive->isOAuthConnected();
        $isOAuthConfigured = $gdrive->isOAuthConfigured();

        return view('settings.index', compact('settings', 'gdriveRootId', 'isOAuthConnected', 'isOAuthConfigured'));
    }

    /**
     * Redirect to Google OAuth Consent Page
     */
    public function redirectToGoogle(GoogleDriveService $gdrive)
    {
        if (!$gdrive->isOAuthConfigured()) {
            return back()->with('error', 'GOOGLE_CLIENT_ID atau GOOGLE_CLIENT_SECRET belum diisi di file .env');
        }

        $currentHost = request()->getHttpHost();
        $scheme = request()->getScheme();
        $redirectUri = "{$scheme}://{$currentHost}/google/callback";

        session(['google_oauth_redirect_uri' => $redirectUri]);

        $client = $gdrive->createBaseOAuthClient($redirectUri);
        return redirect()->away($client->createAuthUrl());
    }

    /**
     * Handle Callback from Google OAuth
     */
    public function handleGoogleCallback(Request $request, GoogleDriveService $gdrive)
    {
        if ($request->has('error')) {
            return redirect()->route('settings.index')->with('error', 'Otorisasi Google dibatalkan: ' . $request->input('error'));
        }

        if (!$request->has('code')) {
            return redirect()->route('settings.index')->with('error', 'Tidak ada authorization code dari Google.');
        }

        try {
            $redirectUri = session('google_oauth_redirect_uri');
            $client = $gdrive->createBaseOAuthClient($redirectUri);
            $accessToken = $client->fetchAccessTokenWithAuthCode($request->input('code'));

            if (isset($accessToken['error'])) {
                throw new \RuntimeException("Google OAuth Error: " . ($accessToken['error_description'] ?? $accessToken['error']));
            }

            if (!empty($accessToken['refresh_token'])) {
                SystemSetting::set('google_drive_refresh_token', $accessToken['refresh_token']);
            }
            SystemSetting::set('google_drive_token_data', json_encode($accessToken));

            ActivityLog::record('connect_gdrive_oauth', 'Menghubungkan akun Google Drive dengan OAuth 2.0');

            return redirect()->route('settings.index')->with('success', 'Akun Google Drive berhasil terhubung! Kuota akun Google Anda kini aktif untuk upload berkas dan backup database.');
        } catch (\Throwable $e) {
            return redirect()->route('settings.index')->with('error', 'Gagal menghubungkan Google Drive: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect Google Drive OAuth
     */
    public function disconnectGoogle(GoogleDriveService $gdrive)
    {
        $gdrive->disconnectOAuth();
        ActivityLog::record('disconnect_gdrive_oauth', 'Memutuskan koneksi Google Drive OAuth');

        return back()->with('success', 'Koneksi akun Google Drive berhasil diputuskan.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'app_title' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email'],
            'company_phone' => ['nullable', 'string'],
            'company_address' => ['nullable', 'string'],
        ]);

        foreach ($validated as $key => $val) {
            SystemSetting::set($key, $val);
        }

        ActivityLog::record('update_settings', 'Memperbarui pengaturan sistem dan profil perusahaan');

        return back()->with('success', 'Pengaturan sistem berhasil disimpan!');
    }

    /**
     * Backup MySQL Database to Google Drive (Folder: 2. Backup SQL)
     */
    public function backupDatabaseToGoogleDrive(GoogleDriveService $gdrive)
    {
        try {
            $result = $gdrive->backupDatabase();

            ActivityLog::record('backup_database_gdrive', "Berhasil membuat cadangan database ke Google Drive ({$result['filename']})");

            return back()->with('success', "Database berhasil dicadangkan ke Google Drive! File: {$result['filename']}")
                         ->with('backup_link', $result['web_view_link']);
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal mencadangkan database ke Google Drive: " . $e->getMessage());
        }
    }

    /**
     * Bulk Sync existing employee documents to Google Drive hierarchy
     */
    public function syncAllDocumentsToGoogleDrive(GoogleDriveService $gdrive)
    {
        try {
            $employees = Employee::with('documents')->get();
            $syncedCount = 0;

            foreach ($employees as $employee) {
                // Ensure employee folder exists in GDrive
                $gdrive->getEmployeeFolderId($employee);

                foreach ($employee->documents as $doc) {
                    if (Storage::disk('public')->exists($doc->file_path)) {
                        $res = $gdrive->uploadEmployeeDocument($employee, $doc->document_type, $doc->file_path, $doc->original_name);
                        if ($res) {
                            $doc->google_drive_file_id = $res['file_id'];
                            $doc->google_drive_url = $res['web_view_link'];
                            $doc->saveQuietly();
                            $syncedCount++;
                        }
                    }
                }
            }

            ActivityLog::record('sync_documents_gdrive', "Sinkronisasi {$syncedCount} berkas karyawan ke Google Drive");

            return back()->with('success', "Sinkronisasi selesai! Berhasil mengunggah {$syncedCount} berkas karyawan ke Google Drive.");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal sinkronisasi ke Google Drive: " . $e->getMessage());
        }
    }
}
