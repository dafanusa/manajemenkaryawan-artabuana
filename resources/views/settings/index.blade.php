@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('page_title', 'Pengaturan Sistem & Profil Perusahaan (Super Admin)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">

        <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 text-brand-primary p-2 flex items-center justify-center flex-shrink-0 border border-teal-100">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="max-h-full object-contain">
            </div>
            <div>
                <h2 class="text-base font-bold text-brand-navy">Identitas Perusahaan & Pengaturan Aplikasi</h2>
                <p class="text-xs text-slate-400">Pengaturan kop dokumen, informasi instansi, dan parameter sistem</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-5 text-xs">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Perusahaan Resmi</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? 'PT ARTHA BUANA PRIMACORAL') }}" required class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Judul Aplikasi</label>
                    <input type="text" name="app_title" value="{{ old('app_title', $settings['app_title'] ?? 'Employee Management System') }}" required class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1.5">Tagline / Bidang Usaha Perusahaan</label>
                    <input type="text" name="company_tagline" value="{{ old('company_tagline', $settings['company_tagline'] ?? 'General Contractor, Supplier & Mining Services') }}" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Email Resmi HRD</label>
                    <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? 'hrd@primacoral.com') }}" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">No. Telepon / Hotline</label>
                    <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone'] ?? '+62 901 321888') }}" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1.5">Alamat Kantor / Base Operasional</label>
                    <textarea name="company_address" rows="3" class="w-full p-2.5 rounded-xl border border-slate-200">{{ old('company_address', $settings['company_address'] ?? 'Jl. Cenderawasih No. 88, Timika, Papua Tengah') }}</textarea>
                </div>
            </div>

            <!-- Identity Colors Reference Card -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="block font-bold text-slate-700 mb-2 uppercase text-[10px] tracking-wider">Identitas Visual Sesuai Logo PT ABP:</span>
                <div class="flex items-center gap-4 text-xs font-semibold">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-[#096256] shadow-sm"></div>
                        <span>Primary Color: #096256 (Hijau Tua ABP)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-[#2A3956] shadow-sm"></div>
                        <span>Secondary Color: #2A3956 (Navy Blue ABP)</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>

    </div>

    <!-- Google Drive Integration & Cloud Database Backup Section -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 p-2.5 flex items-center justify-center flex-shrink-0 border border-amber-100 shadow-sm">
                    <i class="fa-brands fa-google-drive text-2xl text-amber-500"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-brand-navy">Integrasi Google Drive & Cloud Database Backup</h2>
                        @if($isOAuthConnected)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                            <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full animate-pulse"></span> Akun Terhubung
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">
                            <span class="w-1.5 h-1.5 mr-1 bg-amber-500 rounded-full"></span> Belum Terhubung
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400">Penyimpanan berkas karyawan otomatis dan cadangan database SQL terenkripsi ke Google Drive</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($isOAuthConnected)
                    <form method="POST" action="{{ route('settings.google_disconnect') }}" class="inline" onsubmit="return confirm('Yakin ingin memutuskan koneksi akun Google Drive ini?');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-link-slash"></i>
                            <span>Putuskan Akun</span>
                        </button>
                    </form>
                @elseif($isOAuthConfigured)
                    <a href="{{ route('settings.google_connect') }}" class="px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="fa-brands fa-google"></i>
                        <span>Hubungkan Akun Google Drive</span>
                    </a>
                @endif

                @if(!empty($gdriveRootId))
                    <a href="https://drive.google.com/drive/folders/{{ $gdriveRootId }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                        <i class="fa-brands fa-google-drive text-amber-500"></i>
                        <span>Buka Drive Root</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                    </a>
                @endif
            </div>
        </div>

        @if(!$isOAuthConnected)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 font-bold text-amber-900">
                        <i class="fa-solid fa-circle-exclamation text-amber-600"></i>
                        <span>Koneksi Akun Google Drive Diperlukan</span>
                    </div>
                    @if($isOAuthConfigured)
                        <a href="{{ route('settings.google_connect') }}" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold shadow-sm transition inline-flex items-center gap-1.5">
                            <i class="fa-brands fa-google"></i>
                            <span>Klik untuk Login Google</span>
                        </a>
                    @endif
                </div>
                <p class="text-[11px] text-amber-800 leading-relaxed">
                    Agar file upload dan cadangan database dapat menggunakan <strong>kuota penyimpanan akun Google Drive Anda (15 GB / 100 GB)</strong>, sistem menggunakan OAuth 2.0 resmi dari Google.
                    @if(!$isOAuthConfigured)
                        <br><span class="font-bold text-rose-700">Client ID & Client Secret belum dikonfigurasi di file <code>.env</code>. Ikuti panduan di bawah untuk memasukkan kuncinya.</span>
                    @endif
                </p>
            </div>
        @else
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <span class="font-bold">Akun Google Drive Aktif</span>
                        <span class="text-[11px] text-emerald-700 block">Setiap unggahan berkas karyawan dan pencadangan database SQL otomatis tersimpan di Google Drive Anda.</span>
                    </div>
                </div>
            </div>
        @endif

        @if(session('backup_link'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <div class="font-bold text-emerald-900">Cadangan Database Berhasil Diunggah!</div>
                        <div class="text-emerald-700">File SQL telah tersimpan aman di folder <strong>2. Backup SQL</strong> Google Drive.</div>
                    </div>
                </div>
                <a href="{{ session('backup_link') }}" target="_blank" rel="noopener noreferrer" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-sm transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fa-brands fa-google-drive"></i>
                    <span>Lihat di Drive</span>
                </a>
            </div>
        @endif

        <!-- Struktur Folder Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                        <i class="fa-solid fa-folder text-amber-500 text-sm"></i>
                        <span>1. Data Karyawan</span>
                    </div>
                    <span class="text-[10px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full font-medium">Auto-Hierarchy</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Setiap karyawan dibuatkan folder khusus <code>[Kode] Nama Karyawan</code> yang di dalamnya otomatis memuat 4 subfolder dokumen:
                </p>
                <div class="grid grid-cols-2 gap-1.5 text-[10px] font-medium text-slate-600 pt-1">
                    <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-lg border border-slate-200">
                        <i class="fa-solid fa-id-card text-blue-500"></i>
                        <span>Identitas & Foto</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-lg border border-slate-200">
                        <i class="fa-solid fa-graduation-cap text-emerald-500"></i>
                        <span>Pendidikan & Keahlian</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-lg border border-slate-200">
                        <i class="fa-solid fa-file-contract text-violet-500"></i>
                        <span>Kepegawaian & Finansial</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-lg border border-slate-200">
                        <i class="fa-solid fa-signature text-rose-500"></i>
                        <span>Tanda Tangan</span>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                        <i class="fa-solid fa-folder text-amber-500 text-sm"></i>
                        <span>2. Backup SQL</span>
                    </div>
                    <span class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-medium">Database Dump</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Menyimpan file cadangan database MySQL (.sql) lengkap dengan seluruh data karyawan, riwayat gaji, cuti, serta log aktivitas sistem dengan timestamp akurat.
                </p>
                <div class="p-2 rounded-lg bg-white border border-slate-200 text-[10px] text-slate-600 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Format Nama File:</span>
                        <code class="text-indigo-600 font-bold">backup_manajemenkaryawan_YYYY-MM-DD_HHmmss.sql</code>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Jadwal CLI:</span>
                        <code class="text-slate-700">php artisan db:backup-gdrive</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <!-- Form Sinkronisasi Seluruh Berkas Karyawan -->
            <form method="POST" action="{{ route('settings.sync_gdrive') }}" onsubmit="return confirm('Mulai sinkronisasi seluruh dokumen karyawan yang ada di sistem ke folder Google Drive masing-masing?');">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Sinkronisasi Semua Berkas ke Drive</span>
                </button>
            </form>

            <!-- Form Backup Database Sekarang -->
            <form method="POST" action="{{ route('settings.backup_gdrive') }}" id="backupGdriveForm">
                @csrf
                <button type="submit" id="btnBackupGdrive" class="px-5 py-2.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-database" id="iconBackup"></i>
                    <span id="textBackup">Backup Database ke Google Drive Sekarang</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('backupGdriveForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('btnBackupGdrive');
            const icon = document.getElementById('iconBackup');
            const text = document.getElementById('textBackup');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            icon.className = 'fa-solid fa-spinner fa-spin';
            text.innerText = 'Mencadangkan & Mengunggah Database...';
        });
    </script>


</div>
@endsection
