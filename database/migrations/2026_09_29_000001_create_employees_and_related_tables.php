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
        // 1. Employees table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('full_name');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->default('Laki-laki');
            $table->string('religion')->default('Islam');
            $table->enum('ethnicity', ['Papua', 'Non Papua'])->default('Non Papua');
            $table->string('marital_status')->nullable();
            $table->string('nik')->nullable()->index();
            $table->string('kk_number')->nullable();
            $table->text('ktp_address')->nullable();
            $table->text('domicile_address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();

            // Employment info
            $table->string('position');
            $table->string('department');
            $table->enum('employment_status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->string('project_location')->nullable();
            $table->enum('work_location', ['Highland', 'Lowland'])->default('Lowland');
            $table->date('join_date');
            $table->date('leave_date')->nullable();

            // Salary & Leave
            $table->string('current_position')->nullable();
            $table->unsignedInteger('previous_work_years')->default(0);
            $table->unsignedInteger('previous_work_months')->default(0);
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('hourly_rate', 15, 2)->default(0);
            $table->date('last_leave_date')->nullable();

            // Skills
            $table->text('skills_summary')->nullable();
            $table->text('tools_software')->nullable();

            // Administration
            $table->string('npwp_number')->nullable();
            $table->string('ptkp_status')->nullable();
            $table->string('bpjs_kes_number')->nullable();
            $table->string('bpjs_tk_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();

            // Signatures
            $table->string('employee_signature_name')->nullable();
            $table->string('hrd_signature_name')->nullable();

            $table->timestamps();
        });

        // 2. Emergency Contacts
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternative_phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 3. Educations
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('level'); // SD, SLTP, SLTA, S1, S2
            $table->string('institution_name')->nullable();
            $table->string('major')->nullable();
            $table->string('graduation_year')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        // 4. Work Experiences
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('position')->nullable();
            $table->string('period')->nullable();
            $table->text('reason_for_leaving')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        // 5. Certificates
        Schema::create('employee_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('certificate_name');
            $table->string('certificate_number')->nullable();
            $table->string('valid_until')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        // 6. Allowances
        Schema::create('employee_allowances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('allowance_name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Documents Checklist & Uploads
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('document_type'); // ktp, kk, photo, id_card, npwp, bpjs_kes, bpjs_tk, bank_book, salary_slip, leave_form, employee_signature, hrd_signature, etc.
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });

        // 8. Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('action'); // login, logout, create, update, delete, export, etc.
            $table->text('description')->nullable();
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 9. Settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_allowances');
        Schema::dropIfExists('employee_certificates');
        Schema::dropIfExists('work_experiences');
        Schema::dropIfExists('employee_educations');
        Schema::dropIfExists('emergency_contacts');
        Schema::dropIfExists('employees');
    }
};
