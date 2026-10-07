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
                margin: 10mm 12mm 10mm 12mm;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <!-- Top floating action bar -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-md border border-slate-200">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.show', $employee) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-xs font-bold rounded-xl text-slate-700 transition">
                &larr; Kembali ke Detail Karyawan
            </a>
            <span class="text-xs font-semibold text-slate-500">Pratinjau Dokumen Cetak / PDF</span>
        </div>
        <button onclick="window.print()" class="px-5 py-2.5 bg-[#096256] hover:bg-[#074e44] text-white text-xs font-extrabold rounded-xl shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-print"></i>
            <span>Cetak / Simpan PDF Sekarang</span>
        </button>
    </div>

    <!-- PRINT CONTAINER (A4 COMPATIBLE) -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-10 shadow-lg border border-slate-200 rounded-2xl space-y-4 text-[10.5px] leading-tight text-slate-800">

        <!-- HEADER KOP RESMI PERUSAHAAN -->
        <div class="border-b-2 border-[#2A3956] pb-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PT ABP" class="h-14 object-contain">
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
        <div class="space-y-1.5">
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
        <div class="space-y-1">
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
        <div class="space-y-1">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
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
        <div class="space-y-1">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
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
        <div class="space-y-1">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                E. KEAHLIAN & SERTIFIKAT KOMPETENSI
            </div>
            <div class="grid grid-cols-2 gap-3 border border-slate-200 p-2 rounded">
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

        <!-- F. DATA ADMINISTRASI & SIM / LICENSE -->
        <div class="space-y-1">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                F. DATA ADMINISTRASI & SIM / LICENSE
            </div>
            <div class="border border-slate-200 p-2 rounded space-y-1">
                <div class="grid grid-cols-3 gap-x-4">
                    <p><strong>NPWP:</strong> {{ $employee->npwp_number ?? '-' }} (PTKP: {{ $employee->ptkp_status ?? '-' }})</p>
                    <p><strong>BPJS Kes:</strong> {{ $employee->bpjs_kes_number ?? '-' }}</p>
                    <p><strong>BPJS TK:</strong> {{ $employee->bpjs_tk_number ?? '-' }}</p>
                    <p class="col-span-3"><strong>Bank:</strong> {{ $employee->bank_name ?? '-' }} - {{ $employee->bank_account_number ?? '-' }} (a.n {{ $employee->bank_account_holder ?? '-' }})</p>
                </div>

                @if($employee->licenses->count() > 0)
                <div class="pt-1 border-t border-slate-100">
                    <span class="font-bold text-slate-700 block mb-0.5">Daftar SIM / License Kerja:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($employee->licenses as $lic)
                        <div class="border border-slate-200 p-1 rounded bg-slate-50 text-[10px]">
                            <strong>{{ $lic->license_type }}</strong>: {{ $lic->license_number ?? '-' }}
                            <div class="text-[9px] text-slate-500">Berlaku: {{ $lic->expiry_date ? $lic->expiry_date->format('d/m/Y') : '-' }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- G. DATA KEPEGAWAIAN (HRD) -->
        <div class="space-y-1">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                G. DATA KEPEGAWAIAN (HRD)
            </div>
            <div class="grid grid-cols-3 gap-x-4 gap-y-1 border border-slate-200 p-2 rounded">
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
            <div class="pt-1">
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
        <div class="space-y-1">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px] flex justify-between">
                <span>H. GAJI & RIWAYAT CUTI</span>
                @if(Auth::user()->isSuperAdmin())
                <span class="text-[9px] text-teal-200">Confidential</span>
                @endif
            </div>

            <!-- SALARY DETAILS (SUPER ADMIN ONLY) -->
            @if(Auth::user()->isSuperAdmin())
            <div class="border border-slate-200 p-2 rounded space-y-1 bg-teal-50/20">
                <div class="grid grid-cols-4 gap-2">
                    <p><strong>Grade:</strong> {{ $employee->line_grade ?? '-' }}</p>
                    <p><strong>Level:</strong> {{ $employee->salary_level ?? '-' }}</p>
                    <p><strong>Gaji Pokok:</strong> Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</p>
                    <p><strong>Gaji Per Jam:</strong> Rp {{ number_format($employee->hourly_rate, 0, ',', '.') }}</p>
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
            <div class="pt-0.5">
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

        <!-- I. PERNYATAAN & TANDA TANGAN -->
        <div class="space-y-1.5 pt-1">
            <div class="bg-[#2A3956] text-white font-bold py-1 px-2.5 rounded text-[11px]">
                I. PERNYATAAN
            </div>
            <p class="italic text-[9.5px] text-slate-600">
                "Saya menyatakan bahwa seluruh data yang saya berikan dalam formulir ini adalah benar dan dapat dipertanggungjawabkan. Saya bersedia memberitahukan kepada perusahaan apabila terdapat perubahan data."
            </p>

            <div class="grid grid-cols-2 gap-8 pt-1 text-center">
                <!-- TTD Karyawan -->
                <div class="flex flex-col items-center justify-between h-28 border border-slate-300 p-2 rounded">
                    <span class="font-bold text-[10px]">Karyawan yang Bersangkutan</span>
                    <div class="h-12 flex items-center justify-center">
                        @php $empSign = $employee->getDocument('employee_signature'); @endphp
                        @if($empSign)
                        <img src="{{ $empSign->url }}" alt="TTD" class="max-h-full max-w-[120px] object-contain">
                        @else
                        <span class="text-[9px] text-slate-300 italic">(Tanda Tangan)</span>
                        @endif
                    </div>
                    <span class="font-bold underline text-[10px]">{{ $employee->employee_signature_name ?? $employee->full_name }}</span>
                </div>

                <!-- TTD HRD -->
                <div class="flex flex-col items-center justify-between h-28 border border-slate-300 p-2 rounded">
                    <span class="font-bold text-[10px]">HRD / Pemeriksa</span>
                    <div class="h-12 flex items-center justify-center">
                        @php $hrdSign = $employee->getDocument('hrd_signature'); @endphp
                        @if($hrdSign)
                        <img src="{{ $hrdSign->url }}" alt="TTD HRD" class="max-h-full max-w-[120px] object-contain">
                        @else
                        <span class="text-[9px] text-slate-300 italic">(Tanda Tangan & Cap)</span>
                        @endif
                    </div>
                    <span class="font-bold underline text-[10px]">{{ $employee->hrd_signature_name ?? 'HRD PT ABP' }}</span>
                </div>
            </div>
        </div>

        <div class="text-center text-[8.5px] text-slate-400 pt-1 border-t border-slate-200">
            Form Biodata Karyawan &bull; PT Artha Buana Primacoral &bull; Dicetak: {{ date('d/m/Y H:i') }}
        </div>

    </div>

</body>
</html>
