<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'google_drive_folder_id')) {
                $table->string('google_drive_folder_id')->nullable();
            }
            if (!Schema::hasColumn('employees', 'google_drive_folder_url')) {
                $table->text('google_drive_folder_url')->nullable();
            }
        });

        Schema::table('employee_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_documents', 'google_drive_file_id')) {
                $table->string('google_drive_file_id')->nullable()->after('mime_type');
            }
            if (!Schema::hasColumn('employee_documents', 'google_drive_url')) {
                $table->text('google_drive_url')->nullable()->after('google_drive_file_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['google_drive_folder_id', 'google_drive_folder_url']);
        });

        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropColumn(['google_drive_file_id', 'google_drive_url']);
        });
    }
};
