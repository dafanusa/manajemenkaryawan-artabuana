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

    // Department quick add
    deptList: @js($departments),
    selectedDepartment: '{{ old('department') }}',
    newDeptModal: false,
    newDeptName: '',
    newDeptDesc: '',
    newDeptLoading: false,
    newDeptError: '',
    async saveNewDepartment() {
        if (!this.newDeptName.trim()) {
            this.newDeptError = 'Nama departemen tidak boleh kosong';
            return;
        }
        this.newDeptLoading = true;
        this.newDeptError = '';
        try {
            const res = await fetch('{{ route('departments.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: this.newDeptName,
                    description: this.newDeptDesc
                })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                if (!this.deptList.includes(data.department.name)) {
                    this.deptList.push(data.department.name);
                    this.deptList.sort();
                }
                this.selectedDepartment = data.department.name;
                this.newDeptModal = false;
                this.newDeptName = '';
                this.newDeptDesc = '';
            } else {
                this.newDeptError = data.message || 'Gagal menyimpan departemen';
            }
        } catch (e) {
            this.newDeptError = 'Terjadi kesalahan sistem: ' + e.message;
        } finally {
            this.newDeptLoading = false;
        }
    },

    // File Previews helper
    filePreviews: {},
    handleFileInput(event, key) {
        const file = event.target.files[0];
        if (!file) {
            delete this.filePreviews[key];
            return;
        }
        const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
        const isImage = file.type.startsWith('image/');
        const url = URL.createObjectURL(file);
        let formattedSize = file.size >= 1048576 ? (file.size / 1048576).toFixed(2) + ' MB' : (file.size / 1024).toFixed(1) + ' KB';
        this.filePreviews[key] = {
            name: file.name,
            size: formattedSize,
            type: file.type || (isPdf ? 'PDF' : 'File'),
            isPdf: isPdf,
            isImage: isImage,
            url: url
        };
    },
    clearFileInput(key, inputId) {
        delete this.filePreviews[key];
        const input = document.getElementById(inputId);
        if (input) input.value = '';
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

    // Repeater: Skills (Keahlian)
    skills: [
        { skill_name: '', proficiency_level: 'Menengah / Intermediate', notes: '' }
    ],
    addSkill() {
        this.skills.push({ skill_name: '', proficiency_level: 'Menengah / Intermediate', notes: '' });
    },
    removeSkill(idx) {
        this.skills.splice(idx, 1);
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

    // Repeater: SIM / Licenses
    licenses: [
        { license_type: 'SIM A', license_number: '', issue_date: '', expiry_date: '', notes: '' }
    ],
    addLicense() {
        this.licenses.push({ license_type: 'SIM A', license_number: '', issue_date: '', expiry_date: '', notes: '' });
    },
    removeLicense(idx) {
        this.licenses.splice(idx, 1);
    },

    // Repeater: Contracts PKWT
    contracts: [
        { contract_number: '', start_date: '{{ date('Y-m-d') }}', end_date: '{{ date('Y-m-d', strtotime('+1 year')) }}', position: '', department: '', project_location: '', notes: '' }
    ],
    addContract() {
        this.contracts.push({
            contract_number: '',
            start_date: '',
            end_date: '',
            position: '',
            department: this.selectedDepartment || '',
            project_location: '',
            notes: 'Perpanjangan Kontrak ke-' + (this.contracts.length + 1)
        });
    },
    removeContract(idx) {
        this.contracts.splice(idx, 1);
    },

    // Repeater: Leaves (Cuti)
    leaves: [
        { leave_start_date: '', leave_end_date: '', airline: '', total_days: 14, notes: 'Cuti Roster' }
    ],
    addLeave() {
        this.leaves.push({ leave_start_date: '', leave_end_date: '', airline: '', total_days: 14, notes: '' });
    },
    removeLeave(idx) {
        this.leaves.splice(idx, 1);
    },
    calculateLeaveDays(idx) {
        const item = this.leaves[idx];
        if (item.leave_start_date && item.leave_end_date) {
            const start = new Date(item.leave_start_date);
            const end = new Date(item.leave_end_date);
            const diffTime = end - start;
            if (diffTime >= 0) {
                item.total_days = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            }
        }
    },

    // Repeater: Allowances (Tunjangan)
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
    employmentStatus: 'Aktif',
    contractType: 'PKWT'
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
            <span>F. Administrasi & SIM</span>
        </button>
        <button type="button" @click="activeTab = 'kepegawaian'" :class="activeTab === 'kepegawaian' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <span>G. Kepegawaian & Kontrak</span>
        </button>
        <button type="button" @click="activeTab = 'gaji'" :class="activeTab === 'gaji' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            @if(Auth::user()->isSuperAdmin())
            <span>H. Gaji & Cuti</span>
            @else
            <span>H. Data Cuti</span>
            @endif
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
                    <p class="text-xs text-slate-400">Informasi identitas pribadi, kependudukan, dan pas foto resmi 3x4</p>
                </div>
                <span class="text-xs font-bold text-brand-primary bg-teal-50 px-2.5 py-1 rounded-lg">Wajib Diisi</span>
            </div>

            <!-- Foto Preview & Upload Row -->
            <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div class="relative w-28 h-36 rounded-2xl bg-white border-2 border-dashed border-slate-300 overflow-hidden flex items-center justify-center flex-shrink-0 shadow-sm">
                    <template x-if="photoPreview">
                        <img :src="photoPreview" alt="Foto Preview" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!photoPreview">
                        <div class="text-center p-2 text-slate-400">
                            <i class="fa-solid fa-camera text-2xl mb-1 text-slate-300"></i>
                            <span class="block text-[10px] font-semibold">Pas Foto 3x4</span>
                        </div>
                    </template>
                </div>

                <div class="flex-1 space-y-2 text-xs">
                    <span class="block font-bold text-slate-700">Pas Foto Karyawan (Ukuran 3x4)</span>
                    <p class="text-slate-400 text-[11px]">Format didukung: JPG, JPEG, PNG. Maksimal ukuran berkas 5MB dengan latar belakang polos.</p>
                    <input type="file" name="photo" id="photo_input" accept="image/*" @change="updatePhotoPreview" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-primary file:text-white hover:file:bg-brand-primary-hover file:cursor-pointer">
                </div>
            </div>

            <!-- Biodata Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- Nama Lengkap -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap Sesuai KTP <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Contoh: Yohanes Tabuni" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="Laki-laki" {{ old('gender') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place') }}" placeholder="Contoh: Jayapura" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
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

                <!-- Suku Bangsa -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Suku / Asal Etnis <span class="text-red-500">*</span></label>
                    <select name="ethnicity" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="Papua" {{ old('ethnicity') === 'Papua' ? 'selected' : '' }}>Papua (OAP)</option>
                        <option value="Non Papua" {{ old('ethnicity') === 'Non Papua' ? 'selected' : '' }}>Non Papua</option>
                    </select>
                </div>

                <!-- Status Pernikahan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Pernikahan</label>
                    <select name="marital_status" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="">Pilih Status</option>
                        <option value="Belum Menikah" {{ old('marital_status') === 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="Menikah" {{ old('marital_status') === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                        <option value="Cerai Hidup" {{ old('marital_status') === 'Cerai Hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                        <option value="Cerai Mati" {{ old('marital_status') === 'Cerai Mati' ? 'selected' : '' }}>Cerai Mati</option>
                    </select>
                </div>

                <!-- No Telepon / WA -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. HP / WhatsApp Aktif</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email Pribadi</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- NIK KTP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" name="nik" value="{{ old('nik') }}" placeholder="16 digit nomor KTP" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- No KK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Kartu Keluarga (No. KK)</label>
                    <input type="text" name="kk_number" value="{{ old('kk_number') }}" placeholder="16 digit nomor KK" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload Dokumen KTP with live preview -->
                <div class="sm:col-span-2 lg:col-span-1 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Upload Dokumen KTP</label>
                    <input type="file" name="doc_ktp" id="input_doc_ktp" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_ktp')" class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:border file:border-slate-200 hover:file:bg-slate-100">

                    <template x-if="filePreviews['doc_ktp']">
                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                            <div class="flex items-center gap-2 truncate">
                                <i :class="filePreviews['doc_ktp'].isPdf ? 'fa-solid fa-file-pdf text-red-500' : 'fa-solid fa-file-image text-brand-primary'"></i>
                                <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_ktp'].name"></span>
                                <span class="text-[10px] text-slate-400" x-text="'(' + filePreviews['doc_ktp'].size + ')'"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="openFilePreview('KTP', filePreviews['doc_ktp'].url, filePreviews['doc_ktp'].name)" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded hover:bg-teal-100">
                                    Pratinjau
                                </button>
                                <button type="button" @click="clearFileInput('doc_ktp', 'input_doc_ktp')" class="text-red-400 hover:text-red-600 p-1 text-xs">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Upload Dokumen KK with live preview -->
                <div class="sm:col-span-2 lg:col-span-1 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Upload Dokumen Kartu Keluarga (KK)</label>
                    <input type="file" name="doc_kk" id="input_doc_kk" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_kk')" class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:border file:border-slate-200 hover:file:bg-slate-100">

                    <template x-if="filePreviews['doc_kk']">
                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                            <div class="flex items-center gap-2 truncate">
                                <i :class="filePreviews['doc_kk'].isPdf ? 'fa-solid fa-file-pdf text-red-500' : 'fa-solid fa-file-image text-brand-primary'"></i>
                                <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_kk'].name"></span>
                                <span class="text-[10px] text-slate-400" x-text="'(' + filePreviews['doc_kk'].size + ')'"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="openFilePreview('Kartu Keluarga', filePreviews['doc_kk'].url, filePreviews['doc_kk'].name)" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded hover:bg-teal-100">
                                    Pratinjau
                                </button>
                                <button type="button" @click="clearFileInput('doc_kk', 'input_doc_kk')" class="text-red-400 hover:text-red-600 p-1 text-xs">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Alamat KTP -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap Sesuai KTP</label>
                    <textarea name="ktp_address" rows="2" placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten..." class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('ktp_address') }}</textarea>
                </div>

                <!-- Alamat Domisili -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Domisili Saat Ini (Bila berbeda dengan KTP)</label>
                    <textarea name="domicile_address" rows="2" placeholder="Alamat tempat tinggal saat ini di Timika / wilayah proyek..." class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('domicile_address') }}</textarea>
                </div>

            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'kontak'" class="px-5 py-2.5 bg-brand-navy hover:bg-brand-navy-dark text-white text-xs font-bold rounded-xl transition flex items-center gap-2">
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
                <h3 class="text-base font-bold text-brand-navy">B. KONTAK DARURAT (EMERGENCY CONTACT)</h3>
                <p class="text-xs text-slate-400">Pihak keluarga atau kerabat yang wajib dihubungi perusahaan dalam situasi darurat operasional</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap Kontak Darurat</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" placeholder="Contoh: Martha Kogoya" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Hubungan Keluarga</label>
                    <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" placeholder="Istri / Suami / Orang Tua / Saudara Kandung" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Telepon Kontak Utama</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" placeholder="08XXXXXXXXXX" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Telepon Alternatif (Bila Ada)</label>
                    <input type="text" name="emergency_contact_alt_phone" value="{{ old('emergency_contact_alt_phone') }}" placeholder="08XXXXXXXXXX" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Tempat Tinggal Kontak Darurat</label>
                    <input type="text" name="emergency_contact_address" value="{{ old('emergency_contact_address') }}" placeholder="Kota / Alamat tinggal..." class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'pribadi'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-50">
                    &larr; Kembali
                </button>
                <button type="button" @click="activeTab = 'pendidikan'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Pendidikan</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI C: PENDIDIKAN (REPEATER) -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'pendidikan'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">C. RIWAYAT PENDIDIKAN FORMAL</h3>
                    <p class="text-xs text-slate-400">Riwayat jenjang pendidikan formal dari tingkat dasar hingga pendidikan tinggi terakhir</p>
                </div>
                <button type="button" @click="addEducation()" class="px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> + Tambah Jenjang
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(edu, idx) in educations" :key="idx">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-graduation-cap text-teal-600"></i>
                                <span x-text="'Pendidikan ' + (idx + 1) + ': ' + edu.level"></span>
                            </span>
                            <button type="button" @click="removeEducation(idx)" class="text-slate-400 hover:text-red-500 p-1 text-xs" title="Hapus">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jenjang</label>
                                <select :name="`educations[${idx}][level]`" x-model="edu.level" class="w-full p-2 rounded-xl border border-slate-200 bg-white">
                                    <option value="SD">SD</option>
                                    <option value="SLTP">SLTP / SMP</option>
                                    <option value="SLTA">SLTA / SMA / SMK</option>
                                    <option value="D3">Diploma 3 (D3)</option>
                                    <option value="S1">Strata 1 (S1)</option>
                                    <option value="S2">Strata 2 (S2)</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Sekolah / Universitas</label>
                                <input type="text" :name="`educations[${idx}][institution_name]`" x-model="edu.institution_name" placeholder="Nama instansi..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jurusan / Program Studi</label>
                                <input type="text" :name="`educations[${idx}][major]`" x-model="edu.major" placeholder="IPA/IPS/Teknik/dll..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Tahun Kelulusan</label>
                                <input type="text" :name="`educations[${idx}][graduation_year]`" x-model="edu.graduation_year" placeholder="Contoh: 2020" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nomor Ijazah</label>
                                <input type="text" :name="`educations[${idx}][certificate_number]`" x-model="edu.certificate_number" placeholder="No seri ijazah..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block font-bold text-slate-600 mb-1">Upload File Ijazah</label>
                                <input type="file" :name="`educations[${idx}][document]`" :id="`edu_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `edu_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-200 hover:file:bg-slate-300">

                                <template x-if="filePreviews[`edu_${idx}`]">
                                    <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                        <div class="flex items-center gap-2 truncate">
                                            <i :class="filePreviews[`edu_${idx}`].isPdf ? 'fa-solid fa-file-pdf text-red-500' : 'fa-solid fa-file-image text-brand-primary'"></i>
                                            <span class="font-medium truncate text-[11px]" x-text="filePreviews[`edu_${idx}`].name"></span>
                                            <span class="text-[10px] text-slate-400" x-text="'(' + filePreviews[`edu_${idx}`].size + ')'"></span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="openFilePreview('Ijazah ' + edu.level, filePreviews[`edu_${idx}`].url, filePreviews[`edu_${idx}`].name)" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded hover:bg-teal-100">
                                                Pratinjau
                                            </button>
                                            <button type="button" @click="clearFileInput(`edu_${idx}`, `edu_doc_${idx}`)" class="text-red-400 hover:text-red-600 p-1 text-xs">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
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
                    <h3 class="text-base font-bold text-brand-navy">D. PENGALAMAN KERJA SEBELUMNYA</h3>
                    <p class="text-xs text-slate-400">Riwayat perusahaan tempat bekerja, jabatan, lama masa kerja, dan surat paklaring</p>
                </div>
                <button type="button" @click="addExperience()" class="px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> + Tambah Pengalaman
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(exp, idx) in experiences" :key="idx">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-briefcase text-teal-600"></i>
                                <span x-text="'Pengalaman ' + (idx + 1)"></span>
                            </span>
                            <button type="button" @click="removeExperience(idx)" class="text-slate-400 hover:text-red-500 p-1 text-xs" title="Hapus">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Perusahaan / Kontraktor</label>
                                <input type="text" :name="`work_experiences[${idx}][company_name]`" x-model="exp.company_name" placeholder="Nama PT / Instansi..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jabatan / Posisi</label>
                                <input type="text" :name="`work_experiences[${idx}][position]`" x-model="exp.position" placeholder="Jabatan..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Periode (Tahun - Tahun)</label>
                                <input type="text" :name="`work_experiences[${idx}][period]`" x-model="exp.period" placeholder="Contoh: 2019 - 2022" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-600 mb-1">Alasan Berhenti / Resign</label>
                                <input type="text" :name="`work_experiences[${idx}][reason_for_leaving]`" x-model="exp.reason_for_leaving" placeholder="Alasan berhenti / selesai kontrak..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Upload Paklaring / Dokumen</label>
                                <input type="file" :name="`work_experiences[${idx}][document]`" :id="`exp_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `exp_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-200 hover:file:bg-slate-300">

                                <template x-if="filePreviews[`exp_${idx}`]">
                                    <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                        <div class="flex items-center gap-2 truncate">
                                            <i :class="filePreviews[`exp_${idx}`].isPdf ? 'fa-solid fa-file-pdf text-red-500' : 'fa-solid fa-file-image text-brand-primary'"></i>
                                            <span class="font-medium truncate text-[11px]" x-text="filePreviews[`exp_${idx}`].name"></span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="openFilePreview('Paklaring ' + (exp.company_name || ''), filePreviews[`exp_${idx}`].url, filePreviews[`exp_${idx}`].name)" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded hover:bg-teal-100">Pratinjau</button>
                                            <button type="button" @click="clearFileInput(`exp_${idx}`, `exp_doc_${idx}`)" class="text-red-400 hover:text-red-600 p-1 text-xs"><i class="fa-solid fa-xmark"></i></button>
                                        </div>
                                    </div>
                                </template>
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
                <p class="text-xs text-slate-400">Kelola daftar keahlian relasional karyawan dan sertifikasi profesi resmi</p>
            </div>

            <!-- Repeater Keahlian (Skills) Sesuai Permintaan Revisi #6 -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-screwdriver-wrench text-teal-600"></i>
                            <span>Daftar Keahlian Karyawan (Skills List)</span>
                        </h4>
                        <p class="text-[11px] text-slate-400">Tambahkan berbagai kemampuan teknis, manajerial, atau operasional</p>
                    </div>
                    <button type="button" @click="addSkill()" class="px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i> + Tambah Keahlian
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(skill, idx) in skills" :key="idx">
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs items-end">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Keahlian <span class="text-red-500">*</span></label>
                                <input type="text" :name="`skills[${idx}][skill_name]`" x-model="skill.skill_name" placeholder="Contoh: Heavy Equipment Maintenance / Excel / Python..." class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Tingkat / Level Keahlian</label>
                                <select :name="`skills[${idx}][proficiency_level]`" x-model="skill.proficiency_level" class="w-full p-2 rounded-xl border border-slate-200 bg-white">
                                    <option value="Dasar / Beginner">Dasar / Beginner</option>
                                    <option value="Menengah / Intermediate">Menengah / Intermediate</option>
                                    <option value="Mahir / Advanced">Mahir / Advanced</option>
                                    <option value="Ahli / Expert">Ahli / Expert</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block font-bold text-slate-600 mb-1">Keterangan Tambahan</label>
                                    <input type="text" :name="`skills[${idx}][notes]`" x-model="skill.notes" placeholder="Catatan pengalaman keahlian..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>
                                <button type="button" @click="removeSkill(idx)" class="text-slate-400 hover:text-red-500 p-2 text-xs" title="Hapus">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Ringkasan Keterangan Keahlian Teks Lama (Backwards Compatible) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-3 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ringkasan Keahlian Utama (Opsional)</label>
                    <textarea name="skills_summary" rows="2" placeholder="Uraikan keahlian umum karyawan..." class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('skills_summary') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alat / Software yang Dikuasai (Opsional)</label>
                    <textarea name="tools_software" rows="2" placeholder="Contoh: Dispatch, SAP, CAT ET, AutoCAD..." class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('tools_software') }}</textarea>
                </div>
            </div>

            <!-- Sertifikat Repeater -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-award text-amber-500"></i>
                            <span>Sertifikat Kompetensi & Pelatihan</span>
                        </h4>
                        <p class="text-[11px] text-slate-400">Sertifikat pelatihan internal/eksternal, SIO, POP, K3, dsb</p>
                    </div>
                    <button type="button" @click="addCertificate()" class="px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1.5">
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
                                    <label class="block font-bold text-slate-600 mb-1">Upload File Sertifikat</label>
                                    <input type="file" :name="`certificates[${idx}][document]`" :id="`cert_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `cert_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:bg-slate-200">

                                    <template x-if="filePreviews[`cert_${idx}`]">
                                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                            <span class="font-medium truncate text-[11px]" x-text="filePreviews[`cert_${idx}`].name"></span>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="openFilePreview('Sertifikat ' + (cert.certificate_name || ''), filePreviews[`cert_${idx}`].url, filePreviews[`cert_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                                <button type="button" @click="clearFileInput(`cert_${idx}`, `cert_doc_${idx}`)" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                        </div>
                                    </template>
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
                    <span>Lanjut: Data Administrasi & SIM</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI F: DATA ADMINISTRASI & SIM / LICENSE -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'administrasi'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">F. DATA ADMINISTRASI, KEUANGAN & SIM / LICENSE</h3>
                <p class="text-xs text-slate-400">Pajak (NPWP, PTKP), BPJS, Rekening Bank, dan Kepemilikan SIM / License Operasional</p>
            </div>

            <!-- Pajak & BPJS & Bank Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- NPWP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NPWP / NIK Pajak</label>
                    <input type="text" name="npwp_number" value="{{ old('npwp_number') }}" placeholder="15 atau 16 digit NPWP" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload NPWP with live preview -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Upload Kartu NPWP</label>
                    <input type="file" name="doc_npwp" id="input_doc_npwp" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_npwp')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">

                    <template x-if="filePreviews['doc_npwp']">
                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                            <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_npwp'].name"></span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="openFilePreview('NPWP', filePreviews['doc_npwp'].url, filePreviews['doc_npwp'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <button type="button" @click="clearFileInput('doc_npwp', 'input_doc_npwp')" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </template>
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
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Upload Kartu BPJS Kesehatan</label>
                    <input type="file" name="doc_bpjs_kes" id="input_doc_bpjs_kes" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_bpjs_kes')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">

                    <template x-if="filePreviews['doc_bpjs_kes']">
                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                            <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_bpjs_kes'].name"></span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="openFilePreview('BPJS Kesehatan', filePreviews['doc_bpjs_kes'].url, filePreviews['doc_bpjs_kes'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <button type="button" @click="clearFileInput('doc_bpjs_kes', 'input_doc_bpjs_kes')" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </template>
                </div>

                <div></div> <!-- Spacer -->

                <!-- BPJS TK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. BPJS Ketenagakerjaan</label>
                    <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number') }}" placeholder="No KPJ BPJS TK" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload BPJS TK -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Upload Kartu BPJS TK</label>
                    <input type="file" name="doc_bpjs_tk" id="input_doc_bpjs_tk" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_bpjs_tk')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">

                    <template x-if="filePreviews['doc_bpjs_tk']">
                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                            <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_bpjs_tk'].name"></span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="openFilePreview('BPJS Ketenagakerjaan', filePreviews['doc_bpjs_tk'].url, filePreviews['doc_bpjs_tk'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <button type="button" @click="clearFileInput('doc_bpjs_tk', 'input_doc_bpjs_tk')" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </template>
                </div>

                <div></div> <!-- Spacer -->

                <!-- Data Bank -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Mandiri / BCA / BNI / Papua" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Rekening</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Nomor rekening payroll" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Pemilik Rekening</label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder') }}" placeholder="Sesuai buku tabungan" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Upload Buku Rekening -->
                <div class="sm:col-span-2 lg:col-span-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Upload Buku Rekening (Halaman Depan)</label>
                    <input type="file" name="doc_bank_book" id="input_doc_bank_book" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_bank_book')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">

                    <template x-if="filePreviews['doc_bank_book']">
                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                            <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_bank_book'].name"></span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="openFilePreview('Buku Rekening', filePreviews['doc_bank_book'].url, filePreviews['doc_bank_book'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <button type="button" @click="clearFileInput('doc_bank_book', 'input_doc_bank_book')" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- SIM / LICENSE REPEATER (Sesuai Revisi #10) -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-id-card-clip text-teal-600"></i>
                            <span>Daftar SIM & License Kerja (Driver / Operator / K3 / Blasting)</span>
                        </h4>
                        <p class="text-[11px] text-slate-400">Dukungan lebih dari satu SIM (SIM A, SIM B1, SIM B2) atau Lisensi Khusus Pertambangan</p>
                    </div>
                    <button type="button" @click="addLicense()" class="px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i> + Tambah SIM / License
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(lic, idx) in licenses" :key="idx">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-address-card text-teal-600"></i>
                                    <span x-text="'SIM / License ' + (idx + 1) + ': ' + lic.license_type"></span>
                                </span>
                                <button type="button" @click="removeLicense(idx)" class="text-slate-400 hover:text-red-500 p-1 text-xs" title="Hapus">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Jenis SIM / License <span class="text-red-500">*</span></label>
                                    <select :name="`licenses[${idx}][license_type]`" x-model="lic.license_type" class="w-full p-2 rounded-xl border border-slate-200 bg-white">
                                        <option value="SIM A">SIM A (Mobil Pribadi)</option>
                                        <option value="SIM B1">SIM B1 (Bus / Truk Sedang)</option>
                                        <option value="SIM B1 Umum">SIM B1 Umum</option>
                                        <option value="SIM B2">SIM B2 (Alat Berat / Gandengan)</option>
                                        <option value="SIM B2 Umum">SIM B2 Umum</option>
                                        <option value="SIM C">SIM C (Sepeda Motor)</option>
                                        <option value="SIO Forklift">SIO Forklift</option>
                                        <option value="SIO Excavator">SIO Excavator</option>
                                        <option value="SIO Loader">SIO Wheel Loader</option>
                                        <option value="SIO Dump Truck">SIO Haul / Dump Truck</option>
                                        <option value="Mine Driver Permit (MDP)">Mine Driver Permit (MDP)</option>
                                        <option value="Blasting License">Blasting License / Juru Ledak</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Nomor SIM / License <span class="text-red-500">*</span></label>
                                    <input type="text" :name="`licenses[${idx}][license_number]`" x-model="lic.license_number" placeholder="Nomor registrasi..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Tanggal Terbit</label>
                                    <input type="date" :name="`licenses[${idx}][issue_date]`" x-model="lic.issue_date" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Tanggal Berlaku / Expired</label>
                                    <input type="date" :name="`licenses[${idx}][expiry_date]`" x-model="lic.expiry_date" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-slate-600 mb-1">Keterangan Tambahan</label>
                                    <input type="text" :name="`licenses[${idx}][notes]`" x-model="lic.notes" placeholder="Penerbit Polresta / Kemnaker..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-slate-600 mb-1">Upload Dokumen SIM / License</label>
                                    <input type="file" :name="`licenses[${idx}][document]`" :id="`lic_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `lic_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-200">

                                    <template x-if="filePreviews[`lic_${idx}`]">
                                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                            <span class="font-medium truncate text-[11px]" x-text="filePreviews[`lic_${idx}`].name"></span>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="openFilePreview('SIM / License ' + lic.license_type, filePreviews[`lic_${idx}`].url, filePreviews[`lic_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                                <button type="button" @click="clearFileInput(`lic_${idx}`, `lic_doc_${idx}`)" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'keahlian'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
                    &larr; Kembali
                </button>
                <button type="button" @click="activeTab = 'kepegawaian'" class="px-5 py-2.5 bg-brand-navy text-white text-xs font-bold rounded-xl flex items-center gap-2">
                    <span>Lanjut: Data Kepegawaian & Kontrak</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SEKSI G: DATA KEPEGAWAIAN & KONTRAK PKWT -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'kepegawaian'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy">G. DATA KEPEGAWAIAN & RIWAYAT KONTRAK PKWT (HRD)</h3>
                    <p class="text-xs text-slate-400">Penempatan divisi, departemen resmi, status kerja, dan riwayat perpanjangan kontrak</p>
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
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Upload ID Card (Bila Ada)</label>
                    <input type="file" name="doc_id_card" id="input_doc_id_card" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_id_card')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">

                    <template x-if="filePreviews['doc_id_card']">
                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                            <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_id_card'].name"></span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="openFilePreview('ID Card', filePreviews['doc_id_card'].url, filePreviews['doc_id_card'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <button type="button" @click="clearFileInput('doc_id_card', 'input_doc_id_card')" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Status Kepegawaian (Aktif / Tidak Aktif) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Kepegawaian <span class="text-red-500">*</span></label>
                    <select name="employment_status" x-model="employmentStatus" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-bold">
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>

                <!-- Jenis Ikatan Kontrak -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Status Kontrak <span class="text-red-500">*</span></label>
                    <select name="contract_type" x-model="contractType" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-bold text-brand-primary">
                        <option value="PKWT">PKWT (Kontrak Waktu Tertentu)</option>
                        <option value="PKWTT">PKWTT (Karyawan Tetap)</option>
                        <option value="Harian Lepas">Harian Lepas / Project Casual</option>
                        <option value="Magang">Magang / Praktik Industri</option>
                    </select>
                </div>

                <!-- Jabatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jabatan / Posisi <span class="text-red-500">*</span></label>
                    <input type="text" name="position" value="{{ old('position') }}" required placeholder="Contoh: Heavy Equipment Mechanic" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Departemen (Relasional dari Database + Modal Tambah Manual) Sesuai Revisi #1 -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">Departemen <span class="text-red-500">*</span></label>
                        <button type="button" @click="newDeptModal = true" class="text-[11px] font-bold text-brand-primary hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-plus-circle"></i> + Tambah Baru
                        </button>
                    </div>
                    <select name="department" x-model="selectedDepartment" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="">Pilih Departemen</option>
                        <template x-for="dept in deptList" :key="dept">
                            <option :value="dept" x-text="dept" :selected="selectedDepartment === dept"></option>
                        </template>
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

                <!-- Tanggal Masuk (Join Date) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Masuk (Join Date) <span class="text-red-500">*</span></label>
                    <input type="date" name="join_date" value="{{ old('join_date', date('Y-m-d')) }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Tanggal Keluar (Leave Date) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tanggal Keluar (Leave Date)
                        <span class="text-[10px] text-slate-400 font-normal">(Wajib jika status Tidak Aktif)</span>
                    </label>
                    <input type="date" name="leave_date" value="{{ old('leave_date') }}" :required="employmentStatus === 'Tidak Aktif'" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

            </div>

            <!-- RIWAYAT KONTRAK KARYAWAN PKWT (Sesuai Revisi #7) -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-file-signature text-brand-primary"></i>
                            <span>Histori & Perpanjangan Kontrak (Khusus PKWT)</span>
                        </h4>
                        <p class="text-[11px] text-slate-400">Lacak berapa kali karyawan mengalami perpanjangan kontrak (Kontrak 1, Kontrak 2, dst.)</p>
                    </div>
                    <button type="button" @click="addContract()" class="px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i> + Tambah Kontrak
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(c, idx) in contracts" :key="idx">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-brand-primary text-white text-xs font-bold flex items-center justify-center" x-text="idx + 1"></span>
                                    <span class="text-xs font-bold text-brand-navy uppercase tracking-wider" x-text="'Karyawan Kontrak ' + (idx + 1)"></span>
                                    <span x-show="idx > 0" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800" x-text="'Perpanjangan ke-' + idx"></span>
                                </div>
                                <button type="button" @click="removeContract(idx)" class="text-slate-400 hover:text-red-500 p-1 text-xs" title="Hapus Kontrak">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Nomor Kontrak PKWT</label>
                                    <input type="text" :name="`contracts[${idx}][contract_number]`" x-model="c.contract_number" placeholder="Contoh: PKWT/ABP/2026/001" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Tanggal Mulai</label>
                                    <input type="date" :name="`contracts[${idx}][start_date]`" x-model="c.start_date" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Tanggal Selesai (Akhir Kontrak)</label>
                                    <input type="date" :name="`contracts[${idx}][end_date]`" x-model="c.end_date" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Jabatan Kontrak</label>
                                    <input type="text" :name="`contracts[${idx}][position]`" x-model="c.position" placeholder="Posisi penugasan..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Departemen</label>
                                    <input type="text" :name="`contracts[${idx}][department]`" x-model="c.department" placeholder="Departemen..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Lokasi / Proyek</label>
                                    <input type="text" :name="`contracts[${idx}][project_location]`" x-model="c.project_location" placeholder="Lokasi tugas..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Keterangan</label>
                                    <input type="text" :name="`contracts[${idx}][notes]`" x-model="c.notes" placeholder="Catatan evaluasi kerja / durasi..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Upload Dokumen Kontrak</label>
                                    <input type="file" :name="`contracts[${idx}][document]`" :id="`cnt_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `cnt_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:bg-slate-200">

                                    <template x-if="filePreviews[`cnt_${idx}`]">
                                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                            <span class="font-medium truncate text-[11px]" x-text="filePreviews[`cnt_${idx}`].name"></span>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="openFilePreview('Dokumen Kontrak ' + (idx + 1), filePreviews[`cnt_${idx}`].url, filePreviews[`cnt_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                                <button type="button" @click="clearFileInput(`cnt_${idx}`, `cnt_doc_${idx}`)" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
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
        <!-- SEKSI H: GAJI & CUTI (DILINDUNGI ROLE GAJI) -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'gaji'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">

            @if(Auth::user()->isSuperAdmin())
            <!-- A. DATA GAJI (SUPER ADMIN ONLY) Sesuai Revisi #2, #3, #4 -->
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy flex items-center gap-2">
                        <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                        <span>H.1 DATA GAJI KARYAWAN</span>
                        <span class="text-[10px] bg-purple-100 text-purple-700 font-bold px-2 py-0.5 rounded-full border border-purple-200">Khusus Super Admin</span>
                    </h3>
                    <p class="text-xs text-slate-400">Penetapan Line Grade, Level, Gaji Pokok, Gaji Per Jam, dan Tunjangan Rutin</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Line Grade (Grade A - F) Sesuai Revisi #3 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Line Grade <span class="text-red-500">*</span></label>
                    <select name="line_grade" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-bold text-brand-navy">
                        <option value="">Pilih Grade</option>
                        @foreach($grades as $grd)
                        <option value="{{ $grd }}" {{ old('line_grade') === $grd ? 'selected' : '' }}>{{ $grd }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Level (Level 1 - 5) Sesuai Revisi #3 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Level Jabatan <span class="text-red-500">*</span></label>
                    <select name="salary_level" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-bold text-brand-navy">
                        <option value="">Pilih Level</option>
                        @foreach($levels as $lvl)
                        <option value="{{ $lvl }}" {{ old('salary_level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Gaji Pokok -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Gaji Pokok (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="1000" min="0" name="basic_salary" value="{{ old('basic_salary', 0) }}" placeholder="Contoh: 15000000" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono font-bold text-slate-800">
                </div>

                <!-- Gaji Per Jam -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Gaji Per Jam (Rp)</label>
                    <input type="number" step="100" min="0" name="hourly_rate" value="{{ old('hourly_rate', 0) }}" placeholder="Contoh: 85000" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono font-bold text-slate-800">
                </div>

                <!-- Posisi Saat Ini -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Posisi / Kategori Gaji Saat Ini</label>
                    <input type="text" name="current_position" value="{{ old('current_position') }}" placeholder="Posisi yang dijalani saat ini..." class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Lama Kerja di Perusahaan Sebelumnya -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lama Kerja Sebelumnya (Tahun)</label>
                    <input type="number" min="0" name="previous_work_years" value="{{ old('previous_work_years', 0) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lama Kerja Sebelumnya (Bulan)</label>
                    <input type="number" min="0" max="11" name="previous_work_months" value="{{ old('previous_work_months', 0) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <!-- Upload Slip Gaji -->
                <div class="sm:col-span-2 lg:col-span-4 p-3.5 rounded-2xl bg-purple-50/50 border border-purple-200 space-y-1.5">
                    <label class="block text-xs font-bold text-purple-900">Upload Slip Gaji 3 Bulan Terakhir (Super Admin)</label>
                    <input type="file" name="doc_salary_slip" id="input_doc_salary_slip" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_salary_slip')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">

                    <template x-if="filePreviews['doc_salary_slip']">
                        <div class="p-2 rounded-xl bg-white border border-purple-200 flex items-center justify-between text-xs mt-1">
                            <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_salary_slip'].name"></span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="openFilePreview('Slip Gaji', filePreviews['doc_salary_slip'].url, filePreviews['doc_salary_slip'].name)" class="px-2 py-0.5 text-[10px] font-bold text-purple-700 bg-purple-50 rounded">Pratinjau</button>
                                <button type="button" @click="clearFileInput('doc_salary_slip', 'input_doc_salary_slip')" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Repeater Tunjangan Lainnya -->
            <div class="space-y-4 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider">Tunjangan Lainnya (Allowances)</h4>
                        <p class="text-[11px] text-slate-400">Tunjangan mess, lokasi tambang, jabatan, makan, dan transport bulanan</p>
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
            @endif

            <!-- B. DATA CUTI KARYAWAN (REPEATER) Sesuai Revisi #5 -->
            <div class="space-y-4 {{ Auth::user()->isSuperAdmin() ? 'pt-6 border-t-2 border-slate-200' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-plane-departure text-teal-600"></i>
                            <span>Riwayat Pencatatan Cuti Karyawan</span>
                        </h4>
                        <p class="text-[11px] text-slate-400">Pencatatan cuti roster lapangan / tahunan: tanggal berangkat, kembali, maskapai, dan jumlah hari</p>
                    </div>
                    <button type="button" @click="addLeave()" class="px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-primary font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i> + Tambah Cuti
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(lv, idx) in leaves" :key="idx">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs font-bold flex items-center justify-center" x-text="idx + 1"></span>
                                    <span class="text-xs font-bold text-brand-navy uppercase tracking-wider" x-text="'Cuti ' + (idx + 1)"></span>
                                </div>
                                <button type="button" @click="removeLeave(idx)" class="text-slate-400 hover:text-red-500 p-1 text-xs" title="Hapus Cuti">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Tanggal Berangkat <span class="text-red-500">*</span></label>
                                    <input type="date" :name="`leaves[${idx}][leave_start_date]`" x-model="lv.leave_start_date" @change="calculateLeaveDays(idx)" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Tanggal Kembali <span class="text-red-500">*</span></label>
                                    <input type="date" :name="`leaves[${idx}][leave_end_date]`" x-model="lv.leave_end_date" @change="calculateLeaveDays(idx)" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Maskapai Penerbangan</label>
                                    <input type="text" :name="`leaves[${idx}][airline]`" x-model="lv.airline" placeholder="Garuda / Batik Air / Lion Air..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Jumlah Hari</label>
                                    <input type="number" min="1" :name="`leaves[${idx}][total_days]`" x-model="lv.total_days" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Keterangan / Keperluan</label>
                                    <input type="text" :name="`leaves[${idx}][notes]`" x-model="lv.notes" placeholder="Cuti Roster / Tahunan / Keperluan Keluarga..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div class="sm:col-span-2 lg:col-span-5">
                                    <label class="block font-bold text-slate-600 mb-1">Upload Form Cuti / Boarding Pass Tiket (Opsional)</label>
                                    <input type="file" :name="`leaves[${idx}][document]`" :id="`lv_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `lv_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-200">

                                    <template x-if="filePreviews[`lv_${idx}`]">
                                        <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                            <span class="font-medium truncate text-[11px]" x-text="filePreviews[`lv_${idx}`].name"></span>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="openFilePreview('Tiket / Cuti ' + (idx + 1), filePreviews[`lv_${idx}`].url, filePreviews[`lv_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                                <button type="button" @click="clearFileInput(`lv_${idx}`, `lv_doc_${idx}`)" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
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
                        <input type="file" name="doc_employee_signature" id="input_doc_employee_signature" accept=".jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_employee_signature')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:border file:border-slate-200">

                        <template x-if="filePreviews['doc_employee_signature']">
                            <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_employee_signature'].name"></span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="openFilePreview('TTD Karyawan', filePreviews['doc_employee_signature'].url, filePreviews['doc_employee_signature'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                    <button type="button" @click="clearFileInput('doc_employee_signature', 'input_doc_employee_signature')" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                        </template>
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
                        <input type="file" name="doc_hrd_signature" id="input_doc_hrd_signature" accept=".jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_hrd_signature')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:border file:border-slate-200">

                        <template x-if="filePreviews['doc_hrd_signature']">
                            <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_hrd_signature'].name"></span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="openFilePreview('TTD HRD', filePreviews['doc_hrd_signature'].url, filePreviews['doc_hrd_signature'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                    <button type="button" @click="clearFileInput('doc_hrd_signature', 'input_doc_hrd_signature')" class="text-red-400 hover:text-red-600 p-0.5 text-xs"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                        </template>
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

    <!-- QUICK ADD DEPARTMENT MODAL (Sesuai Revisi #1) -->
    <div x-show="newDeptModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="newDeptModal = false"
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 transform transition-all"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 text-brand-primary flex items-center justify-center">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-brand-navy">Tambah Departemen Baru</h3>
                </div>
                <button type="button" @click="newDeptModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <template x-if="newDeptError">
                <div class="mb-3 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700" x-text="newDeptError"></div>
            </template>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Departemen <span class="text-red-500">*</span></label>
                    <input type="text" x-model="newDeptName" placeholder="Contoh: Information Technology" class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Keterangan / Deskripsi</label>
                    <textarea x-model="newDeptDesc" rows="3" placeholder="Deskripsi tugas departemen..." class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="newDeptModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" @click="saveNewDepartment()" :disabled="newDeptLoading" class="px-5 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white font-bold rounded-xl shadow-md transition disabled:opacity-50">
                        <span x-show="!newDeptLoading">Simpan & Gunakan</span>
                        <span x-show="newDeptLoading"><i class="fa-solid fa-spinner animate-spin"></i> Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
