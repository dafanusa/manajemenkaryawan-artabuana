<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:super_admin,admin'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'email.required' => 'Username / Email wajib diisi.',
            'email.unique' => 'Username / Email sudah digunakan oleh pengguna lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        ActivityLog::record(
            'tambah_user',
            "Menambahkan user baru: {$user->name} ({$user->email}) dengan role {$user->role}",
            'User',
            $user->id
        );

        return back()->with('success', "User {$user->name} ({$user->email}) berhasil ditambahkan!");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:super_admin,admin'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:8'],
        ], [
            'email.required' => 'Username / Email wajib diisi.',
            'email.unique' => 'Username / Email sudah digunakan oleh pengguna lain.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        // Prevent self demotion or deactivation
        if (Auth::id() === $user->id) {
            if ($validated['role'] !== 'super_admin') {
                return back()->with('error', 'Anda tidak dapat menurunkan role akun Anda sendiri.');
            }
            if ($validated['status'] !== 'active') {
                return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
        }

        $oldEmail = $user->email;
        $passwordChanged = !empty($validated['password']);

        if ($passwordChanged) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $logMsg = "Memperbarui akun {$user->name} ({$user->email})";
        if ($oldEmail !== $user->email) {
            $logMsg .= " (Username diubah dari {$oldEmail} ke {$user->email})";
        }
        if ($passwordChanged) {
            $logMsg .= " (Password berhasil diperbarui)";
        }

        ActivityLog::record(
            'edit_user',
            $logMsg,
            'User',
            $user->id
        );

        return back()->with('success', "Data user {$user->name} berhasil diperbarui!");
    }

    public function toggleStatus(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        ActivityLog::record(
            'ubah_status_user',
            "Mengubah status user {$user->name} menjadi {$newStatus}",
            'User',
            $user->id
        );

        return back()->with('success', "Status user {$user->name} berhasil diubah menjadi {$newStatus}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:8'],
        ], [
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        ActivityLog::record(
            'reset_password',
            "Mereset password user: {$user->name} ({$user->email})",
            'User',
            $user->id
        );

        return back()->with('success', "Password user {$user->name} berhasil diperbarui!");
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya Super Admin.');
        }

        $userName = $user->name;
        $user->delete();

        ActivityLog::record(
            'hapus_user',
            "Menghapus user: {$userName}",
            'User'
        );

        return back()->with('success', "User {$userName} berhasil dihapus!");
    }
}
