@extends('layouts.app')

@section('title', 'Manajemen Departemen')
@section('page_title', 'Master Data Departemen Perusahaan')

@section('content')
<div class="space-y-6" x-data="{
    createDeptModal: false,
    editModal: {
        open: false,
        id: null,
        name: '',
        description: '',
        actionUrl: ''
    },
    openEdit(dept, actionUrl) {
        this.editModal.id = dept.id;
        this.editModal.name = dept.name;
        this.editModal.description = dept.description || '';
        this.editModal.actionUrl = actionUrl;
        this.editModal.open = true;
    }
}">

    <!-- TOP HEADER & CONTROLS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-100 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-brand-navy">Daftar Departemen Operasional</h2>
            <p class="text-xs text-slate-400">Kelola master departemen yang tersedia untuk penempatan seluruh karyawan</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Search -->
            <form method="GET" action="{{ route('departments.index') }}" class="relative">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari departemen..."
                       class="py-2 pl-8 pr-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
            </form>

            <button type="button" @click="createDeptModal = true" class="px-4 py-2.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2 flex-shrink-0">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Departemen</span>
            </button>
        </div>
    </div>

    <!-- DEPARTMENTS TABLE -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-100 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Departemen</th>
                        <th class="py-3.5 px-4">Keterangan / Deskripsi</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Karyawan</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($departments as $index => $dept)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center font-bold text-slate-400">
                            {{ $departments->firstItem() + $index }}
                        </td>

                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-teal-50 text-brand-primary border border-teal-200/50 flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-sm">
                                    <i class="fa-solid fa-building-user"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-brand-navy block leading-tight">{{ $dept->name }}</span>
                                    <span class="text-[10px] text-slate-400">Dibuat: {{ $dept->created_at ? $dept->created_at->format('d/m/Y') : '-' }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3 px-4 text-slate-500 max-w-xs truncate">
                            {{ $dept->description ?: '-' }}
                        </td>

                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('employees.index', ['department' => $dept->name]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 hover:bg-brand-primary hover:text-white text-slate-700 transition">
                                <i class="fa-solid fa-users text-[10px]"></i>
                                <span>{{ $dept->employees_count }} Orang</span>
                            </a>
                        </td>

                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                        </td>

                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <!-- Edit Button -->
                                <button type="button"
                                        @click="openEdit({{ $dept->toJson() }}, '{{ route('departments.update', $dept) }}')"
                                        class="p-2 text-slate-500 hover:text-brand-primary hover:bg-slate-100 rounded-lg transition"
                                        title="Edit Departemen">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- Delete Button -->
                                @if($dept->employees_count == 0)
                                <button type="button"
                                        @click="openConfirm(
                                            'Hapus Departemen',
                                            'Apakah Anda yakin ingin menghapus departemen {{ $dept->name }}? Departemen ini saat ini belum memiliki karyawan.',
                                            '{{ route('departments.destroy', $dept) }}',
                                            'DELETE',
                                            'Ya, Hapus'
                                        )"
                                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                        title="Hapus Departemen">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                                @else
                                <span class="p-2 text-slate-300 cursor-not-allowed" title="Tidak dapat dihapus karena digunakan karyawan">
                                    <i class="fa-regular fa-trash-can"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 block text-slate-300"></i>
                            <span>Belum ada data departemen ditemukan.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($departments->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $departments->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL TAMBAH DEPARTEMEN -->
    <div x-show="createDeptModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="createDeptModal = false"
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 transform transition-all"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 text-brand-primary flex items-center justify-center">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-brand-navy">Tambah Departemen Baru</h3>
                </div>
                <button type="button" @click="createDeptModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('departments.store') }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Departemen <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Information Technology" class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Keterangan / Deskripsi</label>
                    <textarea name="description" rows="3" placeholder="Deskripsi tugas atau lingkup departemen..." class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="createDeptModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white font-bold rounded-xl shadow-md transition">
                        Simpan Departemen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT DEPARTEMEN -->
    <div x-show="editModal.open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="editModal.open = false"
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 transform transition-all"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 text-brand-primary flex items-center justify-center">
                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-brand-navy">Edit Data Departemen</h3>
                </div>
                <button type="button" @click="editModal.open = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form :action="editModal.actionUrl" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Departemen <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editModal.name" required class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary">
                    <p class="text-[10px] text-slate-400 mt-1">Perubahan nama akan otomatis sinkron pada seluruh data karyawan yang terhubung.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Keterangan / Deskripsi</label>
                    <textarea name="description" x-model="editModal.description" rows="3" class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="editModal.open = false" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white font-bold rounded-xl shadow-md transition">
                        Perbarui Departemen
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

