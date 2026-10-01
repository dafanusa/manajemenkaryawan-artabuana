<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Biodata - {{ $employee->full_name }} ({{ $employee->employee_id }})</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            @page {
                size: A4;
                margin: 12mm 15mm 12mm 15mm;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <!-- Top floating action bar -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-md border border-slate-200">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.show', $employee) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-xs font-bold rounded-xl text-slate-700 transition">
                &larr; Kembali ke Sistem
            </a>
            <span class="text-xs font-semibold text-slate-500">Pratinjau Dokumen Cetak / PDF</span>
        </div>
        <button onclick="window.print()" class="px-5 py-2.5 bg-[#096256] hover:bg-[#074e44] text-white text-xs font-extrabold rounded-xl shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-print"></i>
            <span>Cetak / Simpan PDF Sekarang</span>
        </button>
    </div>

    <!-- PRINT CONTAINER (A4 COMPATIBLE) -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-10 shadow-lg border border-slate-200 rounded-2xl space-y-5 text-[11px] leading-tight text-slate-800">

        <!-- HEADER KOP RESMI PERUSAHAAN -->
        <div class="border-b-2 border-[#2A3956] pb-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PT ABP" class="h-16 object-contain">
                <div>
                    <h1 class="text-lg font-black text-[#2A3956] tracking-tight">PT ARTHA BUANA PRIMACORAL</h1>
                    <p class="text-[10px] text-[#096256] font-bold uppercase tracking-wider">Mining Services, General Contractor & Heavy Equipment Fleet</p>
                    <p class="text-[9px] text-slate-500">Timika Base &bull; Highland Mile 68 / Lowland Operations &bull; Papua Tengah</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[9px] font-bold uppercase text-slate-400 block">Formulir HRD</span>
                <span class="text-xs font-extrabold text-[#2A3956] block">FORM BIODATA KARYAWAN</span>
                <span class="text-[10px] font-mono text-[#096256] font-bold">ID: {{ $employee->employee_id }}</span>
            </div>
        </div>

        <!-- A. DATA PRIBADI -->
        <div class="space-y-2">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] flex justify-between">
                <span>A. DATA PRIBADI</span>
                <span class="font-mono text-[9px] text-teal-200">Status: {{ $employee->employment_status }}</span>
            </div>

            <div class="flex gap-4 items-start">
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
                        <span class="font-bold text-[#096256]">: {{ $employee->ethnicity }}</span>
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
        <div class="space-y-1.5">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                B. KONTAK DARURAT / EMERGENCY CONTACT
            </div>
            @php $c = $employee->emergencyContacts->first(); @endphp
            <div class="grid grid-cols-2 gap-x-4 gap-y-1">
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
        <div class="space-y-1.5">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                C. RIWAYAT PENDIDIKAN TERAKHIR
            </div>
            <table class="w-full text-left border border-slate-300">
                <thead class="bg-slate-100 font-bold border-b border-slate-300">
                    <tr>
                        <th class="p-1.5 border-r border-slate-300 w-16">Jenjang</th>
                        <th class="p-1.5 border-r border-slate-300">Nama Sekolah / Perguruan Tinggi</th>
                        <th class="p-1.5 border-r border-slate-300">Jurusan</th>
                        <th class="p-1.5 border-r border-slate-300 w-20 text-center">Tahun Lulus</th>
                        <th class="p-1.5">No. Ijazah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($employee->educations as $e)
                    <tr>
                        <td class="p-1.5 border-r border-slate-200 font-bold">{{ $e->level }}</td>
                        <td class="p-1.5 border-r border-slate-200">{{ $e->institution_name }}</td>
                        <td class="p-1.5 border-r border-slate-200">{{ $e->major ?? '-' }}</td>
                        <td class="p-1.5 border-r border-slate-200 text-center">{{ $e->graduation_year ?? '-' }}</td>
                        <td class="p-1.5">{{ $e->certificate_number ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-1.5 text-center text-slate-400">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- D. PENGALAMAN KERJA -->
        <div class="space-y-1.5">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                D. PENGALAMAN KERJA TERAKHIR
            </div>
            <table class="w-full text-left border border-slate-300">
                <thead class="bg-slate-100 font-bold border-b border-slate-300">
                    <tr>
                        <th class="p-1.5 border-r border-slate-300">Perusahaan</th>
                        <th class="p-1.5 border-r border-slate-300">Jabatan</th>
                        <th class="p-1.5 border-r border-slate-300 w-28">Periode</th>
                        <th class="p-1.5">Alasan Berhenti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($employee->workExperiences as $w)
                    <tr>
                        <td class="p-1.5 border-r border-slate-200 font-bold">{{ $w->company_name }}</td>
                        <td class="p-1.5 border-r border-slate-200">{{ $w->position ?? '-' }}</td>
                        <td class="p-1.5 border-r border-slate-200">{{ $w->period ?? '-' }}</td>
                        <td class="p-1.5">{{ $w->reason_for_leaving ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-1.5 text-center text-slate-400">Belum ada pengalaman kerja sebelumnya</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- E. KEAHLIAN & ADMINISTRASI -->
        <div class="grid grid-cols-2 gap-4">

            <!-- Keahlian -->
            <div class="space-y-1.5">
                <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                    E. KEAHLIAN & ALAT/SOFTWARE
                </div>
                <div class="border border-slate-200 p-2 rounded space-y-1">
                    <p><strong>Keahlian:</strong> {{ $employee->skills_summary ?? '-' }}</p>
                    <p><strong>Alat/Software:</strong> {{ $employee->tools_software ?? '-' }}</p>
                </div>
            </div>

            <!-- Administrasi -->
            <div class="space-y-1.5">
                <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                    F. DATA ADMINISTRASI
                </div>
                <div class="border border-slate-200 p-2 rounded space-y-1">
                    <p><strong>NPWP:</strong> {{ $employee->npwp_number ?? '-' }} (PTKP: {{ $employee->ptkp_status ?? '-' }})</p>
                    <p><strong>BPJS Kes:</strong> {{ $employee->bpjs_kes_number ?? '-' }} | <strong>BPJS TK:</strong> {{ $employee->bpjs_tk_number ?? '-' }}</p>
                    <p><strong>Bank:</strong> {{ $employee->bank_name ?? '-' }} - {{ $employee->bank_account_number ?? '-' }} (a.n {{ $employee->bank_account_holder ?? '-' }})</p>
                </div>
            </div>

        </div>

        <!-- G. DATA KEPEGAWAIAN -->
        <div class="space-y-1.5">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                G. DATA KEPEGAWAIAN (HRD)
            </div>
            <div class="grid grid-cols-3 gap-x-4 gap-y-1 border border-slate-200 p-2 rounded">
                <p><strong>ID Karyawan:</strong> {{ $employee->employee_id }}</p>
                <p><strong>Jabatan:</strong> {{ $employee->position }}</p>
                <p><strong>Departemen:</strong> {{ $employee->department }}</p>
                <p><strong>Status Kerja:</strong> {{ $employee->employment_status }}</p>
                <p><strong>Lokasi Kerja:</strong> {{ $employee->work_location }}</p>
                <p><strong>Project:</strong> {{ $employee->project_location ?? '-' }}</p>
                <p><strong>Tanggal Masuk:</strong> {{ $employee->join_date ? $employee->join_date->format('d/m/Y') : '-' }}</p>
                <p><strong>Tanggal Keluar:</strong> {{ $employee->leave_date ? $employee->leave_date->format('d/m/Y') : '-' }}</p>
                <p><strong>Cuti Terakhir:</strong> {{ $employee->last_leave_date ? $employee->last_leave_date->format('d/m/Y') : '-' }}</p>
            </div>
        </div>

        <!-- H. GAJI & TUNJANGAN -->
        <div class="space-y-1.5">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                H. GAJI DAN TUNJANGAN
            </div>
            <div class="grid grid-cols-3 gap-x-4 gap-y-1 border border-slate-200 p-2 rounded">
                <p><strong>Gaji Pokok:</strong> Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</p>
                <p><strong>Gaji Per Jam:</strong> Rp {{ number_format($employee->hourly_rate, 0, ',', '.') }}</p>
                <p><strong>Lama Kerja Sebelumnya:</strong> {{ $employee->previous_work_years }} Thn {{ $employee->previous_work_months }} Bln</p>
            </div>
        </div>

        <!-- I. PERNYATAAN & TANDA TANGAN -->
        <div class="space-y-2 pt-1">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                I. PERNYATAAN
            </div>
            <p class="italic text-[10px] text-slate-600">
                "Saya menyatakan bahwa seluruh data yang saya berikan dalam formulir ini adalah benar dan dapat dipertanggungjawabkan. Saya bersedia memberitahukan kepada perusahaan apabila terdapat perubahan data."
            </p>

            <div class="grid grid-cols-2 gap-8 pt-2 text-center">
                <!-- TTD Karyawan -->
                <div class="flex flex-col items-center justify-between h-32 border border-slate-300 p-2 rounded">
                    <span class="font-bold">Karyawan</span>
                    <div class="h-14 flex items-center justify-center">
                        @php $empSign = $employee->getDocument('employee_signature'); @endphp
                        @if($empSign)
                        <img src="{{ $empSign->url }}" alt="TTD" class="max-h-full max-w-[120px] object-contain">
                        @else
                        <span class="text-[9px] text-slate-300 italic">(Tanda Tangan)</span>
                        @endif
                    </div>
                    <span class="font-bold underline">{{ $employee->employee_signature_name ?? $employee->full_name }}</span>
                </div>

                <!-- TTD HRD -->
                <div class="flex flex-col items-center justify-between h-32 border border-slate-300 p-2 rounded">
                    <span class="font-bold">HRD / Pemeriksa</span>
                    <div class="h-14 flex items-center justify-center">
                        @php $hrdSign = $employee->getDocument('hrd_signature'); @endphp
                        @if($hrdSign)
                        <img src="{{ $hrdSign->url }}" alt="TTD HRD" class="max-h-full max-w-[120px] object-contain">
                        @else
                        <span class="text-[9px] text-slate-300 italic">(Tanda Tangan & Cap)</span>
                        @endif
                    </div>
                    <span class="font-bold underline">{{ $employee->hrd_signature_name ?? 'HRD PT ABP' }}</span>
                </div>
            </div>
        </div>

        <div class="text-center text-[9px] text-slate-400 pt-2 border-t border-slate-200">
            Form Biodata Karyawan &bull; PT Artha Buana Primacoral &bull; Dicetak: {{ date('d/m/Y H:i') }}
        </div>

    </div>

</body>
</html>
