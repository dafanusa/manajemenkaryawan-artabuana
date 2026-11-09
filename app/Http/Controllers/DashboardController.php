<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeContract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Overall stats
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('employment_status', 'Aktif')->count();
        $inactiveEmployees = Employee::where('employment_status', 'Tidak Aktif')->count();
        $papuaEmployees = Employee::where('ethnicity', 'Papua')->count();
        $nonPapuaEmployees = Employee::where('ethnicity', 'Non Papua')->count();
        $maleEmployees = Employee::where('gender', 'Laki-laki')->count();
        $femaleEmployees = Employee::where('gender', 'Perempuan')->count();

        // 2. Documents completion
        $allEmployeesWithDocs = Employee::with('documents')->get();
        $incompleteEmployees = $allEmployeesWithDocs->filter(function ($emp) {
            return !$emp->isDocumentsComplete();
        });
        $incompleteDocumentsCount = $incompleteEmployees->count();

        // 3. Chart 1: Papua vs Non Papua
        $papuaPercentage = $totalEmployees > 0 ? round(($papuaEmployees / $totalEmployees) * 100, 1) : 0;
        $nonPapuaPercentage = $totalEmployees > 0 ? round(($nonPapuaEmployees / $totalEmployees) * 100, 1) : 0;

        // 4. Chart 2: Gender
        $malePercentage = $totalEmployees > 0 ? round(($maleEmployees / $totalEmployees) * 100, 1) : 0;
        $femalePercentage = $totalEmployees > 0 ? round(($femaleEmployees / $totalEmployees) * 100, 1) : 0;

        // 5. Chart 3: Religion
        $religionData = Employee::select('religion', DB::raw('count(*) as count'))
            ->groupBy('religion')
            ->orderByDesc('count')
            ->get();

        // 6. Chart 4: Work Location (Highland vs Lowland)
        $highlandEmployees = Employee::where('work_location', 'Highland')->count();
        $lowlandEmployees = Employee::where('work_location', 'Lowland')->count();
        $highlandPercentage = $totalEmployees > 0 ? round(($highlandEmployees / $totalEmployees) * 100, 1) : 0;
        $lowlandPercentage = $totalEmployees > 0 ? round(($lowlandEmployees / $totalEmployees) * 100, 1) : 0;

        // 7. Chart 5: Employment Status
        $activePercentage = $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100, 1) : 0;
        $inactivePercentage = $totalEmployees > 0 ? round(($inactiveEmployees / $totalEmployees) * 100, 1) : 0;

        // 8. Chart 6: Department Distribution
        $departmentData = Employee::select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();

        // 9. Chart 7: Position Distribution (Top 8)
        $positionData = Employee::select('position', DB::raw('count(*) as count'))
            ->groupBy('position')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        // 10. Recent Employees
        $recentEmployees = Employee::latest('join_date')
            ->limit(7)
            ->get();

        // 11. Recent Activities
        $recentActivities = ActivityLog::latest('created_at')
            ->limit(8)
            ->get();

        // 12. New Hire & Turnover (Analisis Masuk & Keluar Karyawan Berdasarkan Tahun)
        $currentYear = (int) date('Y');
        $joinYears = Employee::selectRaw('YEAR(join_date) as y')->whereNotNull('join_date')->distinct()->pluck('y')->map(fn($v) => (int)$v)->toArray();
        $leaveYears = Employee::selectRaw('YEAR(leave_date) as y')->whereNotNull('leave_date')->distinct()->pluck('y')->map(fn($v) => (int)$v)->toArray();
        $availableYears = array_values(array_unique(array_filter(array_merge($joinYears, $leaveYears, [$currentYear]))));
        rsort($availableYears);

        $selectedYear = (int) $request->input('year', $currentYear);
        if (!in_array($selectedYear, $availableYears)) {
            $selectedYear = $currentYear;
        }

        // Hitung New Hire & Karyawan Keluar pada tahun terpilih
        $newHiresCount = Employee::whereYear('join_date', $selectedYear)->count();
        $departuresCount = Employee::whereYear('leave_date', $selectedYear)->count();
        $netHeadcountGrowth = $newHiresCount - $departuresCount;

        // Turnover rate (%)
        $headcountBase = $totalEmployees > 0 ? $totalEmployees : 1;
        $turnoverRate = round(($departuresCount / $headcountBase) * 100, 1);

        // Tren Bulanan (Jan - Des) untuk Tahun Terpilih
        $monthlyHires = array_fill(1, 12, 0);
        $monthlyDepartures = array_fill(1, 12, 0);

        $hiresByMonth = Employee::whereYear('join_date', $selectedYear)
            ->selectRaw('MONTH(join_date) as m, count(*) as count')
            ->groupBy('m')
            ->pluck('count', 'm');
        foreach ($hiresByMonth as $m => $count) {
            $monthlyHires[(int)$m] = (int)$count;
        }

        $departuresByMonth = Employee::whereYear('leave_date', $selectedYear)
            ->selectRaw('MONTH(leave_date) as m, count(*) as count')
            ->groupBy('m')
            ->pluck('count', 'm');
        foreach ($departuresByMonth as $m => $count) {
            $monthlyDepartures[(int)$m] = (int)$count;
        }

        // Data karyawan baru & karyawan keluar di tahun terpilih
        $yearNewHires = Employee::whereYear('join_date', $selectedYear)
            ->orderByDesc('join_date')
            ->get();

        $yearDepartures = Employee::whereYear('leave_date', $selectedYear)
            ->orderByDesc('leave_date')
            ->get();

        // New hire & leave tahun berjalan (2026) untuk quick indicators
        $currentYearHires = Employee::whereYear('join_date', $currentYear)->count();
        $currentYearDepartures = Employee::whereYear('leave_date', $currentYear)->count();

        // 13. Peringatan Kontrak PKWT (Expiring Contracts Notification - 30 Hari & Telah Berakhir)
        $today = Carbon::today('Asia/Jayapura');
        $thresholdDays = 30;
        $thresholdDate = $today->copy()->addDays($thresholdDays)->endOfDay();

        // Ambil ID kontrak terbaru per karyawan aktif (abaikan status 'Diperpanjang')
        $latestContractIds = EmployeeContract::selectRaw('MAX(id) as id')
            ->where('status', '!=', 'Diperpanjang')
            ->groupBy('employee_id');

        $expiringContracts = EmployeeContract::with(['employee' => function ($q) {
                $q->select('id', 'full_name', 'employee_id', 'photo', 'position', 'department', 'employment_status');
            }])
            ->whereIn('id', $latestContractIds)
            ->whereNotNull('end_date')
            ->whereHas('employee', function ($q) {
                $q->where('employment_status', 'Aktif');
            })
            ->whereDate('end_date', '<=', $thresholdDate)
            ->orderBy('end_date', 'asc')
            ->get()
            ->map(function ($contract) use ($today) {
                $endDate = Carbon::parse($contract->end_date)->startOfDay();
                // Hitung selisih hari dari hari ini ke tanggal berakhir
                // Nilai minus: sudah lewat (expired), 0: hari ini berakhir, positif: sisa hari
                $daysRemaining = (int) $today->diffInDays($endDate, false);
                $contract->days_remaining = $daysRemaining;

                if ($daysRemaining < 0) {
                    $contract->urgency_level = 'expired';
                    $contract->urgency_title = 'Telah Berakhir';
                    $contract->status_label = 'Telah Berakhir ' . abs($daysRemaining) . ' Hari Lalu';
                } elseif ($daysRemaining === 0) {
                    $contract->urgency_level = 'today';
                    $contract->urgency_title = 'Hari Ini Berakhir';
                    $contract->status_label = 'Hari Ini Berakhir!';
                } elseif ($daysRemaining <= 7) {
                    $contract->urgency_level = 'urgent';
                    $contract->urgency_title = 'Kritis (< 1 Minggu)';
                    $contract->status_label = 'Sisa ' . $daysRemaining . ' Hari Lagi';
                } else {
                    $contract->urgency_level = 'warning';
                    $contract->urgency_title = 'Peringatan (1 Bulan)';
                    $contract->status_label = 'Sisa ' . $daysRemaining . ' Hari Lagi';
                }

                return $contract;
            });

        $expiringContractsCount = $expiringContracts->count();
        $urgentContractsCount = $expiringContracts->whereIn('urgency_level', ['expired', 'today', 'urgent'])->count();
        $warningContractsCount = $expiringContracts->where('urgency_level', 'warning')->count();
        $expiredContractsCount = $expiringContracts->where('urgency_level', 'expired')->count();

        return view('dashboard.index', compact(
            'totalEmployees',
            'activeEmployees',
            'inactiveEmployees',
            'papuaEmployees',
            'nonPapuaEmployees',
            'maleEmployees',
            'femaleEmployees',
            'incompleteDocumentsCount',
            'papuaPercentage',
            'nonPapuaPercentage',
            'malePercentage',
            'femalePercentage',
            'religionData',
            'highlandEmployees',
            'lowlandEmployees',
            'highlandPercentage',
            'lowlandPercentage',
            'activePercentage',
            'inactivePercentage',
            'departmentData',
            'positionData',
            'recentEmployees',
            'recentActivities',
            'incompleteEmployees',
            'currentYear',
            'availableYears',
            'selectedYear',
            'newHiresCount',
            'departuresCount',
            'netHeadcountGrowth',
            'turnoverRate',
            'monthlyHires',
            'monthlyDepartures',
            'yearNewHires',
            'yearDepartures',
            'currentYearHires',
            'currentYearDepartures',
            'expiringContracts',
            'expiringContractsCount',
            'urgentContractsCount',
            'warningContractsCount',
            'expiredContractsCount'
        ));
    }
}
