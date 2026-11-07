<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class BackupDatabaseToGoogleDrive extends Command
{
    protected $signature = 'db:backup-gdrive';
    protected $description = 'Backup MySQL database dan upload file .sql ke Google Drive folder 2. Backup SQL';

    public function handle(GoogleDriveService $gdrive): int
    {
        $this->info("Memulai proses backup database ke Google Drive...");

        try {
            $result = $gdrive->backupDatabase();
            $this->info("Backup database berhasil!");
            $this->line("Nama File : " . $result['filename']);
            $this->line("File ID   : " . $result['file_id']);
            $this->line("Ukuran    : " . number_format($result['file_size'] / 1024, 2) . " KB");
            $this->line("Link URL  : " . $result['web_view_link']);
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Gagal backup database: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

