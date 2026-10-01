@extends('layouts.app')

@section('title', 'Edit Karyawan - ' . $employee->full_name)
@section('page_title', 'Edit Biodata Karyawan: ' . $employee->full_name)

@section('content')
<div
    x-data="{
        activeTab: 'pribadi',

        photoPreview: @js($employee->photo ? asset('storage/' . $employee->photo) : null),

        updatePhotoPreview(event) {
            const file = event.target.files[0];
            if (file) {
                this.photoPreview = URL.createObjectURL(file);
            }
        },

        // Repeater: Educations
        educations: @js(
            $employee->educations->isNotEmpty()
                ? $employee->educations->map(fn($e) => [
                    'level' => $e->level,
                    'institution_name' => $e->institution_name,
                    'major' => $e->major,
                    'graduation_year' => $e->graduation_year,
                    'certificate_number' => $e->certificate_number,
                    'document_path' => $e->document_path
                ])->values()
                : [
                    [
                        'level' => 'SD',
                        'institution_name' => '',
                        'major' => 'Umum',
                        'graduation_year' => '',
                        'certificate_number' => '',
                        'document_path' => ''
                    ],
                    [
                        'level' => 'SLTP',
                        'institution_name' => '',
                        'major' => 'Umum',
                        'graduation_year' => '',
                        'certificate_number' => '',
                        'document_path' => ''
                    ],
                    [
                        'level' => 'SLTA',
                        'institution_name' => '',
                        'major' => '',
                        'graduation_year' => '',
                        'certificate_number' => '',
                        'document_path' => ''
                    ],
                    [
                        'level' => 'S1',
                        'institution_name' => '',
                        'major' => '',
                        'graduation_year' => '',
                        'certificate_number' => '',
                        'document_path' => ''
                    ]
                ]
        ),

        addEducation() {
            this.educations.push({
                level: 'Lainnya',
                institution_name: '',
                major: '',
                graduation_year: '',
                certificate_number: '',
                document_path: ''
            });
        },

        removeEducation(idx) {
            this.educations.splice(idx, 1);
        },

        // Repeater: Work Experiences
        experiences: @js(
            $employee->workExperiences->isNotEmpty()
                ? $employee->workExperiences->map(fn($w) => [
                    'company_name' => $w->company_name,
                    'position' => $w->position,
                    'period' => $w->period,
                    'reason_for_leaving' => $w->reason_for_leaving,
                    'document_path' => $w->document_path
                ])->values()
                : [
                    [
                        'company_name' => '',
                        'position' => '',
                        'period' => '',
                        'reason_for_leaving' => '',
                        'document_path' => ''
                    ]
                ]
        ),

        addExperience() {
            this.experiences.push({
                company_name: '',
                position: '',
                period: '',
                reason_for_leaving: '',
                document_path: ''
            });
        },

        removeExperience(idx) {
            this.experiences.splice(idx, 1);
        },

        // Repeater: Certificates
        certificates: @js(
            $employee->certificates->isNotEmpty()
                ? $employee->certificates->map(fn($c) => [
                    'certificate_name' => $c->certificate_name,
                    'certificate_number' => $c->certificate_number,
                    'valid_until' => $c->valid_until,
                    'document_path' => $c->document_path
                ])->values()
                : [
                    [
                        'certificate_name' => '',
                        'certificate_number' => '',
                        'valid_until' => '',
                        'document_path' => ''
                    ]
                ]
        ),

        addCertificate() {
            this.certificates.push({
                certificate_name: '',
                certificate_number: '',
                valid_until: '',
                document_path: ''
            });
        },

        removeCertificate(idx) {
            this.certificates.splice(idx, 1);
        },

        // Repeater: Allowances
        allowances: @js(
            $employee->allowances->isNotEmpty()
                ? $employee->allowances->map(fn($a) => [
                    'allowance_name' => $a->allowance_name,
                    'amount' => (float) $a->amount,
                    'notes' => $a->notes
                ])->values()
                : [
                    [
                        'allowance_name' => 'Tunjangan Lokasi',
                        'amount' => 0,
                        'notes' => ''
                    ],
                    [
                        'allowance_name' => 'Tunjangan Makan & Transport',
                        'amount' => 0,
                        'notes' => ''
                    ]
                ]
        ),

        addAllowance() {
            this.allowances.push({
                allowance_name: '',
                amount: 0,
                notes: ''
            });
        },

        removeAllowance(idx) {
            this.allowances.splice(idx, 1);
        },

        employmentStatus: @js(
            old('employment_status', $employee->employment_status)
        )
    }"
    class="space-y-6"
