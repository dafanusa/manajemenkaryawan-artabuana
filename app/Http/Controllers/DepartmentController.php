<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query()->withCount('employees');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $departments = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $department = Department::create([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::record(
            'tambah_departemen',
            "Menambahkan departemen baru: {$department->name}",
            'Department',
            $department->id
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'department' => $department,
                'message' => "Departemen '{$department->name}' berhasil ditambahkan."
            ]);
        }

        return redirect()->route('departments.index')
            ->with('success', "Departemen '{$department->name}' berhasil ditambahkan!");
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,' . $department->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $oldName = $department->name;
        $newName = trim($validated['name']);

        $department->update([
            'name' => $newName,
            'description' => $validated['description'] ?? null,
        ]);

        // Sync employee string column if it was changed
        if ($oldName !== $newName) {
            Employee::where('department_id', $department->id)
                ->orWhere('department', $oldName)
                ->update(['department' => $newName, 'department_id' => $department->id]);
        }

        ActivityLog::record(
            'edit_departemen',
            "Memperbarui nama departemen dari '{$oldName}' menjadi '{$newName}'",
            'Department',
            $department->id
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'department' => $department,
                'message' => "Departemen berhasil diperbarui."
            ]);
        }

        return redirect()->route('departments.index')
            ->with('success', "Departemen '{$newName}' berhasil diperbarui!");
    }

    public function destroy(Department $department)
    {
        $employeeCount = Employee::where('department_id', $department->id)
            ->orWhere('department', $department->name)
            ->count();

        if ($employeeCount > 0) {
            return back()->with('error', "Departemen '{$department->name}' tidak dapat dihapus karena masih digunakan oleh {$employeeCount} karyawan.");
        }

        $name = $department->name;
        $department->delete();

        ActivityLog::record(
            'hapus_departemen',
            "Menghapus departemen: {$name}",
            'Department'
        );

        return redirect()->route('departments.index')
            ->with('success', "Departemen '{$name}' berhasil dihapus!");
    }
}

