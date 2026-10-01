@extends('layouts.app')

@section('title', 'User Management')
@section('page_title', 'Manajemen Pengguna Sistem (Super Admin)')

@section('content')
<div class="space-y-6" x-data="{
    createUserModal: false,
    editModal: {
        open: false,
        id: null,
        name: '',
        email: '',
        role: 'admin',
        status: 'active',
        actionUrl: ''
    },
    resetPwModal: {
        open: false,
        name: '',
        actionUrl: ''
    },
    openEdit(user, actionUrl) {
        this.editModal.id = user.id;
        this.editModal.name = user.name;
        this.editModal.email = user.email;
        this.editModal.role = user.role;
        this.editModal.status = user.status;
        this.editModal.actionUrl = actionUrl;
        this.editModal.open = true;
    },
    openReset(user, actionUrl) {
        this.resetPwModal.name = user.name;
        this.resetPwModal.actionUrl = actionUrl;
        this.resetPwModal.open = true;
    }
}">

    <!-- TOP HEADER & ADD BUTTON -->
    <div class="flex items-center justify-between bg-white p-5 rounded-3xl border border-slate-100 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-brand-navy">Daftar Akun Pengguna</h2>
            <p class="text-xs text-slate-400">Kelola kredensial akses untuk role Super Admin dan Admin HRD</p>
        </div>
        <button type="button" @click="createUserModal = true" class="px-4 py-2.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Tambah User Baru</span>
        </button>
    </div>

    <!-- USERS TABLE -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-100 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">User</th>
                        <th class="py-3.5 px-3">Role</th>
                        <th class="py-3.5 px-3">Status</th>
                        <th class="py-3.5 px-4">Login Terakhir</th>
                        <th class="py-3.5 px-3">Terdaftar Sejak</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-brand-navy text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-bold text-brand-navy block leading-tight">{{ $user->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $user->email }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3 px-3">
                            @if($user->role === 'super_admin')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                <i class="fa-solid fa-shield-halved text-[9px]"></i>
                                Super Admin
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fa-solid fa-user-tie text-[9px]"></i>
                                Admin HRD
                            </span>
                            @endif
                        </td>

                        <td class="py-3 px-3">
                            @if($user->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Dinonaktifkan
                            </span>
                            @endif
                        </td>

                        <td class="py-3 px-4">
                            @if($user->last_login_at)
                            <span class="font-medium text-slate-800 block">{{ $user->last_login_at->format('d/m/Y H:i') }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">IP: {{ $user->last_login_ip ?? '-' }}</span>
                            @else
                            <span class="text-slate-400 italic">Belum pernah login</span>
                            @endif
                        </td>

                        <td class="py-3 px-3 text-slate-500">
                            {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                        </td>

                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">

                                <!-- Edit Button -->
                                <button type="button"
                                        @click="openEdit({{ $user }}, '{{ route('users.update', $user) }}')"
                                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition"
                                        title="Edit User">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>

                                <!-- Reset Password Button -->
                                <button type="button"
                                        @click="openReset({{ $user }}, '{{ route('users.resetPassword', $user) }}')"
                                        class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition"
                                        title="Reset Password">
                                    <i class="fa-solid fa-key"></i>
                                </button>

                                <!-- Toggle Active Status -->
                                @if(Auth::id() !== $user->id)
                                <form method="POST" action="{{ route('users.toggleStatus', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 rounded-lg {{ $user->status === 'active' ? 'bg-slate-100 text-slate-500 hover:text-rose-600' : 'bg-emerald-50 text-emerald-600' }} transition" title="{{ $user->status === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                        <i class="fa-solid {{ $user->status === 'active' ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                    </button>
                                </form>

                                <!-- Delete User -->
                                <button type="button"
                                        @click="openConfirm(
                                            'Hapus Pengguna',
                                            'Apakah Anda yakin ingin menghapus akun {{ $user->name }} ({{ $user->email }})?',
                                            '{{ route('users.destroy', $user) }}',
                                            'DELETE',
                                            'Ya, Hapus Pengguna',
                                            true
                                        )"
                                        class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition"
                                        title="Hapus Pengguna">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>

    <!-- MODAL: TAMBAH USER -->
    <div x-show="createUserModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="createUserModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-brand-navy">Tambah Pengguna Baru</h3>
                <button @click="createUserModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST" action="{{ route('users.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Nama user..." class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="user@primacoral.com" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Role</label>
                        <select name="role" required class="w-full p-2.5 rounded-xl border border-slate-200 bg-white">
                            <option value="admin">Admin HRD</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" required class="w-full p-2.5 rounded-xl border border-slate-200 bg-white">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="createUserModal = false" class="px-4 py-2 border rounded-xl font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-brand-primary text-white font-bold rounded-xl shadow">Simpan User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT USER -->
    <div x-show="editModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="editModal.open = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-brand-navy">Edit Data Pengguna</h3>
                <button @click="editModal.open = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="editModal.actionUrl" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="editModal.name" required class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" x-model="editModal.email" required class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ganti Password (Opsional)</label>
                    <input type="password" name="password" minlength="6" placeholder="Kosongkan jika tidak diubah" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Role</label>
                        <select name="role" x-model="editModal.role" required class="w-full p-2.5 rounded-xl border border-slate-200 bg-white">
                            <option value="admin">Admin HRD</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" x-model="editModal.status" required class="w-full p-2.5 rounded-xl border border-slate-200 bg-white">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModal.open = false" class="px-4 py-2 border rounded-xl font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-brand-primary text-white font-bold rounded-xl shadow">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: RESET PASSWORD -->
    <div x-show="resetPwModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="resetPwModal.open = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-sm font-bold text-brand-navy mb-1">Reset Password Pengguna</h3>
            <p class="text-xs text-slate-500 mb-4">Reset password untuk: <strong x-text="resetPwModal.name"></strong></p>

            <form :action="resetPwModal.actionUrl" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Password Baru</label>
                    <input type="password" name="new_password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="resetPwModal.open = false" class="px-4 py-2 border rounded-xl font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
