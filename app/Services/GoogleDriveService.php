<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\SystemSetting;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleDriveService
{
    protected ?Client $client = null;
    protected ?Drive $driveService = null;
    protected ?string $rootFolderId = null;

    public function __construct()
    {
        $this->rootFolderId = config('services.google.drive_root_folder_id', env('GOOGLE_DRIVE_ROOT_FOLDER_ID'));
    }

    /**
     * Check if OAuth 2.0 is connected with a refresh token.
     */
    public function isOAuthConnected(): bool
    {
        $token = SystemSetting::get('google_drive_refresh_token');
        return !empty($token);
    }

    /**
     * Check if OAuth credentials (Client ID & Secret) are configured in .env.
     */
    public function isOAuthConfigured(): bool
    {
        $clientId = config('services.google.client_id', env('GOOGLE_CLIENT_ID'));
        $clientSecret = config('services.google.client_secret', env('GOOGLE_CLIENT_SECRET'));
        return !empty($clientId) && !empty($clientSecret);
    }

    /**
     * Create base Google Client for OAuth.
     */
    public function createBaseOAuthClient(?string $customRedirectUri = null): Client
    {
        $clientId = config('services.google.client_id', env('GOOGLE_CLIENT_ID'));
        $clientSecret = config('services.google.client_secret', env('GOOGLE_CLIENT_SECRET'));
        $redirectUri = $customRedirectUri ?: config('services.google.redirect_uri', env('GOOGLE_REDIRECT_URI', 'http://127.0.0.1:8000/google/callback'));

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');
        $client->setPrompt('consent select_account');
        $client->setApplicationName('Manajemen Karyawan Artha');

        return $client;
    }

    /**
     * Get OAuth Authorization URL to redirect user.
     */
    public function getAuthUrl(): string
    {
        $client = $this->createBaseOAuthClient();
        return $client->createAuthUrl();
    }

    /**
     * Exchange auth code for tokens and save to system settings.
     */
    public function authenticateWithCode(string $code): array
    {
        $client = $this->createBaseOAuthClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($accessToken['error'])) {
            throw new \RuntimeException("Google OAuth Error: " . ($accessToken['error_description'] ?? $accessToken['error']));
        }

        if (!empty($accessToken['refresh_token'])) {
            SystemSetting::set('google_drive_refresh_token', $accessToken['refresh_token']);
        }
        SystemSetting::set('google_drive_token_data', json_encode($accessToken));

        return $accessToken;
    }

    /**
     * Disconnect Google Drive OAuth.
     */
    public function disconnectOAuth(): void
    {
        SystemSetting::set('google_drive_refresh_token', '');
        SystemSetting::set('google_drive_token_data', '');
        $this->client = null;
        $this->driveService = null;
    }

    /**
     * Get or initialize Google Client using OAuth or Service Account Credentials.
     */
    public function getClient(): Client
    {
        if ($this->client !== null) {
            return $this->client;
        }

        // 1. Try OAuth 2.0 with Refresh Token (Preferred for Personal Drive quota)
        if ($this->isOAuthConfigured() && $this->isOAuthConnected()) {
            $client = $this->createBaseOAuthClient();
            $refreshToken = SystemSetting::get('google_drive_refresh_token');

            $tokenDataJson = SystemSetting::get('google_drive_token_data');
            $tokenData = $tokenDataJson ? json_decode($tokenDataJson, true) : [];

            $client->setAccessToken($tokenData);

            if ($client->isAccessTokenExpired()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (!isset($newToken['error'])) {
                    if (empty($newToken['refresh_token'])) {
                        $newToken['refresh_token'] = $refreshToken;
                    }
                    SystemSetting::set('google_drive_token_data', json_encode($newToken));
                }
            }

            $this->client = $client;
            return $this->client;
        }

        // 2. Fallback to Service Account Credentials
        $credentialsConfig = config('services.google.drive_credentials', env('GOOGLE_DRIVE_CREDENTIALS', 'manajemen-karwayan-artha-e1251b6d9176.json'));
        $credentialsPath = file_exists($credentialsConfig) ? $credentialsConfig : base_path($credentialsConfig);

        if (file_exists($credentialsPath)) {
            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope(Drive::DRIVE);
            $client->setApplicationName('Manajemen Karyawan Artha');

            $this->client = $client;
            return $this->client;
        }

        throw new \RuntimeException("Google Drive credentials belum dikonfigurasi. Hubungkan akun Google melalui OAuth atau sediakan file Service Account JSON.");
    }

    /**
     * Get or initialize Google Drive Service.
     */
    public function getDriveService(): Drive
    {
        if ($this->driveService !== null) {
            return $this->driveService;
        }

        $this->driveService = new Drive($this->getClient());
        return $this->driveService;
    }

    /**
     * Get the configured Root Folder ID.
     */
    public function getRootFolderId(): ?string
    {
        return $this->rootFolderId;
    }

    /**
     * Find or create a subfolder with given name under parentId.
     */
    public function getOrCreateFolder(string $folderName, ?string $parentId = null): string
    {
        $service = $this->getDriveService();
        $parentId = $parentId ?: $this->getRootFolderId();

        // Search if folder already exists under parent
        $escapedName = str_replace("'", "\\'", $folderName);
        $query = "mimeType = 'application/vnd.google-apps.folder' and name = '{$escapedName}' and trashed = false";
        if ($parentId) {
            $query .= " and '{$parentId}' in parents";
        }

        $results = $service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
            'pageSize' => 1,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        if (count($results->getFiles()) > 0) {
            return $results->getFiles()[0]->getId();
        }

        // Create folder
        $folderMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => $parentId ? [$parentId] : null,
        ]);

        $folder = $service->files->create($folderMetadata, [
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);

        return $folder->id;
    }

    /**
     * Get or create folder '1. Data Karyawan' under root.
     */
    public function getDataKaryawanFolderId(): string
    {
        return $this->getOrCreateFolder('1. Data Karyawan', $this->getRootFolderId());
    }

    /**
     * Get or create folder '2. Backup SQL' under root.
     */
    public function getBackupSqlFolderId(): string
    {
        return $this->getOrCreateFolder('2. Backup SQL', $this->getRootFolderId());
    }

    /**
     * Get or create folder '3. Sampah (Karyawan)' under root.
     */
    public function getTrashFolderId(): string
    {
        return $this->getOrCreateFolder('3. Sampah (Karyawan)', $this->getRootFolderId());
    }

    /**
     * Move an employee's folder from '1. Data Karyawan' to '3. Sampah (Karyawan)'
     * and rename it with deletion timestamp in WIT.
     */
    public function moveEmployeeToTrash(Employee $employee): ?string
    {
        if (!$this->isOAuthConnected() && !file_exists(config('services.google.service_account_credentials_path', storage_path('app/google-drive-credentials.json')))) {
            return null;
        }

        try {
            $service = $this->getDriveService();
            $folderId = $employee->google_drive_folder_id;

            // If employee does not have folder ID stored, try searching under '1. Data Karyawan'
            if (!$folderId) {
                $employeeCode = $employee->employee_id ?: ($employee->employee_code ?: ('EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT)));
                $folderName = "[{$employeeCode}] {$employee->full_name}";
                $parentDataFolderId = $this->getDataKaryawanFolderId();

                $escapedName = str_replace("'", "\\'", $folderName);
                $query = "mimeType = 'application/vnd.google-apps.folder' and name = '{$escapedName}' and trashed = false and '{$parentDataFolderId}' in parents";
                $results = $service->files->listFiles([
                    'q' => $query,
                    'spaces' => 'drive',
                    'fields' => 'files(id, name)',
                    'pageSize' => 1,
                    'supportsAllDrives' => true,
                    'includeItemsFromAllDrives' => true,
                ]);

                if (count($results->getFiles()) > 0) {
                    $folderId = $results->getFiles()[0]->getId();
                }
            }

            if (!$folderId) {
                return null;
            }

            $trashFolderId = $this->getTrashFolderId();

            // Fetch current parents and name
            $file = $service->files->get($folderId, [
                'fields' => 'parents, name',
                'supportsAllDrives' => true,
            ]);

            $previousParents = join(',', $file->parents ?? []);
            $currentName = $file->getName();

            // Prefix with deletion timestamp in WIT
            $deletedTimestamp = now()->setTimezone('Asia/Jayapura')->format('d-m-Y_H.i');
            $newName = "[Sampah (Karyawan) - {$deletedTimestamp} WIT] {$currentName}";

            $updateFile = new DriveFile();
            $updateFile->setName($newName);

            $updatedFolder = $service->files->update($folderId, $updateFile, [
                'addParents' => $trashFolderId,
                'removeParents' => $previousParents,
                'fields' => 'id, parents, name',
                'supportsAllDrives' => true,
            ]);

            return $updatedFolder->id;
        } catch (\Throwable $e) {
            Log::warning("Google Drive moveEmployeeToTrash skipped/failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Map document types to the 4 category folders.
     */
    public function getCategoryFolderName(string $documentType): string
    {
        $type = strtolower($documentType);

        if (in_array($type, ['ktp', 'kk', 'photo', 'id_card', 'npwp', 'bpjs_kes', 'bpjs_tk', 'bank_book'])) {
            return 'Identitas & Foto';
        }

        if (str_starts_with($type, 'ijazah') || str_starts_with($type, 'sertifikat') || str_starts_with($type, 'certificate') || in_array($type, ['education', 'training'])) {
            return 'Pendidikan & Keahlian';
        }

        if (in_array($type, ['employee_signature', 'hrd_signature'])) {
            return 'Tanda Tangan';
        }

        return 'Kepegawaian & Finansial';
    }

    /**
     * Get or create employee folder: [ID] Nama Lengkap
     */
    public function getEmployeeFolderId(Employee $employee): string
    {
        $parentDataFolderId = $this->getDataKaryawanFolderId();

        $employeeCode = $employee->employee_id ?: ($employee->employee_code ?: ('EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT)));
        $folderName = "[{$employeeCode}] {$employee->full_name}";

        // 1. Cek jika folder_id sudah tersimpan di database untuk karyawan ini
        if (!empty($employee->google_drive_folder_id)) {
            try {
                $service = $this->getDriveService();
                $existing = $service->files->get($employee->google_drive_folder_id, [
                    'fields' => 'id, name, trashed',
                    'supportsAllDrives' => true,
                ]);
                if ($existing && !$existing->trashed) {
                    // Update nama folder di Google Drive jika ada perubahan nama/ID
                    if ($existing->name !== $folderName) {
                        $updateFile = new DriveFile(['name' => $folderName]);
                        $service->files->update($employee->google_drive_folder_id, $updateFile, [
                            'supportsAllDrives' => true,
                        ]);
                    }
                    return $employee->google_drive_folder_id;
                }
            } catch (\Throwable $e) {
                // Folder tidak ditemukan / akses error, lanjut cari/buat baru
            }
        }

        $folderId = $this->getOrCreateFolder($folderName, $parentDataFolderId);

        // Update employee model with folder info if needed
        $folderUrl = "https://drive.google.com/drive/folders/{$folderId}";
        if ($employee->google_drive_folder_id !== $folderId || $employee->google_drive_folder_url !== $folderUrl) {
            $employee->google_drive_folder_id = $folderId;
            $employee->google_drive_folder_url = $folderUrl;
            $employee->saveQuietly();
        }

        return $folderId;
    }

    /**
     * Get or create the specific category subfolder inside an employee's folder.
     */
    public function getEmployeeCategoryFolderId(Employee $employee, string $documentType): string
    {
        $employeeFolderId = $this->getEmployeeFolderId($employee);
        $categoryName = $this->getCategoryFolderName($documentType);

        return $this->getOrCreateFolder($categoryName, $employeeFolderId);
    }

    /**
     * Upload a raw file (from local path or content) to a specific Google Drive folder.
     */
    public function uploadFile(string $localFilePath, string $targetFileName, string $mimeType, string $parentFolderId): array
    {
        $service = $this->getDriveService();

        if (!file_exists($localFilePath)) {
            throw new \RuntimeException("File lokal tidak ditemukan: {$localFilePath}");
        }

        $fileMetadata = new DriveFile([
            'name' => $targetFileName,
            'parents' => [$parentFolderId],
        ]);

        $content = file_get_contents($localFilePath);

        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, name, webViewLink, webContentLink',
            'supportsAllDrives' => true,
        ]);

        return [
            'file_id' => $file->id,
            'name' => $file->name,
            'web_view_link' => $file->webViewLink,
            'web_content_link' => $file->webContentLink,
        ];
    }

    /**
     * Upload an employee document/file to Google Drive inside the right folder hierarchy.
     */
    public function uploadEmployeeDocument(Employee $employee, string $documentType, UploadedFile|string $file, ?string $originalName = null): ?array
    {
        try {
            $targetFolderId = $this->getEmployeeCategoryFolderId($employee, $documentType);

            if ($file instanceof UploadedFile) {
                $localPath = $file->getRealPath();
                $fileName = $originalName ?: $file->getClientOriginalName();
                $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
            } elseif (is_string($file)) {
                if (Storage::disk('public')->exists($file)) {
                    $localPath = Storage::disk('public')->path($file);
                } elseif (file_exists($file)) {
                    $localPath = $file;
                } else {
                    Log::warning("GoogleDriveService: File lokal tidak ditemukan untuk upload: {$file}");
                    return null;
                }

                $fileName = $originalName ?: basename($localPath);
                $mimeType = mime_content_type($localPath) ?: 'application/octet-stream';
            } else {
                return null;
            }

            // Prefix file name with document type
            $cleanFileName = $this->formatDocumentFileName($employee, $documentType, $fileName);

            $uploaded = $this->uploadFile($localPath, $cleanFileName, $mimeType, $targetFolderId);

            Log::info("Berhasil upload berkas [{$documentType}] karyawan [{$employee->full_name}] ke Google Drive: {$uploaded['file_id']}");

            return $uploaded;
        } catch (\Throwable $e) {
            Log::error("Gagal upload berkas ke Google Drive untuk karyawan [{$employee->id}]: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return null;
        }
    }

    /**
     * Delete a file from Google Drive by its file ID.
     */
    public function deleteFile(?string $fileId): bool
    {
        if (empty($fileId)) {
            return false;
        }

        try {
            $service = $this->getDriveService();
            $service->files->delete($fileId, [
                'supportsAllDrives' => true,
            ]);
            Log::info("GoogleDriveService: Berhasil menghapus file lama dari Google Drive [{$fileId}]");
            return true;
        } catch (\Throwable $e) {
            Log::warning("GoogleDriveService: Gagal menghapus file lama dari Google Drive [{$fileId}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format a descriptive filename for clarity in Google Drive.
     */
    protected function formatDocumentFileName(Employee $employee, string $documentType, string $originalName): string
    {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $cleanType = strtoupper(str_replace(['_', '-'], ' ', $documentType));
        $employeeCode = $employee->employee_id ?: ($employee->employee_code ?: ('EMP-' . $employee->id));

        $safeName = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $employee->full_name);

        return "{$cleanType} - [{$employeeCode}] {$safeName}." . ($ext ?: 'pdf');
    }

    /**
     * Generate MySQL database dump and upload to '2. Backup SQL' folder.
     */
    public function backupDatabase(): array
    {
        $backupFolderId = $this->getBackupSqlFolderId();

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');

        $timestamp = date('Y-m-d_His');
        $filename = "backup_{$dbName}_{$timestamp}.sql";
        $tempDir = storage_path('app/backups');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFilePath = $tempDir . DIRECTORY_SEPARATOR . $filename;

        // Path to mysqldump in Laragon
        $mysqldumpPath = 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe';
        if (!file_exists($mysqldumpPath)) {
            $mysqldumpPath = 'mysqldump';
        }

        $passwordArg = !empty($dbPass) ? "--password=" . escapeshellarg($dbPass) : "";
        $cmd = "\"{$mysqldumpPath}\" --host={$dbHost} --port={$dbPort} --user={$dbUser} {$passwordArg} {$dbName} > \"{$tempFilePath}\"";

        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($tempFilePath) || filesize($tempFilePath) === 0) {
            throw new \RuntimeException("Gagal membuat mysqldump database: return code {$returnVar}");
        }

        $fileSize = filesize($tempFilePath);

        // Upload to Google Drive under '2. Backup SQL'
        $uploadResult = $this->uploadFile($tempFilePath, $filename, 'application/sql', $backupFolderId);

        // Clean up temporary local file
        @unlink($tempFilePath);

        return [
            'file_id' => $uploadResult['file_id'],
            'filename' => $filename,
            'file_size' => $fileSize,
            'web_view_link' => $uploadResult['web_view_link'],
            'created_at' => now(),
        ];
    }
}
