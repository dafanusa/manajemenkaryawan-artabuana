@extends('layouts.app')

@section('title', 'Input Data Karyawan')
@section('page_title', 'Form Biodata Karyawan PT Artha Buana Primacoral')

@section('content')
<div x-data="{
    activeTab: 'pribadi',
    photoPreview: null,
    updatePhotoPreview(event) {
        const file = event.target.files[0];
        if (file) {
            this.photoPreview = URL.createObjectURL(file);
        }
    },
    // Repeater: Educations
    educations: [
        { level: 'SD', institution_name: '', major: 'Umum', graduation_year: '', certificate_number: '' },
        { level: 'SLTP', institution_name: '', major: 'Umum', graduation_year: '', certificate_number: '' },
        { level: 'SLTA', institution_name: '', major: '', graduation_year: '', certificate_number: '' },
        { level: 'S1', institution_name: '', major: '', graduation_year: '', certificate_number: '' }
    ],
    addEducation() {
        this.educations.push({ level: 'Lainnya', institution_name: '', major: '', graduation_year: '', certificate_number: '' });
    },
    removeEducation(idx) {
        this.educations.splice(idx, 1);
    },
    // Repeater: Work Experiences
    experiences: [
        { company_name: '', position: '', period: '', reason_for_leaving: '' }
    ],
    addExperience() {
        this.experiences.push({ company_name: '', position: '', period: '', reason_for_leaving: '' });
    },
    removeExperience(idx) {
        this.experiences.splice(idx, 1);
    },
    // Repeater: Certificates
    certificates: [
        { certificate_name: '', certificate_number: '', valid_until: '' }
    ],
    addCertificate() {
        this.certificates.push({ certificate_name: '', certificate_number: '', valid_until: '' });
    },
    removeCertificate(idx) {
        this.certificates.splice(idx, 1);
    },
    // Repeater: Allowances
    allowances: [
        { allowance_name: 'Tunjangan Lokasi', amount: 0, notes: '' },
        { allowance_name: 'Tunjangan Makan & Transport', amount: 0, notes: '' }
    ],
    addAllowance() {
        this.allowances.push({ allowance_name: '', amount: 0, notes: '' });
    },
    removeAllowance(idx) {
        this.allowances.splice(idx, 1);
    },
    // Status watcher for leave_date requirement
    employmentStatus: 'Aktif'
}" class="space-y-6">

    <!-- FORM HEADER CARD -->
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 text-brand-primary p-2 flex items-center justify-center flex-shrink-0 border border-teal-100">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="max-h-full object-contain">
            </div>
            <div>
                <span class="text-[11px] font-bold text-brand-primary tracking-wider uppercase">Formulir Resmi Perusahaan</span>
                <h2 class="text-xl font-extrabold text-brand-navy">FORM BIODATA KARYAWAN</h2>
                <p class="text-xs text-slate-400">PT Artha Buana Primacoral &bull; Lengkapi seluruh seksi data di bawah ini</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('employees.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Batal
            </a>
            <button type="button" @click="document.getElementById('employeeForm').submit()" class="px-5 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold shadow-md shadow-brand-primary/20 transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Biodata</span>
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
        <button type="button" @click="activeTab = 'pribadi'" :class="activeTab === 'pribadi' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>A. Data Pribadi</span>
        </button>
        <button type="button" @click="activeTab = 'kontak'" :class="activeTab === 'kontak' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>B. Kontak Darurat</span>
        </button>
        <button type="button" @click="activeTab = 'pendidikan'" :class="activeTab === 'pendidikan' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>C. Pendidikan</span>
        </button>
        <button type="button" @click="activeTab = 'pengalaman'" :class="activeTab === 'pengalaman' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>D. Pengalaman</span>
        </button>
        <button type="button" @click="activeTab = 'keahlian'" :class="activeTab === 'keahlian' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>E. Keahlian & Sertifikat</span>
        </button>
        <button type="button" @click="activeTab = 'administrasi'" :class="activeTab === 'administrasi' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>F. Administrasi</span>
        </button>
        <button type="button" @click="activeTab = 'kepegawaian'" :class="activeTab === 'kepegawaian' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>G. Kepegawaian (HRD)</span>
        </button>
        <button type="button" @click="activeTab = 'gaji'" :class="activeTab === 'gaji' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>H. Gaji & Cuti</span>
        </button>
        <button type="button" @click="activeTab = 'pernyataan'" :class="activeTab === 'pernyataan' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>I. Pernyataan & TTD</span>
        </button>
    </div>

    <!-- MAIN FORM -->
    <form id="employeeForm" method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- ============================================== -->
        <!-- SEKSI A: DATA PRIBADI -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'pribadi'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">A. DATA PRIBADI KARYAWAN</h3>
                    <p class="text-xs text-slate-400">Informasi identitas pribadi, kependudukan, dan foto 3x4</p>
                </div>
                <span class="text-xs font-bold text-brand-primary bg-teal-50 px-2.5 py-1 rounded-lg">Wajib Diisi</span>
            </div>

            <!-- Foto Preview & Upload Row -->
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
                    <label class="block text-xs font-bold text-slate-700">Upload Pas Foto Karyawan</label>
                    <input type="file" name="photo" accept="image/png,image/jpeg,image/jpg" @change="updatePhotoPreview" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-primary file:text-white hover:file:bg-brand-primary-hover file:cursor-pointer">
                    <p class="text-[11px] text-slate-400">Format: JPG, JPEG, PNG. Maksimal 5MB. Ukuran standar 3x4 formal.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                <!-- Nama Lengkap -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Contoh: Yohanes Tabuni" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Suku (Papua / Non Papua) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Suku <span class="text-red-500">*</span></label>
                    <select name="ethnicity" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="Papua" {{ old('ethnicity') === 'Papua' ? 'selected' : '' }}>Papua</option>
                        <option value="Non Papua" {{ old('ethnicity') === 'Non Papua' ? 'selected' : '' }}>Non Papua</option>
                    </select>
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place') }}" placeholder="Contoh: Jayapura" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Tanggal Lahir (Calendar / Date Picker) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ date('Y-m-d') }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="Laki-laki" {{ old('gender') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <!-- Agama -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Agama <span class="text-red-500">*</span></label>
                    <select name="religion" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        @foreach($religions as $rel)
                        <option value="{{ $rel }}" {{ old('religion') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Perkawinan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Perkawinan</label>
                    <select name="marital_status" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="Belum Kawin" {{ old('marital_status') === 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                        <option value="Kawin" {{ old('marital_status') === 'Kawin' ? 'selected' : '' }}>Kawin</option>
                        <option value="Cerai Hidup" {{ old('marital_status') === 'Cerai Hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                        <option value="Cerai Mati" {{ old('marital_status') === 'Cerai Mati' ? 'selected' : '' }}>Cerai Mati</option>
                    </select>
                </div>

                <!-- No HP / WhatsApp -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. HP / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08123456789" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Karyawan</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="karyawan@primacoral.com" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- No KTP / NIK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. KTP / NIK</label>
                    <input type="text" name="nik" value="{{ old('nik') }}" placeholder="16 digit NIK" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload KTP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload KTP</label>
                    <input type="file" name="doc_ktp" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <!-- No KK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Kartu Keluarga (KK)</label>
                    <input type="text" name="kk_number" value="{{ old('kk_number') }}" placeholder="16 digit No. KK" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload KK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Kartu Keluarga</label>
                    <input type="file" name="doc_kk" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <!-- Alamat KTP -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat KTP</label>
                    <textarea name="ktp_address" rows="2" placeholder="Alamat lengkap sesuai identitas KTP..." class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('ktp_address') }}</textarea>
                </div>

                <!-- Alamat Domisili -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Domisili Sekarang (Mess / Rumah)</label>
                    <textarea name="domicile_address" rows="2" placeholder="Alamat tempat tinggal saat ini..." class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('domicile_address') }}</textarea>
                </div>

            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'kontak'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Kontak Darurat</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI B: KONTAK DARURAT -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'kontak'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">B. KONTAK DARURAT / EMERGENCY CONTACT</h3>
                <p class="text-xs text-slate-400">Pihak yang dapat dihubungi saat situasi darurat di tempat kerja</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kontak Darurat</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" placeholder="Contoh: Sarah Tabuni" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Hubungan</label>
                    <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" placeholder="Istri, Suami, Orang Tua, Saudara Kandung" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. HP / WhatsApp Kontak</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" placeholder="081234567890" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Telepon Alternatif</label>
                    <input type="text" name="emergency_contact_alt_phone" value="{{ old('emergency_contact_alt_phone') }}" placeholder="082199887766" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Kontak Darurat</label>
                    <textarea name="emergency_contact_address" rows="2" placeholder="Alamat lengkap kontak darurat..." class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('emergency_contact_address') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'pribadi'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
                    &larr; Kembali
                </button>
                <button type="button" @click="activeTab = 'pendidikan'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Riwayat Pendidikan</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI C: RIWAYAT PENDIDIKAN TERAKHIR (REPEATER) -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'pendidikan'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">C. RIWAYAT PENDIDIKAN TERAKHIR</h3>
                    <p class="text-xs text-slate-400">Jenjang pendidikan formal (SD, SLTP, SLTA, S1, S2) dan upload dokumen ijazah</p>
                </div>
                <button type="button" @click="addEducation()" class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1.5">
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
                            <button type="button" @click="removeEducation(idx)" class="text-slate-400 hover:text-red-500 text-xs p-1" title="Hapus baris ini">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>

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
                                <label class="block font-bold text-slate-600 mb-1">Nama Sekolah / Perguruan Tinggi</label>
                                <input type="text" :name="`educations[${idx}][institution_name]`" x-model="edu.institution_name" placeholder="Nama instansi pendidikan..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jurusan / Program Studi</label>
                                <input type="text" :name="`educations[${idx}][major]`" x-model="edu.major" placeholder="Jurusan..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Tahun Lulus</label>
                                <input type="text" :name="`educations[${idx}][graduation_year]`" x-model="edu.graduation_year" placeholder="Contoh: 2018" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block font-bold text-slate-600 mb-1">No. Ijazah</label>
                                <input type="text" :name="`educations[${idx}][certificate_number]`" x-model="edu.certificate_number" placeholder="Nomor resmi ijazah..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="block font-bold text-slate-600 mb-1">Upload Dokumen Ijazah (PDF/Gambar)</label>
                                <input type="file" :name="`educations[${idx}][document]`" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-200 hover:file:bg-slate-300">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'kontak'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
                    &larr; Kembali
                </button>
                <button type="button" @click="activeTab = 'pengalaman'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Pengalaman Kerja</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI D: PENGALAMAN KERJA (REPEATER) -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'pengalaman'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">D. PENGALAMAN KERJA TERAKHIR</h3>
                    <p class="text-xs text-slate-400">Riwayat karir sebelum bergabung di PT Artha Buana Primacoral</p>
                </div>
                <button type="button" @click="addExperience()" class="px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> + Tambah Pengalaman Kerja
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(exp, idx) in experiences" :key="idx">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3 relative">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-brand-navy flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-brand-navy text-white flex items-center justify-center text-[10px]" x-text="idx + 1"></span>
                                <span x-text="exp.company_name ? exp.company_name : 'Pengalaman ' + (idx + 1)"></span>
                            </span>
                            <button type="button" @click="removeExperience(idx)" class="text-slate-400 hover:text-red-500 text-xs p-1">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Perusahaan</label>
                                <input type="text" :name="`work_experiences[${idx}][company_name]`" x-model="exp.company_name" placeholder="Nama perusahaan..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jabatan / Posisi</label>
                                <input type="text" :name="`work_experiences[${idx}][position]`" x-model="exp.position" placeholder="Jabatan..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Periode (Bulan/Tahun - Bulan/Tahun)</label>
                                <input type="text" :name="`work_experiences[${idx}][period]`" x-model="exp.period" placeholder="Contoh: 2019 - 2022" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-600 mb-1">Alasan Berhenti</label>
                                <input type="text" :name="`work_experiences[${idx}][reason_for_leaving]`" x-model="exp.reason_for_leaving" placeholder="Alasan berhenti / resign..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Upload Dokumen Pengalaman Kerja</label>
                                <input type="file" :name="`work_experiences[${idx}][document]`" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-200 hover:file:bg-slate-300">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'pendidikan'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
                    &larr; Kembali
                </button>
                <button type="button" @click="activeTab = 'keahlian'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Keahlian & Sertifikat</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI E: KEAHLIAN & SERTIFIKAT (REPEATER) -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'keahlian'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">E. KEAHLIAN DAN SERTIFIKAT KOMPETENSI</h3>
                <p class="text-xs text-slate-400">Kompetensi teknis, perangkat lunak/alat yang dikuasai, dan sertifikasi profesi</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Keahlian Utama</label>
                    <textarea name="skills_summary" rows="3" placeholder="Uraikan keahlian teknis utama karyawan..." class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('skills_summary') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alat / Software yang Dikuasai</label>
                    <textarea name="tools_software" rows="3" placeholder="Contoh: CAT ET, Dispatch, SAP, AutoCAD, MS Office..." class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('tools_software') }}</textarea>
                </div>
            </div>

            <!-- Sertifikat Repeater -->
            <div class="space-y-4 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider">Sertifikat Kompetensi & Pelatihan</h4>
                    <button type="button" @click="addCertificate()" class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> + Tambah Sertifikat
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(cert, idx) in certificates" :key="idx">
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs items-end">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Sertifikat</label>
                                <input type="text" :name="`certificates[${idx}][certificate_name]`" x-model="cert.certificate_name" placeholder="POP, K3 Umum, SIO..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nomor Sertifikat</label>
                                <input type="text" :name="`certificates[${idx}][certificate_number]`" x-model="cert.certificate_number" placeholder="No registrasi..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Masa Berlaku (s/d)</label>
                                <input type="text" :name="`certificates[${idx}][valid_until]`" x-model="cert.valid_until" placeholder="Contoh: 2028 / Seumur Hidup" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block font-bold text-slate-600 mb-1">Upload File</label>
                                    <input type="file" :name="`certificates[${idx}][document]`" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:bg-slate-200">
                                </div>
                                <button type="button" @click="removeCertificate(idx)" class="text-slate-400 hover:text-red-500 p-2 text-xs" title="Hapus">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'pengalaman'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
                    &larr; Kembali
                </button>
                <button type="button" @click="activeTab = 'administrasi'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Data Administrasi</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI F: DATA ADMINISTRASI -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'administrasi'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">F. DATA ADMINISTRASI & KEUANGAN</h3>
                <p class="text-xs text-slate-400">Pajak (NPWP, PTKP), Jaminan Sosial (BPJS), dan Rekening Bank</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                <!-- NPWP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NPWP / NIK Pajak</label>
                    <input type="text" name="npwp_number" value="{{ old('npwp_number') }}" placeholder="15 atau 16 digit NPWP" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload NPWP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Kartu NPWP</label>
                    <input type="file" name="doc_npwp" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <!-- PTKP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status PTKP</label>
                    <select name="ptkp_status" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="">Pilih Status PTKP</option>
                        @foreach($ptkpOptions as $ptkp)
                        <option value="{{ $ptkp }}" {{ old('ptkp_status') === $ptkp ? 'selected' : '' }}>{{ $ptkp }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- BPJS Kesehatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. BPJS Kesehatan</label>
                    <input type="text" name="bpjs_kes_number" value="{{ old('bpjs_kes_number') }}" placeholder="No kartu BPJS Kesehatan" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload BPJS Kesehatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload BPJS Kesehatan</label>
                    <input type="file" name="doc_bpjs_kes" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <div></div> <!-- Spacer -->

                <!-- BPJS TK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. BPJS Ketenagakerjaan</label>
                    <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number') }}" placeholder="No KPJ BPJS Ketenagakerjaan" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload BPJS TK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload BPJS TK</label>
                    <input type="file" name="doc_bpjs_tk" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <div></div> <!-- Spacer -->

                <!-- Data Bank -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank Mandiri / BCA / Papua" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Rekening</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Nomor rekening transfer payroll" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Pemilik Rekening</label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder') }}" placeholder="Sesuai buku tabungan" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Buku Rekening (Halaman Depan)</label>
                    <input type="file" name="doc_bank_book" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'keahlian'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
                    &larr; Kembali
                </button>
                <button type="button" @click="activeTab = 'kepegawaian'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Data Kepegawaian</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI G: DATA KEPEGAWAIAN (DIISI HRD) -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'kepegawaian'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">G. DATA KEPEGAWAIAN (DIISI HRD)</h3>
                    <p class="text-xs text-slate-400">Parameter penempatan kerja, status, lokasi (Highland/Lowland), dan jadwal kerja</p>
                </div>
                <span class="text-xs font-bold text-teal-800 bg-teal-100/60 px-3 py-1 rounded-xl">HR Official</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                <!-- ID Karyawan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ID Karyawan <span class="text-red-500">*</span></label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $suggestedId) }}" required placeholder="Contoh: ABP-2026-001" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono font-bold text-brand-navy">
                </div>

                <!-- Upload ID Card -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload ID Card (Bila Ada)</label>
                    <input type="file" name="doc_id_card" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <!-- Status Kepegawaian (Aktif / Tidak Aktif) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Kepegawaian <span class="text-red-500">*</span></label>
                    <select name="employment_status" x-model="employmentStatus" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-bold">
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>

                <!-- Jabatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jabatan / Posisi <span class="text-red-500">*</span></label>
                    <input type="text" name="position" value="{{ old('position') }}" required placeholder="Contoh: Heavy Equipment Mechanic" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Departemen -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Departemen <span class="text-red-500">*</span></label>
                    <select name="department" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="">Pilih Departemen</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Lokasi Kerja (Highland / Lowland) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lokasi Kerja <span class="text-red-500">*</span></label>
                    <select name="work_location" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-semibold">
                        <option value="Lowland" {{ old('work_location') === 'Lowland' ? 'selected' : '' }}>Lowland (Timika, Kuala Kencana, Portsite)</option>
                        <option value="Highland" {{ old('work_location') === 'Highland' ? 'selected' : '' }}>Highland (Mile 68, Tembagapura, Grasberg)</option>
                    </select>
                </div>

                <!-- Project / Lokasi Fisik -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Project / Penempatan Spesifik</label>
                    <input type="text" name="project_location" value="{{ old('project_location') }}" placeholder="Contoh: Grasberg Mine Pit / Workshop Mile 38" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Tanggal Masuk (Calendar / Date Picker) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Masuk (Join Date) <span class="text-red-500">*</span></label>
                    <input type="date" name="join_date" value="{{ old('join_date', date('Y-m-d')) }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Tanggal Keluar (Calendar / Date Picker) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tanggal Keluar (Leave Date)
                        <span class="text-[10px] text-slate-400 font-normal">(Wajib jika status Tidak Aktif)</span>
                    </label>
                    <input type="date" name="leave_date" value="{{ old('leave_date') }}" :required="employmentStatus === 'Tidak Aktif'" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'administrasi'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
                    &larr; Kembali
                </button>
                <button type="button" @click="activeTab = 'gaji'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Gaji & Cuti</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI H: GAJI DAN CUTI TERAKHIR -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'gaji'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">H. GAJI DAN CUTI TERAKHIR</h3>
                <p class="text-xs text-slate-400">Riwayat kompensasi, lama masa kerja sebelumnya, tunjangan rutin, dan cuti</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                <!-- Posisi Saat Ini -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Posisi Saat Ini</label>
                    <input type="text" name="current_position" value="{{ old('current_position') }}" placeholder="Posisi yang dilamar / dijalani saat ini..." class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Lama Kerja di Perusahaan Sebelumnya: Tahun & Bulan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lama Kerja Sebelumnya (Tahun)</label>
                    <input type="number" min="0" name="previous_work_years" value="{{ old('previous_work_years', 0) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lama Kerja Sebelumnya (Bulan)</label>
                    <input type="number" min="0" max="11" name="previous_work_months" value="{{ old('previous_work_months', 0) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Gaji Pokok -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Gaji Pokok (Rp)
                        <span class="text-[10px] text-slate-400 font-normal">*) Harap lampirkan Slip Gaji</span>
                    </label>
                    <input type="number" step="1000" min="0" name="basic_salary" value="{{ old('basic_salary', 0) }}" placeholder="Contoh: 15000000" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono">
                </div>

                <!-- Gaji Per Jam -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Gaji Per Jam (Rp)</label>
                    <input type="number" step="100" min="0" name="hourly_rate" value="{{ old('hourly_rate', 0) }}" placeholder="Contoh: 85000" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono">
                </div>

                <!-- Upload Slip Gaji -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Slip Gaji 3 Bulan Terakhir</label>
                    <input type="file" name="doc_salary_slip" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <!-- Cuti Terakhir -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Cuti Terakhir (Tanggal Bulan)
                        <span class="text-[10px] text-slate-400 font-normal">***) Harap lampirkan tiket/boarding pass</span>
                    </label>
                    <input type="date" name="last_leave_date" value="{{ old('last_leave_date') }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload Bukti Tiket / Form Cuti -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Form Cuti / Bukti Tiket / Boarding Pass Terakhir</label>
                    <input type="file" name="doc_leave_form" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

            </div>

            <!-- Tunjangan Lainnya (Repeater) -->
            <div class="space-y-4 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider">Tunjangan Lainnya **)</h4>
                        <p class="text-[11px] text-slate-400">**) Silakan uraikan tunjangan yang diterima setiap bulan (Tunjangan makan, transportasi, jabatan, dll)</p>
                    </div>
                    <button type="button" @click="addAllowance()" class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> + Tambah Tunjangan
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(alw, idx) in allowances" :key="idx">
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs items-end">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Tunjangan</label>
                                <input type="text" :name="`allowances[${idx}][allowance_name]`" x-model="alw.allowance_name" placeholder="Tunjangan Lapangan, Mess, dll..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nominal (Rp/Bulan)</label>
                                <input type="number" step="1000" min="0" :name="`allowances[${idx}][amount]`" x-model="alw.amount" class="w-full p-2 rounded-xl border border-slate-200 font-mono">
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block font-bold text-slate-600 mb-1">Keterangan Tambahan</label>
                                    <input type="text" :name="`allowances[${idx}][notes]`" x-model="alw.notes" placeholder="Catatan..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>
                                <button type="button" @click="removeAllowance(idx)" class="text-slate-400 hover:text-red-500 p-2 text-xs" title="Hapus">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'kepegawaian'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
                    &larr; Kembali
                </button>
                <button type="button" @click="activeTab = 'pernyataan'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Pernyataan & Tanda Tangan</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI I: PERNYATAAN & TANDA TANGAN -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'pernyataan'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">I. PERNYATAAN DAN TANDA TANGAN</h3>
                <p class="text-xs text-slate-400">Verifikasi keabsahan data dan persetujuan perusahaan</p>
            </div>

            <!-- Teks Pernyataan Resmi ABP -->
            <div class="p-5 rounded-2xl bg-teal-50/60 border border-teal-200 text-xs text-slate-700 leading-relaxed">
                <div class="flex items-center gap-2 font-bold text-brand-primary text-sm mb-1">
                    <i class="fa-solid fa-certificate"></i>
                    <span>Pernyataan Kebenaran Data</span>
                </div>
                <p class="italic font-medium">
                    "Saya menyatakan bahwa seluruh data yang saya berikan dalam formulir ini adalah benar dan dapat dipertanggungjawabkan. Saya bersedia memberitahukan kepada perusahaan apabila terdapat perubahan data."
                </p>
            </div>

            <!-- Tanda Tangan Karyawan & HRD -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">

                <!-- Tanda Tangan Karyawan -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                        <span class="text-xs font-bold text-brand-navy uppercase">Tanda Tangan Karyawan</span>
                        <span class="text-[10px] text-slate-400">Yang Membuat Pernyataan</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Karyawan</label>
                        <input type="text" name="employee_signature_name" value="{{ old('employee_signature_name') }}" placeholder="Ketik nama terang karyawan..." class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Tanda Tangan Karyawan</label>
                        <input type="file" name="doc_employee_signature" accept=".jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:border file:border-slate-200">
                    </div>
                </div>

                <!-- Tanda Tangan HRD / Pemeriksa -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                        <span class="text-xs font-bold text-brand-navy uppercase">Tanda Tangan HRD / Pemeriksa</span>
                        <span class="text-[10px] text-teal-600 font-bold">Verifikator</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama HRD / Pemeriksa</label>
                        <input type="text" name="hrd_signature_name" value="{{ old('hrd_signature_name', Auth::user()->name) }}" placeholder="Nama petugas pemeriksa..." class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Tanda Tangan HRD</label>
                        <input type="file" name="doc_hrd_signature" accept=".jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:border file:border-slate-200">
                    </div>
                </div>

            </div>

            <!-- Submit Final Button -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <button type="button" @click="activeTab = 'gaji'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
                    &larr; Kembali
                </button>
                <button type="submit" class="px-8 py-3.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-extrabold rounded-xl shadow-lg shadow-teal-900/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-check-circle text-sm"></i>
                    <span>Simpan & Terbitkan Biodata Karyawan</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
