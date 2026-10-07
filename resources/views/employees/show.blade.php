@extends('layouts.app')

@section('title', 'Preview Biodata - ' . $employee->full_name)
@section('page_title', 'Preview Biodata Karyawan: ' . $employee->full_name)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- ACTION TOOLBAR (NO PRINT) -->
    <div class="no-print bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">

        <div class="flex items-center gap-3">
            <a href="{{ route('employees.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>

            <div class="h-6 w-px bg-slate-200"></div>

            <!-- Status Indicator with Quick Update Modal Trigger -->
            <div class="flex items-center gap-2">
                @if($employee->employment_status === 'Aktif')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Status: Aktif
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Status: Tidak Aktif
                </span>
                @endif

                <span class="text-xs font-semibold text-slate-400">| ID: {{ $employee->employee_id }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">

            <!-- Quick Status Change Button -->
            <form method="POST" action="{{ route('employees.updateStatus', $employee) }}" class="inline">
                @csrf
                @method('PATCH')
                <input type="hidden" name="employment_status" value="{{ $employee->employment_status === 'Aktif' ? 'Tidak Aktif' : 'Aktif' }}">
                @if($employee->employment_status === 'Aktif')
                <input type="hidden" name="leave_date" value="{{ date('Y-m-d') }}">
                @endif
                <button type="submit" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    <i class="fa-solid fa-arrows-rotate mr-1 text-slate-400"></i>
                    <span>Set {{ $employee->employment_status === 'Aktif' ? 'Tidak Aktif' : 'Aktif' }}</span>
                </button>
            </form>

            <!-- Edit Button -->
            <a href="{{ route('employees.edit', $employee) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-brand-navy hover:text-white text-xs font-bold text-slate-700 transition flex items-center gap-1.5">
                <i class="fa-regular fa-pen-to-square"></i>
                <span>Edit Data</span>
            </a>

            <!-- Print / Download PDF -->
            <a href="{{ route('employees.print', $employee) }}" target="_blank" class="px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold shadow-md shadow-brand-primary/20 transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Cetak / Download PDF</span>
            </a>

            @if(Auth::user()->isSuperAdmin())
            <!-- Delete Button (Super Admin Only) -->
            <button type="button"
                    @click="openConfirm(
                        'Hapus Karyawan',
                        'Apakah Anda yakin ingin menghapus data {{ $employee->full_name }}? Aksi ini tidak dapat dibatalkan.',
                        '{{ route('employees.destroy', $employee) }}',
                        'DELETE',
                        'Ya, Hapus',
                        true
                    )"
                    class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-600 hover:text-white text-xs font-bold text-rose-600 transition"
                    title="Hapus Data (Super Admin)">
                <i class="fa-regular fa-trash-can"></i>
            </button>
            @endif

        </div>

    </div>

    <!-- DOCUMENT STATUS CHECKLIST CARD (NO PRINT) -->
    <div class="no-print bg-white p-5 rounded-3xl border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-folder-tree text-brand-primary"></i>
                <span>Status Kelengkapan Dokumen Berkas</span>
            </h4>
            @if($employee->isDocumentsComplete())
            <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                ✓ Lengkap
            </span>
            @else
            <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                ⚠ {{ count($employee->missingDocuments()) }} Dokumen Belum Tersedia
            </span>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2.5 text-xs">
            @php
                $checkList = [
                    'photo' => 'Pas Foto 3x4',
                    'ktp' => 'KTP / NIK',
                    'kk' => 'Kartu Keluarga',
                    'id_card' => 'ID Card',
                    'npwp' => 'NPWP',
                    'bpjs_kes' => 'BPJS Kesehatan',
                    'bpjs_tk' => 'BPJS TK',
                    'bank_book' => 'Buku Rekening',
                ];
                if (Auth::user()->isSuperAdmin()) {
                    $checkList['salary_slip'] = 'Slip Gaji';
                }
                $checkList['leave_form'] = 'Form Cuti/Tiket';
                $checkList['employee_signature'] = 'TTD Karyawan';
                $checkList['hrd_signature'] = 'TTD HRD';
            @endphp

            @foreach($checkList as $docKey => $docLabel)
                @php $doc = $employee->getDocument($docKey); @endphp
                <div class="p-2.5 rounded-xl border {{ $doc ? 'bg-emerald-50/60 border-emerald-200' : 'bg-slate-50 border-slate-200' }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-bold {{ $doc ? 'text-emerald-800' : 'text-slate-500' }} truncate">{{ $docLabel }}</span>
                        @if($doc)
                        <span class="text-emerald-600 text-xs font-bold">✓</span>
                        @else
                        <span class="text-slate-400 text-xs font-bold">⚠</span>
                        @endif
                    </div>
                    @if($doc)
                    <div class="flex items-center gap-1.5 mt-1">
                        <button type="button"
                                @click="openFilePreview('{{ $doc->url }}', '{{ $docLabel }}', '{{ $doc->original_name ?? basename($doc->file_path) }}')"
                                class="text-[10px] text-teal-700 hover:text-teal-900 font-bold flex items-center gap-1 hover:underline">
                            <i class="fa-solid fa-eye text-[9px]"></i>
                            <span>Lihat</span>
                        </button>
                        <span class="text-slate-300 text-[10px]">&bull;</span>
                        <a href="{{ $doc->url }}" download class="text-[10px] text-slate-500 hover:text-slate-700 font-medium" title="Unduh File">
                            <i class="fa-solid fa-download text-[9px]"></i>
                        </a>
                    </div>
                    @else
                    <span class="text-[10px] text-slate-400 italic block mt-1">Belum ada</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- OFFICIAL CORPORATE FORM BIODATA DOCUMENT (PRINTABLE CONTAINER) -->
    <!-- ============================================================== -->
    <div class="bg-white p-8 sm:p-10 rounded-3xl border border-slate-200 shadow-md printable-document space-y-6 text-slate-800">

        <!-- HEADER KOP PERUSAHAAN (MATCHING OFFICIAL ABP BIODATA FORM) -->
        <div class="border-b-2 border-brand-navy pb-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PT ABP" class="h-16 object-contain">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-brand-navy tracking-tight leading-tight">
                        PT ARTHA BUANA PRIMACORAL
                    </h1>
                    <p class="text-[11px] text-brand-primary font-bold uppercase tracking-wider">
                        Mining Services, General Contractor & Heavy Equipment Fleet
                    </p>
                    <p class="text-[10px] text-slate-500">
                        Timika Base &bull; Highland Mile 68 / Lowland Operations &bull; Papua Tengah
                    </p>
                </div>
            </div>

            <!-- Form Badge -->
            <div class="text-right border-l border-slate-200 pl-4 hidden sm:block">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Formulir HRD</span>
                <span class="text-sm font-extrabold text-brand-navy block">FORM BIODATA KARYAWAN</span>
                <span class="text-[10px] font-mono text-teal-700 font-bold">ID: {{ $employee->employee_id }}</span>
            </div>
        </div>

        <!-- A. DATA PRIBADI & FOTO -->
        <div class="space-y-4">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg flex items-center justify-between">
                <span>A. DATA PRIBADI</span>
                <span class="text-[10px] text-teal-200 uppercase font-mono">Status: {{ $employee->employment_status }}</span>
            </div>

            <div class="flex flex-col md:flex-row gap-6 items-start">

                <!-- Pas Foto 3x4 -->
                <div class="w-32 h-44 rounded-xl border border-slate-300 overflow-hidden flex-shrink-0 bg-slate-100 flex items-center justify-center shadow-sm relative group">
                    @if($employee->photo)
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->full_name }}" class="w-full h-full object-cover">
                    <button type="button"
                            @click="openFilePreview('{{ asset('storage/' . $employee->photo) }}', 'Pas Foto - {{ $employee->full_name }}', 'jpg')"
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                        <i class="fa-solid fa-eye"></i>
                        <span>Lihat</span>
                    </button>
                    @else
                    <div class="text-center p-2 text-slate-400">
                        <i class="fa-solid fa-user text-3xl mb-1 text-slate-300"></i>
                        <span class="text-[10px] uppercase font-bold block">Pas Foto<br>3 x 4</span>
                    </div>
                    @endif
                </div>

                <!-- Info Grid -->
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5 text-xs">

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Nama Lengkap</span>
                        <span class="font-bold text-brand-navy">: {{ $employee->full_name }}</span>
                    </div>

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Suku</span>
                        <span class="font-bold text-teal-800">: {{ $employee->ethnicity }}</span>
                    </div>

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Tempat / Tgl. Lahir</span>
                        <span class="font-medium">: {{ $employee->birth_place ?? '-' }}, {{ $employee->birth_date ? $employee->birth_date->format('d F Y') : '-' }}</span>
                    </div>

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Jenis Kelamin</span>
                        <span class="font-medium">: {{ $employee->gender }}</span>
                    </div>

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Agama</span>
                        <span class="font-medium">: {{ $employee->religion }}</span>
                    </div>

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Status Perkawinan</span>
                        <span class="font-medium">: {{ $employee->marital_status ?? '-' }}</span>
                    </div>

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">No. KTP / NIK</span>
                        <span class="font-medium font-mono">: {{ $employee->nik ?? '-' }}</span>
                    </div>

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">No. Kartu Keluarga (KK)</span>
                        <span class="font-medium font-mono">: {{ $employee->kk_number ?? '-' }}</span>
                    </div>

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">No. HP / WhatsApp</span>
                        <span class="font-medium">: {{ $employee->phone ?? '-' }}</span>
                    </div>

                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Email</span>
                        <span class="font-medium">: {{ $employee->email ?? '-' }}</span>
                    </div>

                    <div class="sm:col-span-2 flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Alamat KTP</span>
                        <span class="font-medium">: {{ $employee->ktp_address ?? '-' }}</span>
                    </div>

                    <div class="sm:col-span-2 flex border-b border-slate-100 pb-1.5">
                        <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Alamat Domisili</span>
                        <span class="font-medium">: {{ $employee->domicile_address ?? '-' }}</span>
                    </div>

                </div>

            </div>
        </div>

        <!-- B. KONTAK DARURAT -->
        <div class="space-y-3">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg">
                B. KONTAK DARURAT / EMERGENCY CONTACT
            </div>

            @php $contact = $employee->emergencyContacts->first(); @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-xs">
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Nama Kontak</span>
                    <span class="font-medium">: {{ $contact->name ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Hubungan</span>
                    <span class="font-medium">: {{ $contact->relationship ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">No. HP / WhatsApp</span>
                    <span class="font-medium">: {{ $contact->phone ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">No. Alternatif</span>
                    <span class="font-medium">: {{ $contact->alternative_phone ?? '-' }}</span>
                </div>
                <div class="sm:col-span-2 flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Alamat</span>
                    <span class="font-medium">: {{ $contact->address ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- C. RIWAYAT PENDIDIKAN TERAKHIR -->
        <div class="space-y-3">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg">
                C. RIWAYAT PENDIDIKAN TERAKHIR
            </div>

            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-50 font-bold text-slate-700 border-b border-slate-200 text-[11px]">
                    <tr>
                        <th class="py-2 px-3 border-r border-slate-200 w-16">Jenjang</th>
                        <th class="py-2 px-3 border-r border-slate-200">Nama Sekolah / Perguruan Tinggi</th>
                        <th class="py-2 px-3 border-r border-slate-200">Jurusan / Program Studi</th>
                        <th class="py-2 px-3 border-r border-slate-200 w-24 text-center">Tahun Lulus</th>
                        <th class="py-2 px-3 border-r border-slate-200">No. Ijazah</th>
                        <th class="py-2 px-3 text-center w-20">Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($employee->educations as $edu)
                    <tr>
                        <td class="py-2 px-3 border-r border-slate-200 font-bold">{{ $edu->level }}</td>
                        <td class="py-2 px-3 border-r border-slate-200">{{ $edu->institution_name }}</td>
                        <td class="py-2 px-3 border-r border-slate-200">{{ $edu->major ?? '-' }}</td>
                        <td class="py-2 px-3 border-r border-slate-200 text-center">{{ $edu->graduation_year ?? '-' }}</td>
                        <td class="py-2 px-3 border-r border-slate-200">{{ $edu->certificate_number ?? '-' }}</td>
                        <td class="py-2 px-3 text-center">
                            @if($edu->document_path)
                            <button type="button"
                                    @click="openFilePreview('{{ asset('storage/' . $edu->document_path) }}', 'Ijazah - {{ $edu->institution_name }}', '{{ $educationDoc?->original_name ?? basename($edu->document_path) }}')"
                                    class="text-teal-700 font-bold hover:underline inline-flex items-center gap-1">
                                <i class="fa-solid fa-eye text-[10px]"></i>
                                <span>Lihat</span>
                            </button>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-2 px-3 text-center text-slate-400">Belum ada riwayat pendidikan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- D. PENGALAMAN KERJA TERAKHIR -->
        <div class="space-y-3">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg">
                D. PENGALAMAN KERJA TERAKHIR
            </div>

            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-50 font-bold text-slate-700 border-b border-slate-200 text-[11px]">
                    <tr>
                        <th class="py-2 px-3 border-r border-slate-200">Perusahaan</th>
                        <th class="py-2 px-3 border-r border-slate-200">Jabatan</th>
                        <th class="py-2 px-3 border-r border-slate-200 w-32">Periode</th>
                        <th class="py-2 px-3 border-r border-slate-200">Alasan Berhenti</th>
                        <th class="py-2 px-3 text-center w-20">Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($employee->workExperiences as $exp)
                    <tr>
                        <td class="py-2 px-3 border-r border-slate-200 font-bold">{{ $exp->company_name }}</td>
                        <td class="py-2 px-3 border-r border-slate-200">{{ $exp->position ?? '-' }}</td>
                        <td class="py-2 px-3 border-r border-slate-200">{{ $exp->period ?? '-' }}</td>
                        <td class="py-2 px-3 border-r border-slate-200">{{ $exp->reason_for_leaving ?? '-' }}</td>
                        <td class="py-2 px-3 text-center">
                            @if($exp->document_path)
                            <button type="button"
                                    @click="openFilePreview('{{ asset('storage/' . $exp->document_path) }}', 'Surat Pengalaman - {{ $exp->company_name }}', '{{ $experienceDoc?->original_name ?? basename($exp->document_path) }}')"
                                    class="text-teal-700 font-bold hover:underline inline-flex items-center gap-1">
                                <i class="fa-solid fa-eye text-[10px]"></i>
                                <span>Lihat</span>
                            </button>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-2 px-3 text-center text-slate-400">Belum ada catatan pengalaman kerja sebelumnya.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- E. KEAHLIAN & SERTIFIKAT KOMPETENSI -->
        <div class="space-y-4">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg">
                E. KEAHLIAN DAN SERTIFIKAT KOMPETENSI
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-bold text-slate-700 block mb-1">Keahlian Utama:</span>
                    <p class="text-slate-600 leading-relaxed">{{ $employee->skills_summary ?? '-' }}</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-bold text-slate-700 block mb-1">Alat / Software yang Dikuasai:</span>
                    <p class="text-slate-600 leading-relaxed">{{ $employee->tools_software ?? '-' }}</p>
                </div>
            </div>

            <!-- Relational Skills (Repeater Data) -->
            @if($employee->skills->count() > 0)
            <div class="space-y-2 pt-1">
                <span class="text-xs font-bold text-brand-navy block">Daftar Keahlian Spesifik Karyawan:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    @foreach($employee->skills as $idx => $sk)
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-bold text-slate-800 text-xs">{{ $sk->skill_name }}</span>
                            @if($sk->proficiency_level)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-brand-primary border border-teal-200 whitespace-nowrap">
                                {{ $sk->proficiency_level }}
                            </span>
                            @endif
                        </div>
                        @if($sk->notes)
                        <p class="text-[11px] text-slate-500 mt-1 italic">{{ $sk->notes }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Certificates -->
            @if($employee->certificates->count() > 0)
            <div class="space-y-2 pt-1">
                <span class="text-xs font-bold text-brand-navy block">Sertifikat Kompetensi:</span>
                <table class="w-full text-left text-xs border border-slate-200">
                    <thead class="bg-slate-50 font-bold text-slate-700 border-b border-slate-200 text-[11px]">
                        <tr>
                            <th class="py-2 px-3 border-r border-slate-200">Nama Sertifikat</th>
                            <th class="py-2 px-3 border-r border-slate-200">Nomor Sertifikat</th>
                            <th class="py-2 px-3 border-r border-slate-200 w-32">Masa Berlaku</th>
                            <th class="py-2 px-3 text-center w-20">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($employee->certificates as $cert)
                        <tr>
                            <td class="py-2 px-3 border-r border-slate-200 font-bold">{{ $cert->certificate_name }}</td>
                            <td class="py-2 px-3 border-r border-slate-200">{{ $cert->certificate_number ?? '-' }}</td>
                            <td class="py-2 px-3 border-r border-slate-200">{{ $cert->valid_until ?? '-' }}</td>
                            <td class="py-2 px-3 text-center">
                                @if($cert->document_path)
                                <button type="button"
                                        @click="openFilePreview('{{ asset('storage/' . $cert->document_path) }}', 'Sertifikat - {{ $cert->certificate_name }}', '{{ $certificateDoc?->original_name ?? basename($cert->document_path) }}')"
                                        class="text-teal-700 font-bold hover:underline inline-flex items-center gap-1">
                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                    <span>Lihat</span>
                                </button>
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- F. DATA ADMINISTRASI & SIM / LICENSE -->
        <div class="space-y-4">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg">
                F. DATA ADMINISTRASI & SURAT IZIN MENGEPENGEMUDI (SIM / LICENSE)
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-2 text-xs">
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">NPWP / NIK Pajak</span>
                    <span class="font-medium font-mono">: {{ $employee->npwp_number ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Status PTKP</span>
                    <span class="font-bold text-teal-800">: {{ $employee->ptkp_status ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">BPJS Kesehatan</span>
                    <span class="font-medium font-mono">: {{ $employee->bpjs_kes_number ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">BPJS Ketenagakerjaan</span>
                    <span class="font-medium font-mono">: {{ $employee->bpjs_tk_number ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Nama Bank</span>
                    <span class="font-medium">: {{ $employee->bank_name ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">No. Rekening Bank</span>
                    <span class="font-bold text-brand-navy font-mono">: {{ $employee->bank_account_number ?? '-' }}</span>
                </div>
                <div class="sm:col-span-3 flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Nama Pemilik Rekening</span>
                    <span class="font-medium">: {{ $employee->bank_account_holder ?? '-' }}</span>
                </div>
            </div>

            <!-- SIM / LICENSE LIST -->
            @if($employee->licenses->count() > 0)
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-brand-navy flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-brand-primary"></i>
                        <span>Daftar SIM & License Kerja:</span>
                    </span>
                    <span class="text-[10px] text-slate-400">{{ $employee->licenses->count() }} Dokumen License</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 font-bold text-slate-700 border-b border-slate-200 text-[11px]">
                            <tr>
                                <th class="py-2 px-3 border-r border-slate-200">Jenis SIM / License</th>
                                <th class="py-2 px-3 border-r border-slate-200">Nomor</th>
                                <th class="py-2 px-3 border-r border-slate-200">Tanggal Terbit</th>
                                <th class="py-2 px-3 border-r border-slate-200">Berlaku Sampai</th>
                                <th class="py-2 px-3 border-r border-slate-200 text-center">Status</th>
                                <th class="py-2 px-3 text-center w-24">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($employee->licenses as $lic)
                            <tr>
                                <td class="py-2.5 px-3 border-r border-slate-200 font-bold text-slate-800">{{ $lic->license_type }}</td>
                                <td class="py-2.5 px-3 border-r border-slate-200 font-mono text-brand-navy">{{ $lic->license_number ?? '-' }}</td>
                                <td class="py-2.5 px-3 border-r border-slate-200">{{ $lic->issue_date ? $lic->issue_date->format('d/m/Y') : '-' }}</td>
                                <td class="py-2.5 px-3 border-r border-slate-200">{{ $lic->expiry_date ? $lic->expiry_date->format('d/m/Y') : '-' }}</td>
                                <td class="py-2.5 px-3 border-r border-slate-200 text-center">
                                    @php
                                        $isExpired = $lic->expiry_date && $lic->expiry_date->isPast();
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $isExpired ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                        {{ $isExpired ? 'Kedaluwarsa' : 'Aktif' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($lic->document_path)
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                @click="openFilePreview('{{ asset('storage/' . $lic->document_path) }}', 'SIM/License - {{ $lic->license_type }}', '{{ $licenseDoc?->original_name ?? basename($lic->document_path) }}')"
                                                class="text-teal-700 font-bold hover:underline inline-flex items-center gap-1">
                                            <i class="fa-solid fa-eye text-[10px]"></i>
                                            <span>Lihat</span>
                                        </button>
                                        <span class="text-slate-300">&bull;</span>
                                        <a href="{{ asset('storage/' . $lic->document_path) }}" download class="text-slate-500 hover:text-slate-700">
                                            <i class="fa-solid fa-download text-[10px]"></i>
                                        </a>
                                    </div>
                                    @else
                                    <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- G. DATA KEPEGAWAIAN (DIISI HRD) -->
        <div class="space-y-4">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg flex items-center justify-between">
                <span>G. DATA KEPEGAWAIAN (DIISI HRD)</span>
                <span class="text-[10px] text-teal-200">PT Artha Buana Primacoral</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-2 text-xs">
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">ID Karyawan</span>
                    <span class="font-extrabold text-brand-navy font-mono">: {{ $employee->employee_id }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Jabatan</span>
                    <span class="font-bold text-slate-800">: {{ $employee->position }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Departemen</span>
                    <span class="font-medium">: {{ $employee->department }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Status Kerja</span>
                    <span class="font-bold {{ $employee->employment_status === 'Aktif' ? 'text-emerald-700' : 'text-rose-700' }}">: {{ $employee->employment_status }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Jenis Kontrak</span>
                    <span class="font-bold text-indigo-700">: {{ $employee->contract_type ?? 'PKWT' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Lokasi Kerja</span>
                    <span class="font-bold text-brand-primary">: {{ $employee->work_location }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Project / Lokasi</span>
                    <span class="font-medium">: {{ $employee->project_location ?? '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Tanggal Masuk</span>
                    <span class="font-medium">: {{ $employee->join_date ? $employee->join_date->format('d F Y') : '-' }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-36 font-semibold text-slate-500 flex-shrink-0">Tanggal Keluar</span>
                    <span class="font-medium">: {{ $employee->leave_date ? $employee->leave_date->format('d F Y') : '-' }}</span>
                </div>
            </div>

            <!-- RIWAYAT KONTRAK KARYAWAN PKWT -->
            @if($employee->contracts->count() > 0)
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-brand-navy flex items-center gap-2">
                            <i class="fa-solid fa-file-contract text-brand-primary"></i>
                            <span>Riwayat Kontrak Karyawan (PKWT)</span>
                        </span>
                        <span class="text-[11px] text-slate-500">
                            Karyawan ini telah mengalami <strong class="text-brand-primary">{{ max(0, $employee->contracts->count() - 1) }} kali perpanjangan kontrak</strong> (Total {{ $employee->contracts->count() }} kontrak)
                        </span>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                        Histori Lengkap
                    </span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 font-bold text-slate-700 border-b border-slate-200 text-[11px]">
                            <tr>
                                <th class="py-2 px-3 border-r border-slate-200 w-24">Tahap Kontrak</th>
                                <th class="py-2 px-3 border-r border-slate-200">Nomor Kontrak</th>
                                <th class="py-2 px-3 border-r border-slate-200">Periode Kontrak</th>
                                <th class="py-2 px-3 border-r border-slate-200">Jabatan & Dept</th>
                                <th class="py-2 px-3 border-r border-slate-200">Lokasi / Proyek</th>
                                <th class="py-2 px-3 border-r border-slate-200">Keterangan</th>
                                <th class="py-2 px-3 text-center w-24">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($employee->contracts()->orderBy('contract_sequence', 'asc')->get() as $idx => $cnt)
                            <tr class="{{ $loop->last ? 'bg-teal-50/30' : '' }}">
                                <td class="py-2.5 px-3 border-r border-slate-200 font-bold text-brand-navy">
                                    Kontrak {{ $cnt->contract_sequence ?? ($idx + 1) }}
                                    @if($loop->last)
                                    <span class="block text-[9px] text-emerald-700 font-semibold">(Terkini)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-200 font-mono text-slate-800 font-semibold">{{ $cnt->contract_number }}</td>
                                <td class="py-2.5 px-3 border-r border-slate-200 whitespace-nowrap">
                                    {{ $cnt->start_date ? $cnt->start_date->format('d/m/Y') : '-' }} s/d {{ $cnt->end_date ? $cnt->end_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-200">
                                    <span class="font-semibold block">{{ $cnt->position }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $cnt->department }}</span>
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-200 text-slate-600">{{ $cnt->project_location ?? '-' }}</td>
                                <td class="py-2.5 px-3 border-r border-slate-200 text-slate-500">{{ $cnt->notes ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($cnt->document_path)
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                @click="openFilePreview('{{ asset('storage/' . $cnt->document_path) }}', 'Kontrak {{ $cnt->contract_sequence }} - {{ $cnt->contract_number }}', '{{ $contractDoc?->original_name ?? basename($cnt->document_path) }}')"
                                                class="text-teal-700 font-bold hover:underline inline-flex items-center gap-1">
                                            <i class="fa-solid fa-eye text-[10px]"></i>
                                            <span>Lihat</span>
                                        </button>
                                        <span class="text-slate-300">&bull;</span>
                                        <a href="{{ asset('storage/' . $cnt->document_path) }}" download class="text-slate-500 hover:text-slate-700">
                                            <i class="fa-solid fa-download text-[10px]"></i>
                                        </a>
                                    </div>
                                    @else
                                    <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- H. GAJI DAN CUTI KARYAWAN -->
        <div class="space-y-4">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg flex items-center justify-between">
                <span>H. GAJI DAN CUTI KARYAWAN</span>
                <span class="text-[10px] text-teal-200">Informasi Finansial & Kehadiran</span>
            </div>

            <!-- GAJI SECTION (HAK AKSES SUPER ADMIN ONLY) -->
            @if(Auth::user()->isSuperAdmin())
            <div class="space-y-4 p-4 rounded-2xl bg-teal-50/40 border border-teal-200">
                <div class="flex items-center justify-between border-b border-teal-100 pb-2">
                    <span class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-money-bill-wave text-brand-primary"></i>
                        <span>Data Gaji Karyawan (Super Admin)</span>
                    </span>
                    <span class="text-[10px] font-bold text-teal-800 bg-teal-100/70 px-2.5 py-0.5 rounded-full border border-teal-200">
                        Sensitif / Confidential
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    <div class="p-3 bg-white rounded-xl border border-teal-100 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Line Grade</span>
                        <span class="text-base font-extrabold text-brand-navy">{{ $employee->line_grade ?? '-' }}</span>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-teal-100 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Salary Level</span>
                        <span class="text-base font-extrabold text-brand-navy">{{ $employee->salary_level ?? '-' }}</span>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-teal-100 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Gaji Pokok</span>
                        <span class="text-base font-extrabold text-brand-navy font-mono">Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-teal-100 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Gaji Per Jam</span>
                        <span class="text-base font-extrabold text-brand-navy font-mono">Rp {{ number_format($employee->hourly_rate, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-44 font-semibold text-slate-500 flex-shrink-0">Posisi Saat Ini</span>
                        <span class="font-medium">: {{ $employee->current_position ?? $employee->position }}</span>
                    </div>
                    <div class="flex border-b border-slate-100 pb-1.5">
                        <span class="w-44 font-semibold text-slate-500 flex-shrink-0">Lama Kerja Sebelumnya</span>
                        <span class="font-medium">: {{ $employee->previous_work_years }} Tahun, {{ $employee->previous_work_months }} Bulan</span>
                    </div>
                </div>

                <!-- Tunjangan List -->
                @if($employee->allowances->count() > 0)
                <div class="p-3.5 bg-white rounded-xl border border-teal-100 space-y-1.5">
                    <span class="text-[11px] font-bold text-slate-700 block">Tunjangan Lainnya:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        @foreach($employee->allowances as $alw)
                        <div class="flex items-center justify-between border-b border-slate-100 pb-1">
                            <span class="text-slate-700 font-medium">{{ $alw->allowance_name }} @if($alw->notes)<span class="text-slate-400 text-[11px]">({{ $alw->notes }})</span>@endif</span>
                            <span class="font-bold text-brand-navy font-mono">Rp {{ number_format($alw->amount, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- HISTORI PERUBAHAN GAJI (Gaji Awal -> Gaji Akhir) -->
                <div class="space-y-2 pt-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-brand-navy flex items-center gap-1.5">
                            <i class="fa-solid fa-clock-rotate-left text-brand-primary"></i>
                            <span>Histori Penyesuaian Gaji (Gaji Awal &rarr; Gaji Akhir):</span>
                        </span>
                        <span class="text-[10px] text-slate-400">{{ $employee->salaryHistories->count() }} Data Histori</span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 font-bold text-slate-600 border-b border-slate-200 text-[11px]">
                                <tr>
                                    <th class="py-2.5 px-3">Periode</th>
                                    <th class="py-2.5 px-3 text-center">Grade</th>
                                    <th class="py-2.5 px-3 text-center">Level</th>
                                    <th class="py-2.5 px-3 text-right">Gaji Pokok</th>
                                    <th class="py-2.5 px-3 text-right">Gaji/Jam</th>
                                    <th class="py-2.5 px-3">Tunjangan</th>
                                    <th class="py-2.5 px-3">Keterangan / Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($employee->salaryHistories()->orderBy('start_date', 'asc')->get() as $idx => $sh)
                                <tr class="{{ $sh->is_current ? 'bg-teal-50/40 font-semibold' : '' }}">
                                    <td class="py-2 px-3 text-slate-700 whitespace-nowrap">
                                        {{ $sh->start_date ? $sh->start_date->format('d/m/Y') : '-' }} &rarr;
                                        {{ $sh->end_date ? $sh->end_date->format('d/m/Y') : 'Sekarang' }}
                                    </td>
                                    <td class="py-2 px-3 text-center font-bold text-brand-navy">{{ $sh->line_grade ?? '-' }}</td>
                                    <td class="py-2 px-3 text-center font-bold text-brand-navy">{{ $sh->salary_level ?? '-' }}</td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-brand-navy">Rp {{ number_format($sh->basic_salary, 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 text-right font-mono">Rp {{ number_format($sh->hourly_rate, 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 text-xs">
                                        @if(!empty($sh->allowances_detail))
                                            @foreach($sh->allowances_detail as $ad)
                                                <div>{{ $ad['allowance_name'] ?? 'Tunjangan' }}: Rp {{ number_format($ad['amount'] ?? 0, 0, ',', '.') }}</div>
                                            @endforeach
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3">
                                        @if($sh->is_current)
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Gaji Aktif
                                        </span>
                                        @endif
                                        <span class="text-[11px] text-slate-500 block">{{ $sh->notes ?? ($idx === 0 ? 'Gaji Awal Masuk' : 'Penyesuaian Gaji') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-3 text-center text-slate-400 text-xs">Belum ada histori perubahan gaji.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <!-- ADMIN HRD NOTICE (GAJI DISEMBUNYIKAN SECARA TOTAL) -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3 text-xs text-slate-500">
                <i class="fa-solid fa-lock text-amber-600 text-base"></i>
                <div>
                    <span class="font-bold text-slate-700 block">Informasi Finansial Terlindungi</span>
                    <span>Data gaji dan histori penyesuaian gaji karyawan bersifat rahasia (Confidential) dan hanya dapat diakses oleh Super Admin.</span>
                </div>
            </div>
            @endif

            <!-- DATA & RIWAYAT CUTI KARYAWAN (BISA DIAKSES KEDUA ROLE) -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-brand-navy flex items-center gap-2">
                            <i class="fa-solid fa-plane-departure text-brand-primary"></i>
                            <span>Riwayat Cuti Karyawan</span>
                        </span>
                        <span class="text-[11px] text-slate-500">Cuti Terakhir: <strong>{{ $employee->last_leave_date ? $employee->last_leave_date->format('d F Y') : '-' }}</strong></span>
                    </div>
                    <span class="text-[10px] text-slate-400">{{ $employee->leaves->count() }} Data Cuti Tercatat</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 font-bold text-slate-600 border-b border-slate-200 text-[11px]">
                            <tr>
                                <th class="py-2.5 px-3 w-20">Cuti Ke-</th>
                                <th class="py-2.5 px-3">Tanggal Berangkat</th>
                                <th class="py-2.5 px-3">Tanggal Kembali</th>
                                <th class="py-2.5 px-3">Maskapai</th>
                                <th class="py-2.5 px-3 text-center w-24">Jumlah Hari</th>
                                <th class="py-2.5 px-3">Keterangan</th>
                                <th class="py-2.5 px-3 text-center w-24">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($employee->leaves()->orderBy('leave_start_date', 'desc')->get() as $idx => $leave)
                            <tr>
                                <td class="py-2.5 px-3 font-bold text-slate-700">Cuti {{ $employee->leaves->count() - $idx }}</td>
                                <td class="py-2.5 px-3 font-medium text-slate-800">{{ $leave->leave_start_date ? $leave->leave_start_date->format('d/m/Y') : '-' }}</td>
                                <td class="py-2.5 px-3 text-slate-600">{{ $leave->leave_end_date ? $leave->leave_end_date->format('d/m/Y') : '-' }}</td>
                                <td class="py-2.5 px-3 font-semibold text-teal-800">{{ $leave->airline ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-center font-bold text-brand-navy">{{ $leave->total_days ?? 1 }} Hari</td>
                                <td class="py-2.5 px-3 text-slate-600">{{ $leave->notes ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($leave->document_path)
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                @click="openFilePreview('{{ asset('storage/' . $leave->document_path) }}', 'Dokumen Cuti - {{ $leave->airline }}', '{{ $leaveDoc?->original_name ?? basename($leave->document_path) }}')"
                                                class="text-teal-700 font-bold hover:underline inline-flex items-center gap-1">
                                            <i class="fa-solid fa-eye text-[10px]"></i>
                                            <span>Lihat</span>
                                        </button>
                                        <span class="text-slate-300">&bull;</span>
                                        <a href="{{ asset('storage/' . $leave->document_path) }}" download class="text-slate-500 hover:text-slate-700">
                                            <i class="fa-solid fa-download text-[10px]"></i>
                                        </a>
                                    </div>
                                    @else
                                    <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-3 text-center text-slate-400 text-xs">Belum ada riwayat cuti yang tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- I. PERNYATAAN & TANDA TANGAN (SIGNATURE BLOCKS) -->
        <div class="space-y-4 pt-2">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg">
                I. PERNYATAAN
            </div>

            <p class="text-xs italic text-slate-600 leading-relaxed border-l-4 border-brand-primary pl-3 py-1">
                "Saya menyatakan bahwa seluruh data yang saya berikan dalam formulir ini adalah benar dan dapat dipertanggungjawabkan. Saya bersedia memberitahukan kepada perusahaan apabila terdapat perubahan data."
            </p>

            <div class="grid grid-cols-2 gap-8 pt-4 text-center text-xs">

                <!-- Kolom Tanda Tangan Karyawan -->
                <div class="flex flex-col items-center justify-between min-h-[150px] p-4 rounded-xl border border-slate-200">
                    <span class="font-bold text-slate-600">Karyawan yang Bersangkutan</span>

                    <div class="h-20 flex items-center justify-center my-2">
                        @php $empSign = $employee->getDocument('employee_signature'); @endphp
                        @if($empSign)
                        <img src="{{ $empSign->url }}" alt="TTD Karyawan" class="max-h-full max-w-[160px] object-contain">
                        @else
                        <span class="text-[11px] text-slate-300 italic">(Tanda Tangan Asli)</span>
                        @endif
                    </div>

                    <div>
                        <span class="font-bold text-brand-navy block underline">
                            {{ $employee->employee_signature_name ?? $employee->full_name }}
                        </span>
                        <span class="text-[10px] text-slate-400">Nama Terang Karyawan</span>
                    </div>
                </div>

                <!-- Kolom Tanda Tangan HRD / Pemeriksa -->
                <div class="flex flex-col items-center justify-between min-h-[150px] p-4 rounded-xl border border-slate-200">
                    <span class="font-bold text-slate-600">HRD / Pemeriksa</span>

                    <div class="h-20 flex items-center justify-center my-2">
                        @php $hrdSign = $employee->getDocument('hrd_signature'); @endphp
                        @if($hrdSign)
                        <img src="{{ $hrdSign->url }}" alt="TTD HRD" class="max-h-full max-w-[160px] object-contain">
                        @else
                        <span class="text-[11px] text-slate-300 italic">(Tanda Tangan & Cap)</span>
                        @endif
                    </div>

                    <div>
                        <span class="font-bold text-brand-navy block underline">
                            {{ $employee->hrd_signature_name ?? 'HRD PT ABP' }}
                        </span>
                        <span class="text-[10px] text-slate-400">Verifikator HRD</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- Footer Document Note -->
        <div class="pt-6 border-t border-slate-200 text-center text-[10px] text-slate-400">
            Formulir Biodata Karyawan &bull; PT Artha Buana Primacoral &bull; Dicetak dari Employee Management System pada {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y H:i:s') }}
        </div>

    </div>

</div>
@endsection
