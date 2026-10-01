@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('page_title', 'Daftar & Manajemen Data Karyawan')

@section('content')
<div class="space-y-5" x-data="{ filterOpen: {{ request()->anyFilled(['status', 'gender', 'ethnicity', 'religion', 'work_location', 'department', 'incomplete_docs']) ? 'true' : 'false' }} }">

    <!-- TOP CONTROLS & ACTIONS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-100 shadow-sm">

        <!-- Search Bar -->
        <form method="GET" action="{{ route('employees.index') }}" class="flex-1 max-w-lg">
            <!-- Retain current filters -->
            @foreach(request()->only(['status', 'gender', 'ethnicity', 'religion', 'work_location', 'department', 'incomplete_docs']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama, ID, NIK, jabatan, divisi, lokasi..."
                       class="w-full pl-9 pr-24 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 text-slate-800 transition">
                <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center gap-1">
                    @if(request('search'))
                    <a href="{{ route('employees.index', request()->except('search')) }}" class="p-1 text-slate-400 hover:text-slate-600 text-xs" title="Reset cari">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    @endif
                    <button type="submit" class="px-3 py-1.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-[11px] font-bold rounded-lg transition">
                        Cari
                    </button>
                </div>
            </div>
        </form>

        <!-- Buttons: Filter, Export, Add -->
        <div class="flex items-center gap-2 flex-wrap">

            <!-- Toggle Filter Panel Button -->
            <button @click="filterOpen = !filterOpen"
                    :class="filterOpen ? 'bg-slate-100 text-brand-primary border-brand-primary' : 'bg-white text-slate-600 border-slate-200'"
                    class="px-3.5 py-2.5 rounded-xl border text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Filter</span>
                @if(request()->anyFilled(['status', 'gender', 'ethnicity', 'religion', 'work_location', 'department', 'incomplete_docs']))
                <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                @endif
            </button>

            <!-- Export Dropdown -->
            <div x-data="{ exportMenu: false }" class="relative">
                <button @click="exportMenu = !exportMenu" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition flex items-center gap-2 bg-white">
                    <i class="fa-solid fa-download text-xs text-slate-400"></i>
                    <span>Export</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>

                <div x-show="exportMenu"
                     @click.away="exportMenu = false"
                     x-cloak
                     class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-40">
                    <a href="{{ route('employees.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-brand-primary">
                        <i class="fa-solid fa-file-excel text-emerald-600 w-4"></i>
                        <span>Export Excel (CSV)</span>
                    </a>
                    <a href="{{ route('employees.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" target="_blank" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-brand-primary">
                        <i class="fa-solid fa-file-pdf text-rose-600 w-4"></i>
                        <span>Cetak / PDF Report</span>
                    </a>
                </div>
            </div>

            <!-- Add Employee Button -->
            <a href="{{ route('employees.create') }}" class="px-4 py-2.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold rounded-xl shadow-md shadow-brand-primary/20 transition flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Karyawan</span>
            </a>

        </div>

    </div>

    <!-- FILTER PANEL (COLLAPSIBLE) -->
    <div x-show="filterOpen" x-collapse x-cloak class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-sliders text-brand-primary"></i>
                <span>Filter Karyawan</span>
            </h3>
            <a href="{{ route('employees.index') }}" class="text-xs text-slate-400 hover:text-red-500 font-semibold transition">
                Reset Semua Filter
            </a>
        </div>

        <form method="GET" action="{{ route('employees.index') }}" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <!-- Status -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Kerja</label>
                <select name="status" class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary bg-white">
                    <option value="">Semua Status</option>
                    <option value="Aktif" {{ request('status') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Tidak Aktif" {{ request('status') === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>

            <!-- Gender -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Jenis Kelamin</label>
                <select name="gender" class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary bg-white">
                    <option value="">Semua Gender</option>
                    <option value="Laki-laki" {{ request('gender') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ request('gender') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <!-- Suku -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Suku</label>
                <select name="ethnicity" class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary bg-white">
                    <option value="">Semua Suku</option>
                    <option value="Papua" {{ request('ethnicity') === 'Papua' ? 'selected' : '' }}>Papua</option>
                    <option value="Non Papua" {{ request('ethnicity') === 'Non Papua' ? 'selected' : '' }}>Non Papua</option>
                </select>
            </div>

            <!-- Lokasi Kerja -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Lokasi Kerja</label>
                <select name="work_location" class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary bg-white">
                    <option value="">Semua Lokasi</option>
                    <option value="Highland" {{ request('work_location') === 'Highland' ? 'selected' : '' }}>Highland</option>
                    <option value="Lowland" {{ request('work_location') === 'Lowland' ? 'selected' : '' }}>Lowland</option>
                </select>
            </div>

            <!-- Departemen -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Departemen</label>
                <select name="department" class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary bg-white">
                    <option value="">Semua Dept</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Agama -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Agama</label>
                <select name="religion" class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary bg-white">
                    <option value="">Semua Agama</option>
                    @foreach($religions as $rel)
                    <option value="{{ $rel }}" {{ request('religion') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-2 sm:col-span-3 lg:col-span-6 flex items-center justify-between pt-2">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-amber-800">
                    <input type="checkbox" name="incomplete_docs" value="1" {{ request('incomplete_docs') ? 'checked' : '' }} class="w-4 h-4 rounded text-amber-600 border-slate-300">
                    <span>Hanya tampilkan karyawan dengan dokumen belum lengkap</span>
                </label>
                <button type="submit" class="px-5 py-2 bg-brand-navy hover:bg-brand-navy-dark text-white text-xs font-bold rounded-xl transition">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- EMPLOYEES TABLE CARD -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-100 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">ID Karyawan</th>
                        <th class="py-3.5 px-4">Karyawan</th>
                        <th class="py-3.5 px-3">Gender</th>
                        <th class="py-3.5 px-3">Suku</th>
                        <th class="py-3.5 px-3">Agama</th>
                        <th class="py-3.5 px-4">Jabatan & Dept</th>
                        <th class="py-3.5 px-3">Project / Lokasi</th>
                        <th class="py-3.5 px-3">Lokasi Kerja</th>
                        <th class="py-3.5 px-3">Status</th>
                        <th class="py-3.5 px-3">Tgl Masuk</th>
                        <th class="py-3.5 px-3">Tgl Keluar</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($employees as $emp)
                    <tr class="hover:bg-slate-50/80 transition group">

                        <!-- ID Karyawan -->
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">
                            {{ $emp->employee_id }}
                        </td>

                        <!-- Foto & Nama -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                @if($emp->photo)
                                <img src="{{ asset('storage/' . $emp->photo) }}" alt="{{ $emp->full_name }}" class="w-9 h-9 rounded-xl object-cover shadow-sm flex-shrink-0">
                                @else
                                <div class="w-9 h-9 rounded-xl bg-teal-50 text-brand-primary font-bold flex items-center justify-center text-xs flex-shrink-0">
                                    {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('employees.show', $emp) }}" class="font-bold text-brand-navy hover:text-brand-primary hover:underline block leading-tight">
                                        {{ $emp->full_name }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $emp->nik ?? '-' }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Gender -->
                        <td class="py-3 px-3">
                            <span class="text-slate-600 font-medium">{{ $emp->gender }}</span>
                        </td>

                        <!-- Suku -->
                        <td class="py-3 px-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $emp->ethnicity === 'Papua' ? 'bg-teal-50 text-brand-primary border border-teal-200' : 'bg-slate-100 text-slate-600' }}">
                                {{ $emp->ethnicity }}
                            </span>
                        </td>

                        <!-- Agama -->
                        <td class="py-3 px-3">
                            <span class="text-slate-600">{{ $emp->religion }}</span>
                        </td>

                        <!-- Jabatan & Dept -->
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-800 block">{{ $emp->position }}</span>
                            <span class="text-[10px] text-slate-400">{{ $emp->department }}</span>
                        </td>

                        <!-- Project / Lokasi -->
                        <td class="py-3 px-3">
                            <span class="text-slate-600 font-medium">{{ $emp->project_location ?? '-' }}</span>
                        </td>

                        <!-- Lokasi Kerja (Highland/Lowland) -->
                        <td class="py-3 px-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $emp->work_location === 'Highland' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                {{ $emp->work_location }}
                            </span>
                        </td>

                        <!-- Status Kepegawaian -->
                        <td class="py-3 px-3">
                            @if($emp->employment_status === 'Aktif')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Tidak Aktif
                            </span>
                            @endif
                        </td>

                        <!-- Tgl Masuk -->
                        <td class="py-3 px-3 text-slate-600">
                            {{ $emp->join_date ? $emp->join_date->format('d/m/Y') : '-' }}
                        </td>

                        <!-- Tgl Keluar -->
                        <td class="py-3 px-3 text-slate-500">
                            {{ $emp->leave_date ? $emp->leave_date->format('d/m/Y') : '-' }}
                        </td>

                        <!-- Aksi -->
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <!-- View / Preview -->
                                <a href="{{ route('employees.show', $emp) }}"
                                   class="w-7 h-7 rounded-lg bg-teal-50 text-brand-primary hover:bg-brand-primary hover:text-white flex items-center justify-center transition"
                                   title="Lihat Form Biodata Lengkap">
                                    <i class="fa-regular fa-eye text-xs"></i>
                                </a>

                                <!-- Edit -->
                                <a href="{{ route('employees.edit', $emp) }}"
                                   class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 hover:bg-brand-navy hover:text-white flex items-center justify-center transition"
                                   title="Edit Data Karyawan">
                                    <i class="fa-regular fa-pen-to-square text-xs"></i>
                                </a>

                                <!-- Print -->
                                <a href="{{ route('employees.print', $emp) }}" target="_blank"
                                   class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-700 hover:text-white flex items-center justify-center transition"
                                   title="Cetak Biodata Form">
                                    <i class="fa-solid fa-print text-xs"></i>
                                </a>

                                @if(Auth::user()->isSuperAdmin())
                                <!-- Delete Button (Super Admin Only) -->
                                <button type="button"
                                        @click="openConfirm(
                                            'Hapus Data Karyawan',
                                            'Apakah Anda yakin ingin menghapus data {{ $emp->full_name }} ({{ $emp->employee_id }}) secara permanen beserta seluruh berkas dokumennya?',
                                            '{{ route('employees.destroy', $emp) }}',
                                            'DELETE',
                                            'Ya, Hapus Data',
                                            true
                                        )"
                                        class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition"
                                        title="Hapus Karyawan (Super Admin)">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300 block"></i>
                            <p class="font-bold text-slate-600 text-sm">Tidak ada data karyawan yang cocok.</p>
                            <p class="text-xs text-slate-400 mt-0.5">Coba ubah kata kunci pencarian atau reset filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/40">
            <div class="text-xs text-slate-500">
                Menampilkan <strong>{{ $employees->firstItem() ?? 0 }}</strong> sampai <strong>{{ $employees->lastItem() ?? 0 }}</strong> dari <strong>{{ $employees->total() }}</strong> total karyawan
            </div>
            <div>
                {{ $employees->links() }}
            </div>
        </div>

    </div>

</div>
@endsection