>
    <!-- FORM HEADER -->
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 text-brand-primary p-2 flex items-center justify-center flex-shrink-0 border border-teal-100">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="max-h-full object-contain">
            </div>
            <div>
                <span class="text-[11px] font-bold text-brand-primary tracking-wider uppercase">Mode Pengeditan</span>
                <h2 class="text-xl font-extrabold text-brand-navy">EDIT BIODATA: {{ $employee->full_name }}</h2>
                <p class="text-xs text-slate-400">ID Karyawan: {{ $employee->employee_id }} &bull; Perbarui informasi form di bawah ini</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('employees.show', $employee) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Batal
            </a>
            <button type="button" @click="document.getElementById('editEmployeeForm').submit()" class="px-5 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold shadow-md shadow-brand-primary/20 transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <!-- ERROR LIST -->
    @if ($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl">
        <div class="flex items-center gap-2 text-rose-800 font-bold text-xs mb-2">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span>Terdapat beberapa kesalahan input yang perlu diperbaiki:</span>
        </div>
        <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- SECTION NAVIGATION TABS -->
    <div class="bg-white p-2 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-1 overflow-x-auto text-xs font-bold">
        <button type="button" @click="activeTab = 'pribadi'" :class="activeTab === 'pribadi' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap">A. Data Pribadi</button>
        <button type="button" @click="activeTab = 'kontak'" :class="activeTab === 'kontak' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap">B. Kontak Darurat</button>
        <button type="button" @click="activeTab = 'pendidikan'" :class="activeTab === 'pendidikan' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap">C. Pendidikan</button>
        <button type="button" @click="activeTab = 'pengalaman'" :class="activeTab === 'pengalaman' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap">D. Pengalaman</button>
        <button type="button" @click="activeTab = 'keahlian'" :class="activeTab === 'keahlian' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap">E. Keahlian & Sertifikat</button>
        <button type="button" @click="activeTab = 'administrasi'" :class="activeTab === 'administrasi' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap">F. Administrasi</button>
        <button type="button" @click="activeTab = 'kepegawaian'" :class="activeTab === 'kepegawaian' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap">G. Kepegawaian (HRD)</button>
        <button type="button" @click="activeTab = 'gaji'" :class="activeTab === 'gaji' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap">H. Gaji & Cuti</button>
        <button type="button" @click="activeTab = 'pernyataan'" :class="activeTab === 'pernyataan' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap">I. Pernyataan & TTD</button>
    </div>

    <!-- MAIN FORM -->
    <form id="editEmployeeForm" method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SEKSI A: DATA PRIBADI -->
        <div x-show="activeTab === 'pribadi'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">A. DATA PRIBADI KARYAWAN</h3>
                    <p class="text-xs text-slate-400">Informasi identitas pribadi, kependudukan, dan foto 3x4</p>
                </div>
            </div>

            <!-- Foto Preview & Upload -->
            <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 flex flex-col sm:flex-row items-center gap-6">
                <div class="w-28 h-36 rounded-2xl bg-white border-2 border-dashed border-slate-300 overflow-hidden flex items-center justify-center relative flex-shrink-0 shadow-inner">
                    <template x-if="photoPreview">
                        <img :src="photoPreview" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!photoPreview">
                        <div class="text-center p-2 text-slate-400">
                            <i class="fa-solid fa-camera text-2xl mb-1 block text-slate-300"></i>
                            <span class="text-[10px] font-bold uppercase block leading-tight">Pas Foto<br>3 x 4</span>
                        </div>
                    </template>
                </div>
                <div class="space-y-2 flex-1 text-center sm:text-left">
                    <label class="block text-xs font-bold text-slate-700">Ganti Pas Foto Karyawan</label>
                    <input type="file" name="photo" accept="image/png,image/jpeg,image/jpg" @change="updatePhotoPreview" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-primary file:text-white hover:file:bg-brand-primary-hover file:cursor-pointer">
                    <p class="text-[11px] text-slate-400">Biarkan kosong jika tidak ingin mengubah foto saat ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Suku <span class="text-red-500">*</span></label>
                    <select name="ethnicity" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs bg-white">
                        <option value="Papua" {{ old('ethnicity', $employee->ethnicity) === 'Papua' ? 'selected' : '' }}>Papua</option>
                        <option value="Non Papua" {{ old('ethnicity', $employee->ethnicity) === 'Non Papua' ? 'selected' : '' }}>Non Papua</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $employee->birth_place) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '') }}" max="{{ date('Y-m-d') }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs bg-white">
                        <option value="Laki-laki" {{ old('gender', $employee->gender) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender', $employee->gender) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Agama <span class="text-red-500">*</span></label>
                    <select name="religion" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs bg-white">
                        @foreach($religions as $rel)
                        <option value="{{ $rel }}" {{ old('religion', $employee->religion) === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Perkawinan</label>
                    <select name="marital_status" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs bg-white">
                        <option value="Belum Kawin" {{ old('marital_status', $employee->marital_status) === 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                        <option value="Kawin" {{ old('marital_status', $employee->marital_status) === 'Kawin' ? 'selected' : '' }}>Kawin</option>
                        <option value="Cerai Hidup" {{ old('marital_status', $employee->marital_status) === 'Cerai Hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                        <option value="Cerai Mati" {{ old('marital_status', $employee->marital_status) === 'Cerai Mati' ? 'selected' : '' }}>Cerai Mati</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. HP / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Karyawan</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. KTP / NIK</label>
                    <input type="text" name="nik" value="{{ old('nik', $employee->nik) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload KTP Baru (Opsional)</label>
                    <input type="file" name="doc_ktp" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-100">
                    @if($employee->hasDocument('ktp'))
                    <span class="text-[10px] text-teal-600 font-bold block mt-1">✓ File saat ini tersedia</span>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Kartu Keluarga (KK)</label>
                    <input type="text" name="kk_number" value="{{ old('kk_number', $employee->kk_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload KK Baru (Opsional)</label>
                    <input type="file" name="doc_kk" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-100">
                    @if($employee->hasDocument('kk'))
                    <span class="text-[10px] text-teal-600 font-bold block mt-1">✓ File saat ini tersedia</span>
                    @endif
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat KTP</label>
                    <textarea name="ktp_address" rows="2" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">{{ old('ktp_address', $employee->ktp_address) }}</textarea>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Domisili Sekarang</label>
                    <textarea name="domicile_address" rows="2" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">{{ old('domicile_address', $employee->domicile_address) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'kontak'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Kontak Darurat</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- SEKSI B: KONTAK DARURAT -->
        @php $contact = $employee->emergencyContacts->first(); @endphp
        <div x-show="activeTab === 'kontak'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">B. KONTAK DARURAT / EMERGENCY CONTACT</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kontak Darurat</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $contact?->name) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Hubungan</label>
                    <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $contact?->relationship) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. HP / WhatsApp</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $contact?->phone) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Alternatif</label>
                    <input type="text" name="emergency_contact_alt_phone" value="{{ old('emergency_contact_alt_phone', $contact?->alternative_phone) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Kontak Darurat</label>
                    <textarea name="emergency_contact_address" rows="2" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">{{ old('emergency_contact_address', $contact?->address) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'pribadi'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">&larr; Kembali</button>
                <button type="button" @click="activeTab = 'pendidikan'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2"><span>Lanjut: Pendidikan</span> <i class="fa-solid fa-arrow-right text-[10px]"></i></button>
            </div>
        </div>

        <!-- SEKSI C: PENDIDIKAN (REPEATER) -->
        <div x-show="activeTab === 'pendidikan'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">C. RIWAYAT PENDIDIKAN TERAKHIR</h3>
                    <p class="text-xs text-slate-400">Jenjang pendidikan formal dan dokumen ijazah</p>
                </div>
                <button type="button" @click="addEducation()" class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Tambah Jenjang
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(edu, idx) in educations" :key="idx">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3 relative">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-brand-navy flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-brand-primary text-white flex items-center justify-center text-[10px]" x-text="idx + 1"></span>
                                <span x-text="'Jenjang: ' + edu.level"></span>
                            </span>
                            <button type="button" @click="removeEducation(idx)" class="text-slate-400 hover:text-red-500 text-xs p-1">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>

                        <input type="hidden" :name="`educations[${idx}][document_path]`" :value="edu.document_path">

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jenjang</label>
                                <select :name="`educations[${idx}][level]`" x-model="edu.level" class="w-full p-2 rounded-xl border border-slate-200 bg-white">
                                    <option value="SD">SD</option>
                                    <option value="SLTP">SLTP / SMP</option>
                                    <option value="SLTA">SLTA / SMA / SMK</option>
                                    <option value="D3">D3</option>
                                    <option value="S1">S1 / Sarjana</option>
                                    <option value="S2">S2 / Magister</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block font-bold text-slate-600 mb-1">Nama Sekolah / PT</label>
                                <input type="text" :name="`educations[${idx}][institution_name]`" x-model="edu.institution_name" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jurusan</label>
                                <input type="text" :name="`educations[${idx}][major]`" x-model="edu.major" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Tahun Lulus</label>
                                <input type="text" :name="`educations[${idx}][graduation_year]`" x-model="edu.graduation_year" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block font-bold text-slate-600 mb-1">No. Ijazah</label>
                                <input type="text" :name="`educations[${idx}][certificate_number]`" x-model="edu.certificate_number" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div class="lg:col-span-3">
                                <label class="block font-bold text-slate-600 mb-1">Upload Ijazah Baru</label>
                                <input type="file" :name="`educations[${idx}][document]`" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-200">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'kontak'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">&larr; Kembali</button>
                <button type="button" @click="activeTab = 'pengalaman'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2"><span>Lanjut: Pengalaman Kerja</span> <i class="fa-solid fa-arrow-right text-[10px]"></i></button>
            </div>
        </div>

        <!-- SEKSI D: PENGALAMAN (REPEATER) -->
        <div x-show="activeTab === 'pengalaman'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">D. PENGALAMAN KERJA TERAKHIR</h3>
                </div>
                <button type="button" @click="addExperience()" class="px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> + Tambah Pengalaman
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(exp, idx) in experiences" :key="idx">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3 relative">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-brand-navy" x-text="'Pengalaman ' + (idx + 1)"></span>
                            <button type="button" @click="removeExperience(idx)" class="text-slate-400 hover:text-red-500 text-xs p-1">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>

                        <input type="hidden" :name="`work_experiences[${idx}][document_path]`" :value="exp.document_path">

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Perusahaan</label>
                                <input type="text" :name="`work_experiences[${idx}][company_name]`" x-model="exp.company_name" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jabatan</label>
                                <input type="text" :name="`work_experiences[${idx}][position]`" x-model="exp.position" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Periode</label>
                                <input type="text" :name="`work_experiences[${idx}][period]`" x-model="exp.period" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-600 mb-1">Alasan Berhenti</label>
                                <input type="text" :name="`work_experiences[${idx}][reason_for_leaving]`" x-model="exp.reason_for_leaving" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Upload Dokumen Baru</label>
                                <input type="file" :name="`work_experiences[${idx}][document]`" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-200">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'pendidikan'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">&larr; Kembali</button>
                <button type="button" @click="activeTab = 'keahlian'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2"><span>Lanjut: Keahlian</span> <i class="fa-solid fa-arrow-right text-[10px]"></i></button>
            </div>
        </div>

        <!-- SEKSI E: KEAHLIAN & SERTIFIKAT -->
        <div x-show="activeTab === 'keahlian'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">E. KEAHLIAN DAN SERTIFIKAT KOMPETENSI</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Keahlian Utama</label>
                    <textarea name="skills_summary" rows="3" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">{{ old('skills_summary', $employee->skills_summary) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alat / Software yang Dikuasai</label>
                    <textarea name="tools_software" rows="3" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">{{ old('tools_software', $employee->tools_software) }}</textarea>
                </div>
            </div>

            <!-- Sertifikat Repeater -->
            <div class="space-y-4 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider">Sertifikat Kompetensi</h4>
                    <button type="button" @click="addCertificate()" class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> + Tambah Sertifikat
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(cert, idx) in certificates" :key="idx">
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs items-end">
                            <input type="hidden" :name="`certificates[${idx}][document_path]`" :value="cert.document_path">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Sertifikat</label>
                                <input type="text" :name="`certificates[${idx}][certificate_name]`" x-model="cert.certificate_name" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nomor Sertifikat</label>
                                <input type="text" :name="`certificates[${idx}][certificate_number]`" x-model="cert.certificate_number" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Masa Berlaku</label>
                                <input type="text" :name="`certificates[${idx}][valid_until]`" x-model="cert.valid_until" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block font-bold text-slate-600 mb-1">Upload Dokumen</label>
                                    <input type="file" :name="`certificates[${idx}][document]`" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:bg-slate-200">
                                </div>
                                <button type="button" @click="removeCertificate(idx)" class="text-slate-400 hover:text-red-500 p-2 text-xs">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'pengalaman'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">&larr; Kembali</button>
                <button type="button" @click="activeTab = 'administrasi'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2"><span>Lanjut: Administrasi</span> <i class="fa-solid fa-arrow-right text-[10px]"></i></button>
            </div>
        </div>

        <!-- SEKSI F: ADMINISTRASI -->
        <div x-show="activeTab === 'administrasi'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">F. DATA ADMINISTRASI</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NPWP / NIK Pajak</label>
                    <input type="text" name="npwp_number" value="{{ old('npwp_number', $employee->npwp_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload NPWP Baru</label>
                    <input type="file" name="doc_npwp" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status PTKP</label>
                    <select name="ptkp_status" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs bg-white">
                        <option value="">Pilih PTKP</option>
                        @foreach($ptkpOptions as $ptkp)
                        <option value="{{ $ptkp }}" {{ old('ptkp_status', $employee->ptkp_status) === $ptkp ? 'selected' : '' }}>{{ $ptkp }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. BPJS Kesehatan</label>
                    <input type="text" name="bpjs_kes_number" value="{{ old('bpjs_kes_number', $employee->bpjs_kes_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload BPJS Kesehatan Baru</label>
                    <input type="file" name="doc_bpjs_kes" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-100">
                </div>
                <div></div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. BPJS Ketenagakerjaan</label>
                    <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number', $employee->bpjs_tk_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload BPJS TK Baru</label>
                    <input type="file" name="doc_bpjs_tk" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-100">
                </div>
                <div></div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Rekening Bank</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Pemilik Rekening</label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $employee->bank_account_holder) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Buku Rekening Baru</label>
                    <input type="file" name="doc_bank_book" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-100">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'keahlian'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">&larr; Kembali</button>
                <button type="button" @click="activeTab = 'kepegawaian'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2"><span>Lanjut: Kepegawaian</span> <i class="fa-solid fa-arrow-right text-[10px]"></i></button>
            </div>
        </div>

        <!-- SEKSI G: DATA KEPEGAWAIAN (HRD) -->
        <div x-show="activeTab === 'kepegawaian'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">G. DATA KEPEGAWAIAN (DIISI HRD)</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ID Karyawan <span class="text-red-500">*</span></label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs font-mono font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload ID Card Baru</label>
                    <input type="file" name="doc_id_card" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Kepegawaian <span class="text-red-500">*</span></label>
                    <select name="employment_status" x-model="employmentStatus" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs bg-white font-bold">
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" name="position" value="{{ old('position', $employee->position) }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Departemen <span class="text-red-500">*</span></label>
                    <select name="department" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs bg-white">
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ old('department', $employee->department) === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lokasi Kerja <span class="text-red-500">*</span></label>
                    <select name="work_location" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs bg-white font-semibold">
                        <option value="Lowland" {{ old('work_location', $employee->work_location) === 'Lowland' ? 'selected' : '' }}>Lowland</option>
                        <option value="Highland" {{ old('work_location', $employee->work_location) === 'Highland' ? 'selected' : '' }}>Highland</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Project / Penempatan</label>
                    <input type="text" name="project_location" value="{{ old('project_location', $employee->project_location) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Masuk (Join Date) <span class="text-red-500">*</span></label>
                    <input type="date" name="join_date" value="{{ old('join_date', $employee->join_date ? $employee->join_date->format('Y-m-d') : '') }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Keluar (Leave Date)</label>
                    <input type="date" name="leave_date" value="{{ old('leave_date', $employee->leave_date ? $employee->leave_date->format('Y-m-d') : '') }}" :required="employmentStatus === 'Tidak Aktif'" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'administrasi'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">&larr; Kembali</button>
                <button type="button" @click="activeTab = 'gaji'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2"><span>Lanjut: Gaji & Cuti</span> <i class="fa-solid fa-arrow-right text-[10px]"></i></button>
            </div>
        </div>

        <!-- SEKSI H: GAJI & CUTI -->
        <div x-show="activeTab === 'gaji'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">H. GAJI DAN CUTI TERAKHIR</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Posisi Saat Ini</label>
                    <input type="text" name="current_position" value="{{ old('current_position', $employee->current_position) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lama Kerja Sebelumnya (Tahun)</label>
                    <input type="number" min="0" name="previous_work_years" value="{{ old('previous_work_years', $employee->previous_work_years) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lama Kerja Sebelumnya (Bulan)</label>
                    <input type="number" min="0" max="11" name="previous_work_months" value="{{ old('previous_work_months', $employee->previous_work_months) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Gaji Pokok (Rp)</label>
                    <input type="number" step="1000" min="0" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Gaji Per Jam (Rp)</label>
                    <input type="number" step="100" min="0" name="hourly_rate" value="{{ old('hourly_rate', $employee->hourly_rate) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Slip Gaji Baru</label>
                    <input type="file" name="doc_salary_slip" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-100">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Cuti Terakhir</label>
                    <input type="date" name="last_leave_date" value="{{ old('last_leave_date', $employee->last_leave_date ? $employee->last_leave_date->format('Y-m-d') : '') }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Form Cuti / Tiket Baru</label>
                    <input type="file" name="doc_leave_form" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-100">
                </div>
            </div>

            <!-- Tunjangan Repeater -->
            <div class="space-y-4 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider">Tunjangan Lainnya</h4>
                    <button type="button" @click="addAllowance()" class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> + Tambah Tunjangan
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(alw, idx) in allowances" :key="idx">
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs items-end">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Tunjangan</label>
                                <input type="text" :name="`allowances[${idx}][allowance_name]`" x-model="alw.allowance_name" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nominal (Rp)</label>
                                <input type="number" step="1000" min="0" :name="`allowances[${idx}][amount]`" x-model="alw.amount" class="w-full p-2 rounded-xl border border-slate-200 font-mono">
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block font-bold text-slate-600 mb-1">Keterangan</label>
                                    <input type="text" :name="`allowances[${idx}][notes]`" x-model="alw.notes" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>
                                <button type="button" @click="removeAllowance(idx)" class="text-slate-400 hover:text-red-500 p-2 text-xs">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'kepegawaian'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">&larr; Kembali</button>
                <button type="button" @click="activeTab = 'pernyataan'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2"><span>Lanjut: Pernyataan</span> <i class="fa-solid fa-arrow-right text-[10px]"></i></button>
            </div>
        </div>

        <!-- SEKSI I: PERNYATAAN & TTD -->
        <div x-show="activeTab === 'pernyataan'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">I. PERNYATAAN DAN TANDA TANGAN</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- TTD Karyawan -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                    <span class="text-xs font-bold text-brand-navy uppercase block border-b border-slate-200 pb-2">Tanda Tangan Karyawan</span>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Karyawan</label>
                        <input type="text" name="employee_signature_name" value="{{ old('employee_signature_name', $employee->employee_signature_name ?? $employee->full_name) }}" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Tanda Tangan Baru</label>
                        <input type="file" name="doc_employee_signature" accept=".jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                    </div>
                </div>

                <!-- TTD HRD -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                    <span class="text-xs font-bold text-brand-navy uppercase block border-b border-slate-200 pb-2">Tanda Tangan HRD / Pemeriksa</span>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama HRD / Pemeriksa</label>
                        <input type="text" name="hrd_signature_name" value="{{ old('hrd_signature_name', $employee->hrd_signature_name ?? Auth::user()->name) }}" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Tanda Tangan HRD Baru</label>
                        <input type="file" name="doc_hrd_signature" accept=".jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <button type="button" @click="activeTab = 'gaji'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">&larr; Kembali</button>
                <button type="submit" class="px-8 py-3.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-extrabold rounded-xl shadow-lg shadow-teal-900/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-sm"></i>
                    <span>Simpan Perubahan Data Karyawan</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
