@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('page_title', 'Pengaturan Sistem & Profil Perusahaan (Super Admin)')

@section('content')
<!-- Sticky Top Animated Progress Line (0% - 100%) -->
<div id="topFixedProgressBarContainer" class="fixed top-0 left-0 right-0 z-[9999] h-2 bg-slate-200/80 hidden pointer-events-none">
    <div id="topFixedProgressBar" class="h-full bg-gradient-to-r from-teal-500 via-emerald-400 to-teal-600 transition-all duration-300 shadow-lg shadow-teal-500/50" style="width: 0%"></div>
</div>

<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">

        <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 text-brand-primary p-2 flex items-center justify-center flex-shrink-0 border border-teal-100">
                <img src="{{ asset('images/logo-icon.png') }}?v=2" alt="Logo" class="max-h-full object-contain">
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
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? 'PT ARTHA BUANA PRIMACOAL') }}" required class="w-full p-2.5 rounded-xl border border-slate-200">
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
                    <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? 'hrd@primacoal.com') }}" class="w-full p-2.5 rounded-xl border border-slate-200">
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

        <!-- REAL-TIME DYNAMIC PROGRESS BAR CARD (0% - 100%) -->
        <div id="gdriveProgressCard" class="hidden rounded-2xl bg-gradient-to-r from-teal-50 via-slate-50 to-emerald-50 border-2 border-teal-500/40 p-5 shadow-lg space-y-3.5 transition-all duration-300">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-teal-600 text-white flex items-center justify-center text-lg shadow-md flex-shrink-0">
                        <i id="gdriveProgressIcon" class="fa-solid fa-cloud-arrow-up animate-bounce"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 id="gdriveProgressTitle" class="text-sm font-extrabold text-brand-navy">Memproses Backup Database ke Google Drive...</h4>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 animate-pulse">
                                <span class="w-1.5 h-1.5 mr-1 bg-teal-500 rounded-full"></span> Live Progress
                            </span>
                        </div>
                        <p id="gdriveProgressSubtitle" class="text-xs text-slate-500 font-medium">Mohon tunggu sebentar, sistem sedang menghubungkan dan mengunggah ke Google Drive</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span id="gdriveProgressPercent" class="text-2xl sm:text-3xl font-black text-teal-700 font-mono tracking-tight">0%</span>
                </div>
            </div>

            <!-- Animated Progress Bar 0% - 100% -->
            <div class="w-full bg-slate-200/80 rounded-full h-4 overflow-hidden p-0.5 border border-slate-300/60 shadow-inner">
                <div id="gdriveProgressBar" class="h-full rounded-full bg-gradient-to-r from-teal-500 via-emerald-500 to-teal-600 transition-all duration-300 relative shadow-sm" style="width: 0%">
                    <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                </div>
            </div>

            <!-- Dynamic Steps Indicator -->
            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 pt-0.5">
                <span id="gdriveProgressStatus" class="flex items-center gap-2 text-teal-900 font-bold">
                    <i class="fa-solid fa-circle-notch fa-spin text-teal-600"></i>
                    <span id="gdriveProgressText">Menginisialisasi sesi OAuth 2.0 Google Drive...</span>
                </span>
                <span class="text-slate-400 font-mono text-[10px] flex items-center gap-1">
                    <i class="fa-brands fa-google-drive text-amber-500"></i> arthabuana.timika@gmail.com
                </span>
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

            <!-- Folder 3: Sampah (Karyawan) -->
            <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-200/60 space-y-2 sm:col-span-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                        <i class="fa-solid fa-trash-can text-rose-500 text-sm"></i>
                        <span>3. Sampah (Karyawan)</span>
                    </div>
                    <span class="text-[10px] bg-rose-100 text-rose-700 px-2.5 py-0.5 rounded-full font-bold">Otomatisasi Sampah & Arsip</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                    Ketika data karyawan dihapus oleh Super Admin melalui aplikasi web, sistem akan secara otomatis memindahkan seluruh folder dan berkas milik karyawan tersebut dari folder <strong>1. Data Karyawan</strong> ke folder <strong>3. Sampah (Karyawan)</strong> di Google Drive, serta melabelinya dengan format: <code class="text-rose-700 font-semibold">[Sampah (Karyawan) - DD-MM-YYYY_HH.mm WIT] [ID] Nama Karyawan</code>.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <!-- Form Sinkronisasi Seluruh Berkas Karyawan -->
            <form method="POST" action="{{ route('settings.sync_gdrive') }}" id="syncGdriveForm">
                @csrf
                <button type="submit" id="btnSyncGdrive" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up" id="iconSync"></i>
                    <span id="textSync">Sinkronisasi Semua Berkas ke Drive</span>
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

    <!-- Live Progress Bar Controller (0% - 100%) -->
    <script>
        function startProgressSimulation(mode) {
            const topBarContainer = document.getElementById('topFixedProgressBarContainer');
            const topBar = document.getElementById('topFixedProgressBar');
            const card = document.getElementById('gdriveProgressCard');
            const bar = document.getElementById('gdriveProgressBar');
            const percentText = document.getElementById('gdriveProgressPercent');
            const titleText = document.getElementById('gdriveProgressTitle');
            const subtitleText = document.getElementById('gdriveProgressSubtitle');
            const statusText = document.getElementById('gdriveProgressText');
            const icon = document.getElementById('gdriveProgressIcon');

            // Disable buttons to prevent double-click
            const btnBackup = document.getElementById('btnBackupGdrive');
            const btnSync = document.getElementById('btnSyncGdrive');
            if (btnBackup) { btnBackup.disabled = true; btnBackup.classList.add('opacity-60', 'cursor-not-allowed'); }
            if (btnSync) { btnSync.disabled = true; btnSync.classList.add('opacity-60', 'cursor-not-allowed'); }

            if (mode === 'backup') {
                titleText.textContent = 'Memproses Backup Database ke Google Drive...';
                subtitleText.textContent = 'Mengekspor struktur MySQL dan mengunggah file .sql ke folder 2. Backup SQL';
                icon.className = 'fa-solid fa-database text-white animate-bounce';
                document.getElementById('textBackup').textContent = 'Mencadangkan Database...';
                document.getElementById('iconBackup').className = 'fa-solid fa-spinner fa-spin';
            } else {
                titleText.textContent = 'Memproses Sinkronisasi Seluruh Berkas Karyawan...';
                subtitleText.textContent = 'Mengunggah dan merapikan seluruh dokumen fisik ke folder 1. Data Karyawan';
                icon.className = 'fa-solid fa-cloud-arrow-up text-white animate-bounce';
                document.getElementById('textSync').textContent = 'Menyinkronkan Berkas...';
                document.getElementById('iconSync').className = 'fa-solid fa-spinner fa-spin';
            }

            // Show UI Elements
            if (topBarContainer) topBarContainer.classList.remove('hidden');
            if (card) {
                card.classList.remove('hidden');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            let progress = 0;
            function setProgress(val, message) {
                progress = Math.min(val, 99);
                const str = progress + '%';
                if (bar) bar.style.width = str;
                if (topBar) topBar.style.width = str;
                if (percentText) percentText.textContent = str;
                if (message && statusText) statusText.textContent = message;
            }

            // Step 1: 0% -> 22% (Init connection)
            setProgress(5, 'Menginisialisasi koneksi aman OAuth 2.0 Google Drive...');
            setTimeout(() => setProgress(22, 'Mengautentikasi akun arthabuana.timika@gmail.com...'), 500);

            // Step 2: 22% -> 50% (Processing data)
            setTimeout(() => {
                if (mode === 'backup') {
                    setProgress(48, 'Mengekspor dump seluruh tabel database MySQL...');
                } else {
                    setProgress(48, 'Memeriksa berkas identitas, foto, dan dokumen karyawan...');
                }
            }, 1400);

            // Step 3: 50% -> 76% (Uploading to Drive)
            setTimeout(() => {
                if (mode === 'backup') {
                    setProgress(75, 'Mengunggah file backup .sql ke folder 2. Backup SQL...');
                } else {
                    setProgress(75, 'Mengunggah paket berkas ke Google Drive masing-masing karyawan...');
                }
            }, 2800);

            // Step 4: 76% -> 92% (Verifying)
            setTimeout(() => {
                setProgress(90, 'Memverifikasi respon Google Drive & menyelesaikan penyimpanan...');
            }, 4500);

            // Step 5: 92% -> 98% (Finalizing)
            setTimeout(() => {
                setProgress(96, 'Menyelesaikan pencatatan log aktivitas sistem...');
            }, 6200);

            // Slow step creep until server finishes and reloads
            const slowInterval = setInterval(() => {
                if (progress < 99) {
                    setProgress(progress + 1);
                } else {
                    clearInterval(slowInterval);
                }
            }, 1000);
        }

        // Attach to Backup Form
        document.getElementById('backupGdriveForm')?.addEventListener('submit', function() {
            startProgressSimulation('backup');
        });

        // Attach to Sync Form
        document.getElementById('syncGdriveForm')?.addEventListener('submit', function(e) {
            if (!confirm('Mulai sinkronisasi seluruh dokumen karyawan yang ada di sistem ke folder Google Drive masing-masing?')) {
                e.preventDefault();
                return false;
            }
            startProgressSimulation('sync');
        });
    </script>


</div>
@endsection
