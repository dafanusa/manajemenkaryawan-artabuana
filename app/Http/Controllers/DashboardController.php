<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
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
            'incompleteEmployees'
        ));
    }
}
