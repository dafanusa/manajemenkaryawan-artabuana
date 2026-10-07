@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Eksekutif & Statistik Karyawan')

@section('content')
<div class="space-y-6">

    <!-- WELCOME BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-primary via-[#0a7566] to-brand-navy text-white p-6 sm:p-8 shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-xs font-semibold tracking-wide text-teal-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Sistem Aktif & Terintegrasi</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, {{ Auth::user()->name }}! 👋
                </h2>
                <p class="text-teal-100/90 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Aplikasi Manajemen Karyawan <strong>PT Artha Buana Primacoral</strong>. Seluruh metrik statistik dan visualisasi grafik di bawah ini diambil secara real-time dari database.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('employees.create') }}" class="px-4 py-2.5 bg-white text-brand-primary hover:bg-teal-50 font-bold text-xs rounded-xl shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Input Karyawan Baru</span>
                </a>
                <a href="{{ route('employees.index') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl border border-white/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Lihat Seluruh Data</span>
                </a>
            </div>
        </div>

        <!-- Decorative background shapes -->
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        <div class="absolute right-40 top-0 w-48 h-48 rounded-full bg-teal-300/10 pointer-events-none blur-xl"></div>
    </div>

    <!-- METRICS CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Total Karyawan -->
        <a href="{{ route('employees.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Karyawan</span>
                <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-brand-navy group-hover:text-white text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-users text-sm"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-brand-navy">{{ $totalEmployees }}</span>
                <span class="text-xs text-slate-400 font-medium">Orang</span>
            </div>
            <p class="text-[11px] text-teal-600 font-medium mt-1">100% Seluruh Personel</p>
        </a>

        <!-- Karyawan Aktif -->
        <a href="{{ route('employees.index', ['status' => 'Aktif']) }}" class="group bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Karyawan Aktif</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-user-check text-sm"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600">{{ $activeEmployees }}</span>
                <span class="text-xs text-slate-400 font-medium">Orang</span>
            </div>
            <p class="text-[11px] text-emerald-600 font-medium mt-1">{{ $activePercentage }}% dari total karyawan</p>
        </a>

        <!-- Karyawan Tidak Aktif -->
        <a href="{{ route('employees.index', ['status' => 'Tidak Aktif']) }}" class="group bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tidak Aktif</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-user-xmark text-sm"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-rose-600">{{ $inactiveEmployees }}</span>
                <span class="text-xs text-slate-400 font-medium">Orang</span>
            </div>
            <p class="text-[11px] text-rose-500 font-medium mt-1">{{ $inactivePercentage }}% Resign / Off-boarding</p>
        </a>

        <!-- Dokumen Belum Lengkap (Interactive card) -->
        <a href="{{ route('employees.index', ['incomplete_docs' => 1]) }}" class="group bg-amber-50/60 border border-amber-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-900 uppercase tracking-wider">Dokumen Belum Lengkap</span>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 group-hover:bg-amber-600 group-hover:text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-folder-open text-sm"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-amber-800">{{ $incompleteDocumentsCount }}</span>
                <span class="text-xs text-amber-700 font-medium">Karyawan</span>
            </div>
            <p class="text-[11px] text-amber-700 font-bold mt-1 flex items-center gap-1">
                <span>Klik untuk lihat daftar</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </p>
        </a>

    </div>

    <!-- DEMOGRAPHIC METRICS (Papua, Gender, Location) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Papua -->
        <a href="{{ route('employees.index', ['ethnicity' => 'Papua']) }}" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 hover:border-teal-500 transition">
            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-brand-primary flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-earth-asia"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Suku: Papua</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-extrabold text-brand-navy">{{ $papuaEmployees }}</span>
                    <span class="text-xs font-semibold text-brand-primary">({{ $papuaPercentage }}%)</span>
                </div>
            </div>
        </a>

        <!-- Non Papua -->
        <a href="{{ route('employees.index', ['ethnicity' => 'Non Papua']) }}" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 hover:border-teal-500 transition">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-globe"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Suku: Non Papua</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-extrabold text-brand-navy">{{ $nonPapuaEmployees }}</span>
                    <span class="text-xs font-semibold text-blue-600">({{ $nonPapuaPercentage }}%)</span>
                </div>
            </div>
        </a>

        <!-- Laki-laki -->
        <a href="{{ route('employees.index', ['gender' => 'Laki-laki']) }}" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 hover:border-teal-500 transition">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-mars"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Laki-laki</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-extrabold text-brand-navy">{{ $maleEmployees }}</span>
                    <span class="text-xs font-semibold text-sky-600">({{ $malePercentage }}%)</span>
                </div>
            </div>
        </a>

        <!-- Perempuan -->
        <a href="{{ route('employees.index', ['gender' => 'Perempuan']) }}" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 hover:border-teal-500 transition">
            <div class="w-12 h-12 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-venus"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Perempuan</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-extrabold text-brand-navy">{{ $femaleEmployees }}</span>
                    <span class="text-xs font-semibold text-pink-600">({{ $femalePercentage }}%)</span>
                </div>
            </div>
        </a>

    </div>

    <!-- CHARTS SECTION: ROW 1 (Papua/Non Papua, Gender, Highland/Lowland, Status) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Chart 1: Papua vs Non Papua -->
        <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-brand-navy">Komposisi Suku</h3>
                    <span class="text-[10px] bg-teal-50 text-brand-primary font-bold px-2 py-0.5 rounded-full">Donut</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Papua vs Non Papua</p>
                <div class="relative h-44 flex items-center justify-center">
                    <canvas id="chartPapua"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-center text-xs">
                <div class="p-2 rounded-xl bg-teal-50/50">
                    <span class="block text-slate-400 text-[10px]">Papua</span>
                    <span class="font-bold text-brand-primary">{{ $papuaEmployees }} ({{ $papuaPercentage }}%)</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-50">
                    <span class="block text-slate-400 text-[10px]">Non Papua</span>
                    <span class="font-bold text-brand-navy">{{ $nonPapuaEmployees }} ({{ $nonPapuaPercentage }}%)</span>
                </div>
            </div>
        </div>

        <!-- Chart 2: Gender -->
        <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-brand-navy">Jenis Kelamin</h3>
                    <span class="text-[10px] bg-sky-50 text-sky-600 font-bold px-2 py-0.5 rounded-full">Pie</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Laki-laki & Perempuan</p>
                <div class="relative h-44 flex items-center justify-center">
                    <canvas id="chartGender"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-center text-xs">
                <div class="p-2 rounded-xl bg-sky-50/50">
                    <span class="block text-slate-400 text-[10px]">Laki-laki</span>
                    <span class="font-bold text-sky-700">{{ $maleEmployees }} ({{ $malePercentage }}%)</span>
                </div>
                <div class="p-2 rounded-xl bg-pink-50/50">
                    <span class="block text-slate-400 text-[10px]">Perempuan</span>
                    <span class="font-bold text-pink-700">{{ $femaleEmployees }} ({{ $femalePercentage }}%)</span>
                </div>
            </div>
        </div>

        <!-- Chart 3: Lokasi Kerja (Highland vs Lowland) -->
        <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-brand-navy">Lokasi Kerja</h3>
                    <span class="text-[10px] bg-emerald-50 text-emerald-600 font-bold px-2 py-0.5 rounded-full">Donut</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Highland vs Lowland</p>
                <div class="relative h-44 flex items-center justify-center">
                    <canvas id="chartLocation"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-center text-xs">
                <div class="p-2 rounded-xl bg-emerald-50/50">
                    <span class="block text-slate-400 text-[10px]">Highland</span>
                    <span class="font-bold text-emerald-700">{{ $highlandEmployees }} ({{ $highlandPercentage }}%)</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-50">
                    <span class="block text-slate-400 text-[10px]">Lowland</span>
                    <span class="font-bold text-brand-navy">{{ $lowlandEmployees }} ({{ $lowlandPercentage }}%)</span>
                </div>
            </div>
        </div>

        <!-- Chart 4: Status Kepegawaian -->
        <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-brand-navy">Status Pegawai</h3>
                    <span class="text-[10px] bg-indigo-50 text-indigo-600 font-bold px-2 py-0.5 rounded-full">Donut</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Aktif vs Tidak Aktif</p>
                <div class="relative h-44 flex items-center justify-center">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-center text-xs">
                <div class="p-2 rounded-xl bg-emerald-50/50">
                    <span class="block text-slate-400 text-[10px]">Aktif</span>
                    <span class="font-bold text-emerald-700">{{ $activeEmployees }}</span>
                </div>
                <div class="p-2 rounded-xl bg-rose-50/50">
                    <span class="block text-slate-400 text-[10px]">Tidak Aktif</span>
                    <span class="font-bold text-rose-700">{{ $inactiveEmployees }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- CHARTS SECTION: ROW 2 (Departemen, Agama, Jabatan) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Chart 5: Departemen -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-brand-navy">Distribusi Departemen</h3>
                <span class="text-[10px] text-slate-400 font-medium">Berdasarkan Personel</span>
            </div>
            <p class="text-xs text-slate-400 mb-4">Jumlah karyawan per divisi</p>
            <div class="h-64">
                <canvas id="chartDepartment"></canvas>
            </div>
        </div>

        <!-- Chart 6: Agama -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-brand-navy">Komposisi Agama</h3>
                <span class="text-[10px] text-slate-400 font-medium">Database Real-time</span>
            </div>
            <p class="text-xs text-slate-400 mb-4">Keragaman keyakinan karyawan</p>
            <div class="h-64">
                <canvas id="chartReligion"></canvas>
            </div>
        </div>

        <!-- Chart 7: Jabatan -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-brand-navy">Top Jabatan / Posisi</h3>
                <span class="text-[10px] text-slate-400 font-medium">8 Posisi Terbanyak</span>
            </div>
            <p class="text-xs text-slate-400 mb-4">Struktur peranan kerja di lapangan</p>
            <div class="h-64">
                <canvas id="chartPosition"></canvas>
            </div>
        </div>

    </div>

    <!-- BOTTOM SECTION: RECENT EMPLOYEES & RECENT ACTIVITIES -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Table: Karyawan Terbaru (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">Karyawan Terbaru Bergabung</h3>
                    <p class="text-xs text-slate-400">Daftar penambahan personel paling mutakhir</p>
                </div>
                <a href="{{ route('employees.index') }}" class="text-xs font-bold text-brand-primary hover:underline flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-100 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Karyawan</th>
                            <th class="py-3 px-3">Jabatan & Dept</th>
                            <th class="py-3 px-3">Suku & Lokasi</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3">Tgl Masuk</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentEmployees as $emp)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    @if($emp->photo)
                                    <img src="{{ asset('storage/' . $emp->photo) }}" alt="{{ $emp->full_name }}" class="w-9 h-9 rounded-xl object-cover shadow-sm">
                                    @else
                                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-brand-primary font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                                    </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('employees.show', $emp) }}" class="font-bold text-brand-navy hover:text-brand-primary hover:underline block leading-tight">
                                            {{ $emp->full_name }}
                                        </a>
                                        <span class="text-[10px] text-slate-400">{{ $emp->employee_id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-semibold block text-slate-800">{{ $emp->position }}</span>
                                <span class="text-[10px] text-slate-400">{{ $emp->department }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold {{ $emp->ethnicity === 'Papua' ? 'bg-teal-50 text-brand-primary' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $emp->ethnicity }}
                                </span>
                                <span class="block text-[10px] text-slate-400 mt-0.5">{{ $emp->work_location }}</span>
                            </td>
                            <td class="py-3 px-3">
                                @if($emp->employment_status === 'Aktif')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Tidak Aktif
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-500">
                                {{ $emp->join_date ? $emp->join_date->format('d M Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('employees.show', $emp) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-primary hover:bg-slate-100 transition inline-block" title="Lihat Profil">
                                    <i class="fa-regular fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data karyawan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Timeline / Incomplete Docs (1 Col) -->
        <div class="space-y-6">

            <!-- Incomplete Docs Card -->
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                        <span>Perhatian Dokumen ({{ $incompleteDocumentsCount }})</span>
                    </h4>
                    <a href="{{ route('employees.index', ['incomplete_docs' => 1]) }}" class="text-[11px] font-bold text-amber-700 hover:underline">Semua</a>
                </div>

                @if($incompleteEmployees->isEmpty())
                <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl text-center">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-lg mb-1 block"></i>
                    <p class="text-xs font-bold text-emerald-800">Semua Dokumen Lengkap!</p>
                    <p class="text-[10px] text-emerald-600 mt-0.5">Semua data berkas wajib telah terpenuhi.</p>
                </div>
                @else
                <div class="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                    @foreach($incompleteEmployees->take(4) as $incomp)
                    <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/70 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-800 text-xs">{{ $incomp->full_name }}</p>
                            <span class="text-[10px] text-rose-500 font-medium">Kurang: {{ count($incomp->missingDocuments()) }} dokumen</span>
                        </div>
                        <a href="{{ route('employees.edit', $incomp) }}" class="px-2.5 py-1 bg-white border border-slate-200 hover:border-brand-primary text-[10px] font-bold rounded-lg text-brand-primary shadow-xs transition">
                            Upload
                        </a>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Recent Activity Log (Super Admin or General) -->
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-brand-primary"></i>
                        <span>Aktivitas Sistem Terkini</span>
                    </h4>
                    @if(Auth::user()->isSuperAdmin())
                    <a href="{{ route('activity_logs.index') }}" class="text-[11px] font-bold text-brand-primary hover:underline">Lihat Log</a>
                    @endif
                </div>

                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    @forelse($recentActivities as $act)
                    <div class="flex items-start gap-3 text-xs border-b border-slate-50 pb-2.5 last:border-0 last:pb-0">
                        <div class="w-7 h-7 rounded-lg bg-teal-50 text-brand-primary flex items-center justify-center flex-shrink-0 text-[11px] mt-0.5 font-bold">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 leading-snug truncate">{{ $act->description ?? $act->action }}</p>
                            <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-0.5">
                                <span>{{ $act->user_name ?? 'User' }}</span>
                                <span>&bull;</span>
                                <span>{{ $act->created_at ? $act->created_at->diffForHumans() : 'Baru saja' }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">Belum ada riwayat aktivitas.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function createDashboardChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);

        if (!canvas) {
            return;
        }

        // Jika chart sebelumnya sudah ada pada canvas,
        // hancurkan terlebih dahulu agar aman jika script dijalankan ulang.
        const existingChart = Chart.getChart(canvas);

        if (existingChart) {
            existingChart.destroy();
        }

        new Chart(canvas, config);
    }

    function initDashboardCharts() {

        // Brand palette colors
        const primaryTeal = '#096256';
        const navyColor = '#2A3956';

        // 1. Chart Papua vs Non Papua
        createDashboardChart('chartPapua', {
            type: 'doughnut',
            data: {
                labels: ['Papua', 'Non Papua'],
                datasets: [{
                    data: [
                        {{ $papuaEmployees }},
                        {{ $nonPapuaEmployees }}
                    ],
                    backgroundColor: [primaryTeal, navyColor],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '72%'
            }
        });

        // 2. Chart Gender
        createDashboardChart('chartGender', {
            type: 'pie',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [
                        {{ $maleEmployees }},
                        {{ $femaleEmployees }}
                    ],
                    backgroundColor: ['#0284c7', '#ec4899'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // 3. Chart Location
        createDashboardChart('chartLocation', {
            type: 'doughnut',
            data: {
                labels: ['Highland', 'Lowland'],
                datasets: [{
                    data: [
                        {{ $highlandEmployees }},
                        {{ $lowlandEmployees }}
                    ],
                    backgroundColor: ['#059669', '#3b82f6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '72%'
            }
        });

        // 4. Chart Status
        createDashboardChart('chartStatus', {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Tidak Aktif'],
                datasets: [{
                    data: [
                        {{ $activeEmployees }},
                        {{ $inactiveEmployees }}
                    ],
                    backgroundColor: ['#10b981', '#f43f5e'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '72%'
            }
        });

        // 5. Chart Department
        createDashboardChart('chartDepartment', {
            type: 'bar',
            data: {
                labels: {!! json_encode($departmentData->pluck('department')) !!},
                datasets: [{
                    label: 'Jumlah Karyawan',
                    data: {!! json_encode($departmentData->pluck('count')) !!},
                    backgroundColor: primaryTeal,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // 6. Chart Religion
        createDashboardChart('chartReligion', {
            type: 'bar',
            data: {
                labels: {!! json_encode($religionData->pluck('religion')) !!},
                datasets: [{
                    label: 'Karyawan',
                    data: {!! json_encode($religionData->pluck('count')) !!},
                    backgroundColor: [
                        '#096256',
                        '#2A3956',
                        '#0284c7',
                        '#f59e0b',
                        '#8b5cf6',
                        '#64748b'
                    ],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // 7. Chart Position
        createDashboardChart('chartPosition', {
            type: 'bar',
            data: {
                labels: {!! json_encode($positionData->pluck('position')) !!},
                datasets: [{
                    label: 'Personel',
                    data: {!! json_encode($positionData->pluck('count')) !!},
                    backgroundColor: '#2A3956',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    }

    // Jalankan dengan aman baik ketika DOM masih loading
    // maupun ketika DOM sudah selesai.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    } else {
        initDashboardCharts();
    }
</script>
@endpush
