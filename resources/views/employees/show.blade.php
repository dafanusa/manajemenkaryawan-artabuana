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

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5 text-xs">
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
                    'salary_slip' => 'Slip Gaji',
                    'leave_form' => 'Form Cuti/Tiket',
                    'employee_signature' => 'TTD Karyawan',
                    'hrd_signature' => 'TTD HRD',
                ];
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
                    <a href="{{ $doc->url }}" target="_blank" class="text-[10px] text-teal-700 hover:underline font-bold flex items-center gap-1">
                        <span>Lihat File</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                    </a>
                    @else
                    <span class="text-[10px] text-slate-400 italic">Belum ada</span>
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
                <div class="w-32 h-44 rounded-xl border border-slate-300 overflow-hidden flex-shrink-0 bg-slate-100 flex items-center justify-center shadow-sm relative">
                    @if($employee->photo)
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->full_name }}" class="w-full h-full object-cover">
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
                            <a href="{{ asset('storage/' . $edu->document_path) }}" target="_blank" class="text-teal-700 font-bold hover:underline">Lihat</a>
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
                            <a href="{{ asset('storage/' . $exp->document_path) }}" target="_blank" class="text-teal-700 font-bold hover:underline">Lihat</a>
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

        <!-- E. KEAHLIAN & SERTIFIKAT -->
        <div class="space-y-3">
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

            @if($employee->certificates->count() > 0)
            <table class="w-full text-left text-xs border border-slate-200 mt-2">
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
                            <a href="{{ asset('storage/' . $cert->document_path) }}" target="_blank" class="text-teal-700 font-bold hover:underline">Lihat</a>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <!-- F. DATA ADMINISTRASI -->
        <div class="space-y-3">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg">
                F. DATA ADMINISTRASI
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
        </div>

        <!-- G. DATA KEPEGAWAIAN (DIISI HRD) -->
        <div class="space-y-3">
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
        </div>

        <!-- H. GAJI DAN CUTI TERAKHIR -->
        <div class="space-y-3">
            <div class="bg-brand-navy text-white text-xs font-bold py-1.5 px-3 rounded-lg">
                H. GAJI DAN CUTI TERAKHIR
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-xs">
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-44 font-semibold text-slate-500 flex-shrink-0">Posisi Saat Ini</span>
                    <span class="font-medium">: {{ $employee->current_position ?? $employee->position }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-44 font-semibold text-slate-500 flex-shrink-0">Lama Kerja Sebelumnya</span>
                    <span class="font-medium">: {{ $employee->previous_work_years }} Tahun, {{ $employee->previous_work_months }} Bulan</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-44 font-semibold text-slate-500 flex-shrink-0">Gaji Pokok *)</span>
                    <span class="font-bold text-brand-navy font-mono">: Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</span>
                </div>
                <div class="flex border-b border-slate-100 pb-1.5">
                    <span class="w-44 font-semibold text-slate-500 flex-shrink-0">Gaji Per Jam</span>
                    <span class="font-medium font-mono">: Rp {{ number_format($employee->hourly_rate, 0, ',', '.') }}</span>
                </div>
                <div class="sm:col-span-2 flex border-b border-slate-100 pb-1.5">
                    <span class="w-44 font-semibold text-slate-500 flex-shrink-0">Cuti Terakhir ***)</span>
                    <span class="font-medium">: {{ $employee->last_leave_date ? $employee->last_leave_date->format('d F Y') : '-' }}</span>
                </div>
            </div>

            <!-- Tunjangan List -->
            @if($employee->allowances->count() > 0)
            <div class="pt-1">
                <span class="text-[11px] font-bold text-slate-500 block mb-1">Tunjangan Lainnya **):</span>
                <ul class="list-disc list-inside text-xs text-slate-700 space-y-0.5">
                    @foreach($employee->allowances as $alw)
                    <li>
                        <strong>{{ $alw->allowance_name }}</strong>: Rp {{ number_format($alw->amount, 0, ',', '.') }}
                        @if($alw->notes)<span class="text-slate-400">({{ $alw->notes }})</span>@endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
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
