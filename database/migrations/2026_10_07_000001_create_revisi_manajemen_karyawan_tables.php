<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Departments table
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed initial departments
            $defaultDepartments = [
                'Mining Operations',
                'Human Resources',
                'Finance & Accounting',
                'Health Safety Environment',
                'Supply Chain & Logistics',
                'Maintenance',
                'Engineering',
                'General Services',
                'Information Technology',
                'Security',
            ];

            // Also grab existing distinct departments from employees if any
            if (Schema::hasTable('employees')) {
                $existing = DB::table('employees')->whereNotNull('department')->pluck('department')->toArray();
                $defaultDepartments = array_unique(array_merge($defaultDepartments, $existing));
            }

            foreach ($defaultDepartments as $deptName) {
                $deptName = trim($deptName);
                if (!empty($deptName)) {
                    DB::table('departments')->insertOrIgnore([
                        'name' => $deptName,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 2. Modify employees table: add department_id, line_grade, salary_level, contract_type
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('department')->constrained('departments')->nullOnDelete();
            }
            if (!Schema::hasColumn('employees', 'line_grade')) {
                $table->string('line_grade', 20)->nullable()->after('current_position'); // Grade A - Grade F
            }
            if (!Schema::hasColumn('employees', 'salary_level')) {
                $table->string('salary_level', 20)->nullable()->after('line_grade'); // Level 1 - Level 5
            }
            if (!Schema::hasColumn('employees', 'contract_type')) {
                $table->string('contract_type', 50)->default('PKWT')->after('employment_status'); // PKWT, PKWTT, Tetap, etc.
            }
        });

        // Link existing employees to departments
        if (Schema::hasTable('departments') && Schema::hasColumn('employees', 'department_id')) {
            $depts = DB::table('departments')->pluck('id', 'name');
            $employees = DB::table('employees')->get();
            foreach ($employees as $emp) {
                if (!empty($emp->department) && isset($depts[$emp->department])) {
                    DB::table('employees')->where('id', $emp->id)->update([
                        'department_id' => $depts[$emp->department]
                    ]);
                }
            }
        }

        // 3. Employee Salary Histories (Relational Salary Tracking)
        if (!Schema::hasTable('employee_salary_histories')) {
            Schema::create('employee_salary_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('line_grade', 20)->nullable(); // Grade A - F
                $table->string('salary_level', 20)->nullable(); // Level 1 - 5
                $table->decimal('basic_salary', 15, 2)->default(0);
                $table->decimal('hourly_rate', 15, 2)->default(0);
                $table->decimal('allowances_total', 15, 2)->default(0);
                $table->json('allowances_detail')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('period', 100)->nullable(); // e.g. "2023–2024", "2024–Sekarang"
                $table->text('notes')->nullable(); // Keterangan / alasan perubahan
                $table->boolean('is_current')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            // Populate initial salary history for existing employees if they have basic_salary
            $existingEmployees = DB::table('employees')->get();
            foreach ($existingEmployees as $emp) {
                $allowances = DB::table('employee_allowances')->where('employee_id', $emp->id)->get();
                $totalAllowances = $allowances->sum('amount');
                $allowanceDetails = $allowances->map(function ($a) {
                    return [
                        'allowance_name' => $a->allowance_name,
                        'amount' => (float)$a->amount,
                        'notes' => $a->notes
                    ];
                })->toArray();

                $joinYear = $emp->join_date ? date('Y', strtotime($emp->join_date)) : date('Y');
                $period = $joinYear . '–Sekarang';

                DB::table('employee_salary_histories')->insert([
                    'employee_id' => $emp->id,
                    'line_grade' => 'Grade C',
                    'salary_level' => 'Level 3',
                    'basic_salary' => $emp->basic_salary ?? 0,
                    'hourly_rate' => $emp->hourly_rate ?? 0,
                    'allowances_total' => $totalAllowances,
                    'allowances_detail' => json_encode($allowanceDetails),
                    'start_date' => $emp->join_date ?? now(),
                    'end_date' => null,
                    'period' => $period,
                    'notes' => 'Gaji Awal / Penetapan Pertama',
                    'is_current' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update default grade/level in employee record
                DB::table('employees')->where('id', $emp->id)->update([
                    'line_grade' => 'Grade C',
                    'salary_level' => 'Level 3',
                ]);
            }
        }

        // 4. Employee Leaves (Pencatatan Riwayat Cuti)
        if (!Schema::hasTable('employee_leaves')) {
            Schema::create('employee_leaves', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->date('leave_start_date'); // Tanggal berangkat
                $table->date('leave_end_date'); // Tanggal kembali
                $table->string('airline')->nullable(); // Maskapai
                $table->unsignedInteger('total_days')->default(1); // Jumlah hari
                $table->text('notes')->nullable(); // Keterangan
                $table->string('document_path')->nullable(); // Upload tiket / formulir
                $table->timestamps();
            });

            // Seed existing last_leave_date if present
            $leaveEmployees = DB::table('employees')->whereNotNull('last_leave_date')->get();
            foreach ($leaveEmployees as $emp) {
                DB::table('employee_leaves')->insert([
                    'employee_id' => $emp->id,
                    'leave_start_date' => $emp->last_leave_date,
                    'leave_end_date' => date('Y-m-d', strtotime($emp->last_leave_date . ' + 14 days')),
                    'airline' => 'Garuda Indonesia',
                    'total_days' => 14,
                    'notes' => 'Cuti Roster Terakhir',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Employee Skills (Keahlian Relasional)
        if (!Schema::hasTable('employee_skills')) {
            Schema::create('employee_skills', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('skill_name'); // Nama keahlian
                $table->string('proficiency_level')->nullable(); // Pemula, Menengah, Mahir, Ahli
                $table->text('notes')->nullable(); // Keterangan
                $table->timestamps();
            });

            // Convert existing skills_summary / tools_software to employee_skills if available
            $skillEmployees = DB::table('employees')->get();
            foreach ($skillEmployees as $emp) {
                if (!empty($emp->skills_summary)) {
                    $skills = array_filter(array_map('trim', explode(',', $emp->skills_summary)));
                    foreach ($skills as $s) {
                        DB::table('employee_skills')->insert([
                            'employee_id' => $emp->id,
                            'skill_name' => $s,
                            'proficiency_level' => 'Mahir / Advanced',
                            'notes' => 'Kompetensi operasional teruji',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                if (!empty($emp->tools_software)) {
                    $tools = array_filter(array_map('trim', explode(',', $emp->tools_software)));
                    foreach ($tools as $t) {
                        DB::table('employee_skills')->insert([
                            'employee_id' => $emp->id,
                            'skill_name' => $t,
                            'proficiency_level' => 'Menengah / Intermediate',
                            'notes' => 'Software & alat operasional',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // 6. Employee Contracts (Riwayat Kontrak PKWT)
        if (!Schema::hasTable('employee_contracts')) {
            Schema::create('employee_contracts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->unsignedInteger('contract_sequence')->default(1); // Kontrak 1, Kontrak 2, dst.
                $table->string('contract_number'); // Nomor kontrak
                $table->date('start_date'); // Tanggal mulai
                $table->date('end_date'); // Tanggal selesai
                $table->string('position')->nullable(); // Jabatan
                $table->string('department')->nullable(); // Departemen
                $table->string('project_location')->nullable(); // Lokasi/proyek
                $table->text('notes')->nullable(); // Keterangan
                $table->string('document_path')->nullable(); // File dokumen kontrak
                $table->string('status')->default('Aktif'); // Aktif, Selesai, Diperpanjang
                $table->timestamps();
            });

            // Populate initial contract for existing employees
            $allEmployees = DB::table('employees')->get();
            foreach ($allEmployees as $emp) {
                $start = $emp->join_date ?? '2023-01-01';
                $end = date('Y-m-d', strtotime($start . ' + 1 year'));
                DB::table('employee_contracts')->insert([
                    'employee_id' => $emp->id,
                    'contract_sequence' => 1,
                    'contract_number' => 'PKWT/ABP/' . ($emp->join_date ? date('Y', strtotime($emp->join_date)) : '2023') . '/' . sprintf('%03d', $emp->id),
                    'start_date' => $start,
                    'end_date' => $end,
                    'position' => $emp->position,
                    'department' => $emp->department,
                    'project_location' => $emp->project_location ?? 'Portsite / Grasberg',
                    'notes' => 'Kontrak Awal PKWT 1 Tahun',
                    'status' => 'Aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 7. Employee Licenses (SIM & License)
        if (!Schema::hasTable('employee_licenses')) {
            Schema::create('employee_licenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('license_type'); // SIM A, SIM B1, SIM B2, SIO Forklift, dsb
                $table->string('license_number'); // Nomor SIM/License
                $table->date('issue_date')->nullable(); // Tanggal terbit
                $table->date('expiry_date')->nullable(); // Tanggal berlaku / expired
                $table->string('document_path')->nullable(); // File dokumen
                $table->text('notes')->nullable(); // Keterangan
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_licenses');
        Schema::dropIfExists('employee_contracts');
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('employee_leaves');
        Schema::dropIfExists('employee_salary_histories');

        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
            if (Schema::hasColumn('employees', 'contract_type')) {
                $table->dropColumn('contract_type');
            }
            if (Schema::hasColumn('employees', 'salary_level')) {
                $table->dropColumn('salary_level');
            }
            if (Schema::hasColumn('employees', 'line_grade')) {
                $table->dropColumn('line_grade');
            }
        });

        Schema::dropIfExists('departments');
    }
};

