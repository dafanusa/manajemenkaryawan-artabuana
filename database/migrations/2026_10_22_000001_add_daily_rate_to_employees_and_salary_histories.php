<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'daily_rate')) {
                $table->decimal('daily_rate', 15, 2)->default(0)->after('basic_salary');
            }
            if (!Schema::hasColumn('employees', 'salary_effective_date')) {
                $table->date('salary_effective_date')->nullable()->after('hourly_rate');
            }
        });

        Schema::table('employee_salary_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_salary_histories', 'daily_rate')) {
                $table->decimal('daily_rate', 15, 2)->default(0)->after('basic_salary');
            }
        });

        // Sync salary_effective_date from current salary history if available
        $histories = DB::table('employee_salary_histories')->where('is_current', true)->get();
        foreach ($histories as $h) {
            DB::table('employees')->where('id', $h->employee_id)->update([
                'salary_effective_date' => $h->start_date,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'salary_effective_date')) {
                $table->dropColumn('salary_effective_date');
            }
            if (Schema::hasColumn('employees', 'daily_rate')) {
                $table->dropColumn('daily_rate');
            }
        });

        Schema::table('employee_salary_histories', function (Blueprint $table) {
            if (Schema::hasColumn('employee_salary_histories', 'daily_rate')) {
                $table->dropColumn('daily_rate');
            }
        });
    }
};

