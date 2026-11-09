<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Biodata - {{ $employee->full_name }} ({{ $employee->employee_id }})</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }

        /* PRINT / PDF EXPORT PAGINATION RULES */
        @media print {
            body {
                background: white !important;
                color: #1e293b !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 8mm 10mm 10mm 10mm;
            }

            /* Container reset - use full page margins cleanly */
            .print-container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: transparent !important;
            }

            /* Kop Header */
            .print-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                border-bottom: 2px solid #2A3956 !important;
                padding-bottom: 12px !important;
                margin-bottom: 12px !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                break-after: avoid !important;
                page-break-after: avoid !important;
            }

            .print-header-box {
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-end !important;
                justify-content: center !important;
                border: 2px solid #2A3956 !important;
                border-radius: 12px !important;
                background-color: #ffffff !important;
                padding: 6px 16px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Setiap bagian/poin diperlakukan sebagai satu blok konten */
            .print-section {
                break-inside: avoid;
                page-break-inside: avoid;
                -webkit-column-break-inside: avoid;
            }

            /* Judul bagian TIDAK BOLEH terpisah dari kontennya (mencegah orphan header) */
            .print-section-header {
                background-color: #2A3956 !important;
                color: #ffffff !important;
                break-after: avoid !important;
                page-break-after: avoid !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Card dan Grid di dalam bagian */
            .print-card,
            .print-grid {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            /* Tabel: cegah baris terpotong & ulangi thead saat halaman berlanjut */
            table.w-full {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            thead {
                display: table-header-group !important;
                break-after: avoid !important;
                page-break-after: avoid !important;
            }

            thead th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            tr {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            tbody {
                break-inside: auto !important;
            }

            /* Tanda Tangan: harus selalu utuh dan bersama pernyataannya */
            .print-signature-section {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .print-footer {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                border-top: 1px solid #cbd5e1 !important;
                margin-top: 10px !important;
                padding-top: 6px !important;
                font-size: 8.5pt !important;
                color: #64748b !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Pastikan warna latar belakang dan teks resmi tercetak presisi */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <!-- Top floating action bar -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl shadow-md border border-slate-200">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.show', $employee) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-xs font-bold rounded-xl text-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <div>
                <span class="text-xs font-bold text-slate-800 block">Pratinjau Dokumen Biodata</span>
                <span class="text-[10px] text-slate-400">Siap diunduh sebagai file PDF atau dicetak</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <!-- Tombol 1: Download PDF Langsung Tanpa Header Chrome -->
            <button id="btnDownloadPdf" onclick="downloadPdfDirectly()" class="px-5 py-2.5 bg-[#096256] hover:bg-[#074e44] text-white text-xs font-extrabold rounded-xl shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-file-arrow-down text-sm"></i>
                <span>Download PDF Langsung</span>
            </button>

            <!-- Tombol 2: Cetak ke Printer Fisik (Opsional) -->
            <button onclick="executePrint()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition flex items-center gap-1.5" title="Cetak langsung ke kertas via printer fisik">
                <i class="fa-solid fa-print"></i>
                <span class="hidden sm:inline">Cetak Printer</span>
            </button>
        </div>
    </div>

    <!-- PRINT CONTAINER (A4 COMPATIBLE) -->
    <div class="print-container max-w-4xl mx-auto bg-white p-8 sm:p-10 shadow-lg border border-slate-200 rounded-2xl space-y-3.5 text-[10.5px] leading-tight text-slate-800">

        <!-- HEADER KOP RESMI PERUSAHAAN (SESUAI FORMAT PREVIEW) -->
        <div class="print-header flex items-center justify-between pb-3.5 mb-3 border-b-2 border-[#2A3956]" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2A3956; padding-bottom: 12px; margin-bottom: 12px;">
            <!-- Logo Perusahaan Proporsional -->
            <div class="flex items-center" style="display: flex; align-items: center; flex-shrink: 0;">
                <img src="{{ asset('images/logo.png') }}" alt="PT Artha Buana Primacoal" class="h-11 max-h-12 w-auto object-contain" style="height: 44px; max-height: 48px; width: auto; object-fit: contain; display: block;">
            </div>

            <!-- Kotak Judul Formulir HRD (Struktur Flex Murni - Anti Blank & Anti Pudar) -->
            <div class="print-header-box flex flex-col items-end justify-center border-2 border-[#2A3956] rounded-xl px-4 py-1.5 bg-white" style="display: flex; flex-direction: column; align-items: flex-end; justify-content: center; border: 2px solid #2A3956; border-radius: 12px; padding: 6px 16px; background-color: #ffffff; flex-shrink: 0;">
                <span style="display: block; color: #096256; font-size: 10px; font-weight: 800; line-height: 14px; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Plus Jakarta Sans', sans-serif;">FORMULIR HRD</span>
                <span style="display: block; color: #2A3956; font-size: 15px; font-weight: 900; line-height: 20px; white-space: nowrap; font-family: 'Plus Jakarta Sans', sans-serif;">FORM BIODATA KARYAWAN</span>
                <span style="display: block; color: #475569; font-size: 10px; font-weight: 700; font-family: monospace, 'Courier New', Courier; line-height: 14px;">ID: {{ $employee->employee_id }}</span>
            </div>
        </div>

        <!-- A. DATA PRIBADI -->
        <div class="space-y-1.5 print-section">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] flex justify-between print-section-header" style="background-color: #2A3956 !important; color: #ffffff !important;">
                <span>A. DATA PRIBADI</span>
                <span class="font-mono text-[9px] text-teal-200" style="color: #99f6e4 !important;">Status: {{ $employee->employment_status }}</span>
            </div>

            <div class="flex gap-4 items-start print-card">
                <div class="w-24 h-32 rounded border border-slate-300 overflow-hidden flex-shrink-0 bg-slate-100 flex items-center justify-center">
                    @if($employee->photo)
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="Foto" class="w-full h-full object-cover">
                    @else
                    <span class="text-[9px] text-slate-400 font-bold uppercase text-center">Pas Foto<br>3 x 4</span>
                    @endif
                </div>

                <div class="flex-1 grid grid-cols-2 gap-x-4 gap-y-1">
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">Nama Lengkap</span>
                        <span class="font-bold">: {{ $employee->full_name }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">Suku</span>
                        <span class="font-bold text-[#096256]" style="color: #096256 !important;">: {{ $employee->ethnicity }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">Tempat / Tgl. Lahir</span>
                        <span>: {{ $employee->birth_place ?? '-' }}, {{ $employee->birth_date ? $employee->birth_date->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">Jenis Kelamin</span>
                        <span>: {{ $employee->gender }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">Agama</span>
                        <span>: {{ $employee->religion }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">Status Perkawinan</span>
                        <span>: {{ $employee->marital_status ?? '-' }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">No. KTP / NIK</span>
                        <span class="font-mono">: {{ $employee->nik ?? '-' }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">No. Kartu Keluarga</span>
                        <span class="font-mono">: {{ $employee->kk_number ?? '-' }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">No. HP / WhatsApp</span>
                        <span>: {{ $employee->phone ?? '-' }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">Email</span>
                        <span>: {{ $employee->email ?? '-' }}</span>
                    </div>
                    <div class="col-span-2 flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">Alamat KTP</span>
                        <span>: {{ $employee->ktp_address ?? '-' }}</span>
                    </div>
                    <div class="col-span-2 flex border-b border-slate-100 pb-0.5">
                        <span class="w-32 font-semibold text-slate-600">Alamat Domisili</span>
                        <span>: {{ $employee->domicile_address ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- B. KONTAK DARURAT -->
        <div class="space-y-1 print-section">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] print-section-header">
                B. KONTAK DARURAT / EMERGENCY CONTACT
            </div>
            @php $c = $employee->emergencyContacts->first(); @endphp
            <div class="grid grid-cols-2 gap-x-4 gap-y-1 print-card">
                <div class="flex border-b border-slate-100 pb-0.5">
                    <span class="w-32 font-semibold text-slate-600">Nama Kontak</span>
                    <span class="font-medium">: {{ $c->name ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-0.5">
                    <span class="w-32 font-semibold text-slate-600">Hubungan</span>
                    <span class="font-medium">: {{ $c->relationship ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-0.5">
                    <span class="w-32 font-semibold text-slate-600">No. HP / WhatsApp</span>
                    <span>: {{ $c->phone ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-0.5">
                    <span class="w-32 font-semibold text-slate-600">No. Alternatif</span>
                    <span>: {{ $c->alternative_phone ?? '-' }}</span>
                </div>
                <div class="col-span-2 flex border-b border-slate-100 pb-0.5">
                    <span class="w-32 font-semibold text-slate-600">Alamat</span>
                    <span>: {{ $c->address ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- C. PENDIDIKAN -->
        <div class="space-y-1 print-section">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] print-section-header">
                C. RIWAYAT PENDIDIKAN TERAKHIR
            </div>
            <table class="w-full text-left border border-slate-300">
                <thead class="bg-slate-100 font-bold border-b border-slate-300">
                    <tr>
                        <th class="p-1 border-r border-slate-300 w-16">Jenjang</th>
                        <th class="p-1 border-r border-slate-300">Nama Sekolah / Perguruan Tinggi</th>
                        <th class="p-1 border-r border-slate-300">Jurusan</th>
                        <th class="p-1 border-r border-slate-300 w-20 text-center">Tahun Lulus</th>
                        <th class="p-1">No. Ijazah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($employee->educations as $e)
                    <tr>
                        <td class="p-1 border-r border-slate-200 font-bold">{{ $e->level }}</td>
                        <td class="p-1 border-r border-slate-200">{{ $e->institution_name }}</td>
                        <td class="p-1 border-r border-slate-200">{{ $e->major ?? '-' }}</td>
                        <td class="p-1 border-r border-slate-200 text-center">{{ $e->graduation_year ?? '-' }}</td>
                        <td class="p-1">{{ $e->certificate_number ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-1 text-center text-slate-400">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- D. PENGALAMAN KERJA -->
        <div class="space-y-1 print-section">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] print-section-header">
                D. PENGALAMAN KERJA TERAKHIR
            </div>
            <table class="w-full text-left border border-slate-300">
                <thead class="bg-slate-100 font-bold border-b border-slate-300">
                    <tr>
                        <th class="p-1 border-r border-slate-300">Perusahaan</th>
                        <th class="p-1 border-r border-slate-300">Jabatan</th>
                        <th class="p-1 border-r border-slate-300 w-28">Periode</th>
                        <th class="p-1">Alasan Berhenti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($employee->workExperiences as $w)
                    <tr>
                        <td class="p-1 border-r border-slate-200 font-bold">{{ $w->company_name }}</td>
                        <td class="p-1 border-r border-slate-200">{{ $w->position ?? '-' }}</td>
                        <td class="p-1 border-r border-slate-200">{{ $w->period ?? '-' }}</td>
                        <td class="p-1">{{ $w->reason_for_leaving ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-1 text-center text-slate-400">Belum ada pengalaman kerja sebelumnya</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- E. KEAHLIAN & SERTIFIKAT -->
        <div class="space-y-1 print-section">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] print-section-header">
                E. KEAHLIAN & SERTIFIKAT KOMPETENSI
            </div>
            <div class="grid grid-cols-2 gap-3 border border-slate-200 p-2 rounded print-card">
                <div>
                    <p><strong>Keahlian Utama:</strong> {{ $employee->skills_summary ?? '-' }}</p>
                    <p><strong>Alat/Software:</strong> {{ $employee->tools_software ?? '-' }}</p>
                </div>
                <div>
                    @if($employee->skills->count() > 0)
                    <p class="font-bold text-slate-700">Daftar Keahlian Spesifik:</p>
                    <ul class="list-disc list-inside text-[10px] space-y-0.5">
                        @foreach($employee->skills as $sk)
                        <li><strong>{{ $sk->skill_name }}</strong> @if($sk->proficiency_level)({{ $sk->proficiency_level }})@endif @if($sk->notes)- {{ $sk->notes }}@endif</li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-slate-400 italic">Tidak ada daftar keahlian tambahan.</p>
                    @endif
                </div>
            </div>
            @if($employee->certificates->count() > 0)
            <table class="w-full text-left border border-slate-300 mt-1">
                <thead class="bg-slate-100 font-bold border-b border-slate-300">
                    <tr>
                        <th class="p-1 border-r border-slate-300">Nama Sertifikat</th>
                        <th class="p-1 border-r border-slate-300">Nomor Sertifikat</th>
                        <th class="p-1">Masa Berlaku</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($employee->certificates as $cert)
                    <tr>
                        <td class="p-1 border-r border-slate-200 font-semibold">{{ $cert->certificate_name }}</td>
                        <td class="p-1 border-r border-slate-200">{{ $cert->certificate_number ?? '-' }}</td>
                        <td class="p-1">{{ $cert->valid_until ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <!-- F. DATA ADMINISTRASI & SIM / LICENSE (SESUAI REVISI #2) -->
        <div class="space-y-1 print-section">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] flex justify-between items-center print-section-header">
                <span>F. DATA ADMINISTRASI & SURAT IZIN MENGEMUDI (SIM / LICENSE)</span>
                <span class="text-[9px] text-teal-200">Dokumen Izin & Kepatuhan</span>
            </div>
            <div class="border border-slate-200 p-2.5 rounded-xl space-y-2 print-card">
                <!-- Administrasi Umum -->
                <div class="grid grid-cols-3 gap-x-4 gap-y-0.5">
                    <p><strong>NPWP:</strong> {{ $employee->npwp_number ?? '-' }} (PTKP: {{ $employee->ptkp_status ?? '-' }})</p>
                    <p><strong>BPJS Kes:</strong> {{ $employee->bpjs_kes_number ?? '-' }}</p>
                    <p><strong>BPJS TK:</strong> {{ $employee->bpjs_tk_number ?? '-' }}</p>
                    <p class="col-span-3"><strong>Bank:</strong> {{ $employee->bank_name ?? '-' }} - {{ $employee->bank_account_number ?? '-' }} (a.n {{ $employee->bank_account_holder ?? '-' }})</p>
                </div>

                <!-- Bagian Revisi: SIM Pemerintah (Multi-pilihan) & License FI (Ada/Tidak Ada) -->
                @php
                    // Collect Government SIMs
                    $simLicenses = $employee->licenses->filter(function($l) {
                        return stripos($l->license_type, 'SIM') !== false;
                    });
                    $simTypes = $simLicenses->map(function($l) {
                        $cleaned = trim(str_ireplace('SIM', '', $l->license_type));
                        return $cleaned ?: $l->license_type;
                    })->filter()->values()->toArray();

                    $simText = !empty($simTypes) ? implode(', ', $simTypes) : 'C, B';
                    $simNumbers = $simLicenses->pluck('license_number')->filter()->implode(', ');
                    if (empty($simNumbers) && $employee->licenses->first()) {
                        $simNumbers = $employee->licenses->first()->license_number;
                    }
                    $simExpiry = $simLicenses->first()?->expiry_date?->format('d/m/Y');

                    // Check License FI (Freeport Indonesia)
                    $fiLicense = $employee->licenses->first(function($l) {
                        return stripos($l->license_type, 'FI') !== false || stripos($l->license_type, 'Freeport') !== false || stripos($l->license_type, 'SIMPER') !== false;
                    });
                    $hasFi = $fiLicense ? true : false;
                @endphp

                <div class="pt-2 border-t border-slate-200 grid grid-cols-2 gap-3 print-card">
                    <!-- SIM Pemerintah: Mendukung pengisian lebih dari 1 jenis SIM (contoh: C, B) -->
                    <div class="border border-slate-200 p-2 rounded-lg bg-slate-50/70">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-[#2A3956] text-[10px] uppercase tracking-wide">SIM Pemerintah (SIM Pol)</span>
                            <span class="text-[8.5px] text-slate-400 italic">Dapat multi-jenis (misal: C, B)</span>
                        </div>
                        <div class="space-y-0.5 text-[10px]">
                            <p>
                                <strong>Jenis SIM:</strong>
                                <span class="px-2 py-0.5 rounded bg-white border border-slate-300 font-bold text-[#096256]">{{ $simText }}</span>
                            </p>
                            @if(!empty($simNumbers))
                            <p><strong>Nomor SIM:</strong> <span class="font-mono">{{ $simNumbers }}</span></p>
                            @endif
                            @if($simExpiry)
                            <p><strong>Masa Berlaku:</strong> {{ $simExpiry }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- License FI: Pilihan Ada dan Tidak Ada -->
                    <div class="border border-slate-200 p-2 rounded-lg bg-slate-50/70">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-[#2A3956] text-[10px] uppercase tracking-wide">License FI (PT Freeport Indonesia)</span>
                            <span class="text-[8.5px] text-slate-400">Pilihan: Ada / Tidak Ada</span>
                        </div>
                        <div class="space-y-1 text-[10px]">
                            <div class="flex items-center gap-2">
                                <strong>Status:</strong>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9.5px] font-bold {{ $hasFi ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-500' }}">
                                        {!! $hasFi ? '&#9679;' : '&#9675;' !!} Ada
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9.5px] font-bold {{ !$hasFi ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-slate-100 text-slate-500' }}">
                                        {!! !$hasFi ? '&#9679;' : '&#9675;' !!} Tidak Ada
                                    </span>
                                </span>
                            </div>
                            @if($hasFi && $fiLicense)
                            <p><strong>No. License FI:</strong> <span class="font-mono font-bold">{{ $fiLicense->license_number ?? '-' }}</span></p>
                            <p><strong>Masa Berlaku:</strong> {{ $fiLicense->expiry_date ? $fiLicense->expiry_date->format('d/m/Y') : '-' }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Dokumen License Operasional / K3 Lainnya -->
                @php
                    $otherLicenses = $employee->licenses->reject(function($l) {
                        return stripos($l->license_type, 'SIM') !== false || stripos($l->license_type, 'FI') !== false || stripos($l->license_type, 'Freeport') !== false;
                    });
                @endphp
                @if($otherLicenses->count() > 0)
                <div class="pt-1 border-t border-slate-100 text-[9.5px] print-card">
                    <span class="font-bold text-slate-700 block mb-0.5">License & SIO Khusus Lainnya:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                        @foreach($otherLicenses as $lic)
                        <div class="border border-slate-200 p-1 rounded bg-white">
                            <strong>{{ $lic->license_type }}</strong>: {{ $lic->license_number ?? '-' }}
                            <div class="text-[8.5px] text-slate-500">Berlaku: {{ $lic->expiry_date ? $lic->expiry_date->format('d/m/Y') : '-' }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- G. DATA KEPEGAWAIAN (HRD) -->
        <div class="space-y-1 print-section">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] print-section-header">
                G. DATA KEPEGAWAIAN (HRD)
            </div>
            <div class="grid grid-cols-3 gap-x-4 gap-y-1 border border-slate-200 p-2 rounded print-card">
                <p><strong>ID Karyawan:</strong> {{ $employee->employee_id }}</p>
                <p><strong>Jabatan:</strong> {{ $employee->position }}</p>
                <p><strong>Departemen:</strong> {{ $employee->department }}</p>
                <p><strong>Status Kerja:</strong> {{ $employee->employment_status }}</p>
                <p><strong>Jenis Kontrak:</strong> {{ $employee->contract_type ?? 'PKWT' }}</p>
                <p><strong>Lokasi Kerja:</strong> {{ $employee->work_location }}</p>
                <p><strong>Project:</strong> {{ $employee->project_location ?? '-' }}</p>
                <p><strong>Tanggal Masuk:</strong> {{ $employee->join_date ? $employee->join_date->format('d/m/Y') : '-' }}</p>
                <p><strong>Tanggal Keluar:</strong> {{ $employee->leave_date ? $employee->leave_date->format('d/m/Y') : '-' }}</p>
            </div>

            @if($employee->contracts->count() > 0)
            <div class="pt-1 print-card">
                <p class="font-bold text-slate-700 text-[10px] mb-0.5">
                    Riwayat Kontrak PKWT (Total {{ $employee->contracts->count() }} Kontrak &bull; {{ max(0, $employee->contracts->count() - 1) }} Kali Perpanjangan):
                </p>
                <table class="w-full text-left border border-slate-300 text-[9.5px]">
                    <thead class="bg-slate-100 font-bold border-b border-slate-300">
                        <tr>
                            <th class="p-1 border-r border-slate-300 w-16">Tahap</th>
                            <th class="p-1 border-r border-slate-300">Nomor Kontrak</th>
                            <th class="p-1 border-r border-slate-300">Periode Kontrak</th>
                            <th class="p-1 border-r border-slate-300">Jabatan & Dept</th>
                            <th class="p-1 border-r border-slate-300">Lokasi / Proyek</th>
                            <th class="p-1">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($employee->contracts()->orderBy('contract_sequence', 'asc')->get() as $idx => $cnt)
                        <tr>
                            <td class="p-1 border-r border-slate-200 font-bold">Kontrak {{ $cnt->contract_sequence ?? ($idx + 1) }}</td>
                            <td class="p-1 border-r border-slate-200 font-mono">{{ $cnt->contract_number }}</td>
                            <td class="p-1 border-r border-slate-200 whitespace-nowrap">{{ $cnt->start_date ? $cnt->start_date->format('d/m/Y') : '-' }} s/d {{ $cnt->end_date ? $cnt->end_date->format('d/m/Y') : '-' }}</td>
                            <td class="p-1 border-r border-slate-200">{{ $cnt->position }} ({{ $cnt->department }})</td>
                            <td class="p-1 border-r border-slate-200">{{ $cnt->project_location ?? '-' }}</td>
                            <td class="p-1">{{ $cnt->notes ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- H. GAJI & CUTI -->
        <div class="space-y-1 print-section">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] flex justify-between print-section-header">
                <span>H. GAJI & RIWAYAT CUTI</span>
                @if(Auth::user()?->isSuperAdmin())
                <span class="text-[9px] text-teal-200 font-bold">Confidential</span>
                @endif
            </div>

            <!-- SALARY DETAILS (SUPER ADMIN ONLY) -->
            @if(Auth::user()?->isSuperAdmin())
            <div class="border border-slate-200 p-2 rounded space-y-1 bg-teal-50/20 print-card">
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <p><strong>Grade:</strong> {{ $employee->line_grade ?? '-' }}</p>
                    <p><strong>Level:</strong> {{ $employee->salary_level ?? '-' }}</p>
                    <p><strong>Gaji Pokok:</strong> Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</p>
                    <p><strong>Gaji Harian:</strong> Rp {{ number_format($employee->daily_rate, 0, ',', '.') }}</p>
                    <p><strong>Gaji Per Jam:</strong> Rp {{ number_format($employee->hourly_rate, 0, ',', '.') }}</p>
                    <p><strong>Tgl Berlaku:</strong> {{ $employee->salary_effective_date ? $employee->salary_effective_date->format('d/m/Y') : ($employee->currentSalaryHistory?->start_date ? $employee->currentSalaryHistory->start_date->format('d/m/Y') : '-') }}</p>
                </div>
                @if($employee->allowances->count() > 0)
                <div class="text-[10px] pt-0.5 border-t border-slate-100">
                    <strong>Tunjangan Lainnya:</strong>
                    @foreach($employee->allowances as $alw)
                        {{ $alw->allowance_name }} (Rp {{ number_format($alw->amount, 0, ',', '.') }})@if(!$loop->last), @endif
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- RIWAYAT CUTI (TAMPIL UNTUK SEMUA) -->
            @if($employee->leaves->count() > 0)
            <div class="pt-0.5 print-card">
                <p class="font-bold text-slate-700 text-[10px] mb-0.5">
                    Riwayat Cuti Karyawan (Cuti Terakhir: {{ $employee->last_leave_date ? $employee->last_leave_date->format('d/m/Y') : '-' }}):
                </p>
                <table class="w-full text-left border border-slate-300 text-[9.5px]">
                    <thead class="bg-slate-100 font-bold border-b border-slate-300">
                        <tr>
                            <th class="p-1 border-r border-slate-300 w-16">Cuti Ke-</th>
                            <th class="p-1 border-r border-slate-300">Tgl Berangkat</th>
                            <th class="p-1 border-r border-slate-300">Tgl Kembali</th>
                            <th class="p-1 border-r border-slate-300">Maskapai</th>
                            <th class="p-1 border-r border-slate-300 text-center w-16">Jumlah Hari</th>
                            <th class="p-1">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($employee->leaves()->orderBy('leave_start_date', 'desc')->get() as $idx => $lv)
                        <tr>
                            <td class="p-1 border-r border-slate-200 font-bold">Cuti {{ $employee->leaves->count() - $idx }}</td>
                            <td class="p-1 border-r border-slate-200">{{ $lv->leave_start_date ? $lv->leave_start_date->format('d/m/Y') : '-' }}</td>
                            <td class="p-1 border-r border-slate-200">{{ $lv->leave_end_date ? $lv->leave_end_date->format('d/m/Y') : '-' }}</td>
                            <td class="p-1 border-r border-slate-200">{{ $lv->airline ?? '-' }}</td>
                            <td class="p-1 border-r border-slate-200 text-center font-bold">{{ $lv->total_days ?? 1 }} Hari</td>
                            <td class="p-1">{{ $lv->notes ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- I. PERNYATAAN & TANDA TANGAN (SESUAI REVISI #3: PENUNJUK JABATAN DIPERBESAR) -->
        <div class="space-y-1.5 pt-1 print-section print-signature-section">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] print-section-header">
                I. PERNYATAAN
            </div>
            <p class="italic text-[9.5px] text-slate-600 print-card">
                "Saya menyatakan bahwa seluruh data yang saya berikan dalam formulir ini adalah benar dan dapat dipertanggungjawabkan. Saya bersedia memberitahukan kepada perusahaan apabila terdapat perubahan data."
            </p>

            <div class="grid grid-cols-2 gap-8 pt-2 text-center print-card">
                <!-- TTD Karyawan -->
                <div style="background-color: #ffffff;" class="flex flex-col items-center justify-between h-36 border border-slate-300 p-2.5 rounded-xl">
                    <span class="font-black text-[12px] text-[#2A3956] uppercase tracking-wider">Karyawan Yang Bersangkutan</span>
                    <div class="h-20 flex items-center justify-center my-0.5">
                        @php $empSign = $employee->getDocument('employee_signature'); @endphp
                        @if($empSign)
                        <img src="{{ $empSign->url }}" alt="TTD" class="h-full w-auto max-h-20 max-w-[200px] object-contain">
                        @else
                        <span class="text-[9px] text-slate-300 italic">(Tanda Tangan)</span>
                        @endif
                    </div>
                    <div class="w-full border-t border-slate-300 pt-1">
                        <span class="font-bold underline text-[11px] text-slate-800 block">{{ $employee->employee_signature_name ?? $employee->full_name }}</span>
                    </div>
                </div>

                <!-- TTD HRD / Pemeriksa -->
                <div style="background-color: #ffffff;" class="flex flex-col items-center justify-between h-36 border border-slate-300 p-2.5 rounded-xl">
                    <span class="font-black text-[13px] text-[#2A3956] uppercase tracking-wider">HRD / Pemeriksa</span>
                    <div class="h-20 flex items-center justify-center my-0.5">
                        @php $hrdSign = $employee->getDocument('hrd_signature'); @endphp
                        @if($hrdSign)
                        <img src="{{ $hrdSign->url }}" alt="TTD HRD" class="h-full w-auto max-h-20 max-w-[200px] object-contain">
                        @else
                        <span class="text-[9px] text-slate-300 italic">(Tanda Tangan & Cap)</span>
                        @endif
                    </div>
                    <div class="w-full border-t border-slate-300 pt-1">
                        <span class="font-bold underline text-[11.5px] text-slate-800 block">{{ ($employee->hrd_signature_name && !in_array($employee->hrd_signature_name, ['Super Admin ABP', 'Admin HRD ABP', 'HRD PT ABP'])) ? $employee->hrd_signature_name : 'HR, Admin Manager' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER DOKUMEN RESMI SESUAI PREVIEW -->
        <div class="flex items-center justify-between text-[9px] text-slate-500 pt-2.5 mt-2 border-t border-slate-200 print-footer" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 8px;">
            <span>Form Biodata Karyawan &bull; PT Artha Buana Primacoal &bull; Dicetak: {{ date('d/m/Y H:i') }}</span>
            <span class="font-bold text-slate-600 font-mono">Dokumen Resmi HRD</span>
        </div>

    </div>

    <!-- Script PDF Generator (Lokal dengan fallback CDN) -->
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        async function downloadPdfDirectly() {
            const btn = document.getElementById('btnDownloadPdf');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-sm"></i><span>Sedang Menyiapkan PDF...</span>';

            // Pastikan seluruh font sudah selesai di-render browser
            if (document.fonts) {
                await document.fonts.ready;
            }

            const element = document.querySelector('.print-container');

            // Simpan status class kartu web
            const hadShadow = element.classList.contains('shadow-lg');
            const hadRounded = element.classList.contains('rounded-2xl');
            const hadBorder = element.classList.contains('border');

            // Lepas border kartu dan sudut melengkung sementara agar lembaran PDF kotak rapi
            element.classList.remove('shadow-lg', 'rounded-2xl', 'border', 'border-slate-200');
            element.style.borderRadius = '0px';
            element.style.border = 'none';
            element.style.boxShadow = 'none';

            // Beri jeda agar browser selesai me-repaint tampilan
            await new Promise(resolve => setTimeout(resolve, 150));

            @php
                $cleanEmpName = trim(preg_replace('/[^\p{L}\p{N}\-_]+/u', '_', $employee->full_name), '_');
                $cleanEmpId = trim(preg_replace('/[^\p{L}\p{N}\-_]+/u', '_', $employee->employee_id), '_');
                $pdfFileName = "Biodata_{$cleanEmpName}_{$cleanEmpId}.pdf";
            @endphp
            const fileName = "{{ $pdfFileName }}";

            const opt = {
                margin: [4, 4, 6, 4],
                filename: fileName,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff',
                    scrollY: 0,
                    scrollX: 0
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy'],
                    avoid: ['.print-section', '.print-signature-section', 'tr']
                }
            };

            try {
                await html2pdf().set(opt).from(element).save();
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check text-sm"></i><span>Berhasil Diunduh!</span>';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 3000);
            } catch (err) {
                console.error('Gagal generate PDF langsung:', err);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('Terjadi kendala saat download langsung. Mengalihkan ke jendela cetak...');
                window.print();
            } finally {
                // Kembalikan tampilan kartu web
                if (hadShadow) element.classList.add('shadow-lg');
                if (hadRounded) element.classList.add('rounded-2xl');
                if (hadBorder) element.classList.add('border', 'border-slate-200');
                element.style.borderRadius = '';
                element.style.border = '';
                element.style.boxShadow = '';
            }
        }

        function executePrint() {
            window.print();
        }
    </script>
</body>
</html>
