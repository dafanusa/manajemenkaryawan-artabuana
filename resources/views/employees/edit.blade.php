@extends('layouts.app')

@section('title', 'Edit Karyawan - ' . $employee->full_name)
@section('page_title', 'Edit Biodata Karyawan: ' . $employee->full_name)

@section('content')
<div x-data="{
    activeTab: 'pribadi',

    photoPreview: @js($employee->photo ? asset('storage/' . $employee->photo) : null),
    updatePhotoPreview(event) {
        const file = event.target.files[0];
        if (file) {
            this.photoPreview = URL.createObjectURL(file);
        }
    },

    // Department quick add
    deptList: @js($departments),
    selectedDepartment: @js(old('department', $employee->department)),
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

    // File replace toggle state
    showUpload: {},

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
                ['level' => 'SD', 'institution_name' => '', 'major' => 'Umum', 'graduation_year' => '', 'certificate_number' => '', 'document_path' => ''],
                ['level' => 'SLTP', 'institution_name' => '', 'major' => 'Umum', 'graduation_year' => '', 'certificate_number' => '', 'document_path' => ''],
                ['level' => 'SLTA', 'institution_name' => '', 'major' => '', 'graduation_year' => '', 'certificate_number' => '', 'document_path' => ''],
                ['level' => 'S1', 'institution_name' => '', 'major' => '', 'graduation_year' => '', 'certificate_number' => '', 'document_path' => '']
            ]
    ),
    addEducation() {
        this.educations.push({ level: 'Lainnya', institution_name: '', major: '', graduation_year: '', certificate_number: '', document_path: '' });
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
                ['company_name' => '', 'position' => '', 'period' => '', 'reason_for_leaving' => '', 'document_path' => '']
            ]
    ),
    addExperience() {
        this.experiences.push({ company_name: '', position: '', period: '', reason_for_leaving: '', document_path: '' });
    },
    removeExperience(idx) {
        this.experiences.splice(idx, 1);
    },

    // Repeater: Skills (Keahlian) Sesuai Revisi #6
    skills: @js(
        $employee->skills->isNotEmpty()
            ? $employee->skills->map(fn($s) => [
                'skill_name' => $s->skill_name,
                'proficiency_level' => $s->proficiency_level ?? 'Menengah / Intermediate',
                'notes' => $s->notes ?? ''
            ])->values()
            : [
                ['skill_name' => '', 'proficiency_level' => 'Menengah / Intermediate', 'notes' => '']
            ]
    ),
    addSkill() {
        this.skills.push({ skill_name: '', proficiency_level: 'Menengah / Intermediate', notes: '' });
    },
    removeSkill(idx) {
        this.skills.splice(idx, 1);
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
                ['certificate_name' => '', 'certificate_number' => '', 'valid_until' => '', 'document_path' => '']
            ]
    ),
    addCertificate() {
        this.certificates.push({ certificate_name: '', certificate_number: '', valid_until: '', document_path: '' });
    },
    removeCertificate(idx) {
        this.certificates.splice(idx, 1);
    },

    // Repeater: SIM / Licenses Sesuai Revisi #10
    licenses: @js(
        $employee->licenses->isNotEmpty()
            ? $employee->licenses->map(fn($l) => [
                'license_type' => $l->license_type,
                'license_number' => $l->license_number,
                'issue_date' => $l->issue_date ? $l->issue_date->format('Y-m-d') : '',
                'expiry_date' => $l->expiry_date ? $l->expiry_date->format('Y-m-d') : '',
                'notes' => $l->notes ?? '',
                'document_path' => $l->document_path
            ])->values()
            : [
                ['license_type' => 'SIM A', 'license_number' => '', 'issue_date' => '', 'expiry_date' => '', 'notes' => '', 'document_path' => '']
            ]
    ),
    addLicense() {
        this.licenses.push({ license_type: 'SIM A', license_number: '', issue_date: '', expiry_date: '', notes: '', document_path: '' });
    },
    removeLicense(idx) {
        this.licenses.splice(idx, 1);
    },

    // Repeater: Contracts PKWT Sesuai Revisi #7
    contracts: @js(
        $employee->contracts->isNotEmpty()
            ? $employee->contracts->map(fn($c) => [
                'contract_number' => $c->contract_number,
                'start_date' => $c->start_date ? $c->start_date->format('Y-m-d') : '',
                'end_date' => $c->end_date ? $c->end_date->format('Y-m-d') : '',
                'position' => $c->position ?? '',
                'department' => $c->department ?? '',
                'project_location' => $c->project_location ?? '',
                'notes' => $c->notes ?? '',
                'status' => $c->status ?? 'Aktif',
                'document_path' => $c->document_path
            ])->values()
            : [
                ['contract_number' => 'PKWT/' . date('Y') . '/001', 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+1 year')), 'position' => '', 'department' => '', 'project_location' => '', 'notes' => '', 'status' => 'Aktif', 'document_path' => '']
            ]
    ),
    addContract() {
        this.contracts.push({
            contract_number: '',
            start_date: '',
            end_date: '',
            position: '{{ $employee->position }}',
            department: this.selectedDepartment || '{{ $employee->department }}',
            project_location: '{{ $employee->project_location }}',
            notes: 'Perpanjangan Kontrak ke-' + (this.contracts.length + 1),
            status: 'Aktif',
            document_path: ''
        });
    },
    removeContract(idx) {
        this.contracts.splice(idx, 1);
    },

    // Repeater: Leaves (Cuti) Sesuai Revisi #5
    leaves: @js(
        $employee->leaves->isNotEmpty()
            ? $employee->leaves->map(fn($l) => [
                'leave_start_date' => $l->leave_start_date ? $l->leave_start_date->format('Y-m-d') : '',
                'leave_end_date' => $l->leave_end_date ? $l->leave_end_date->format('Y-m-d') : '',
                'airline' => $l->airline ?? '',
                'total_days' => $l->total_days ?? 1,
                'notes' => $l->notes ?? '',
                'document_path' => $l->document_path
            ])->values()
            : [
                ['leave_start_date' => '', 'leave_end_date' => '', 'airline' => '', 'total_days' => 14, 'notes' => '', 'document_path' => '']
            ]
    ),
    addLeave() {
        this.leaves.push({ leave_start_date: '', leave_end_date: '', airline: '', total_days: 14, notes: '', document_path: '' });
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

    // Repeater: Allowances
    allowances: @js(
        $employee->allowances->isNotEmpty()
            ? $employee->allowances->map(fn($a) => [
                'allowance_name' => $a->allowance_name,
                'amount' => (float)$a->amount,
                'notes' => $a->notes ?? ''
            ])->values()
            : [
                ['allowance_name' => 'Tunjangan Lokasi', 'amount' => 0, 'notes' => ''],
                ['allowance_name' => 'Tunjangan Makan & Transport', 'amount' => 0, 'notes' => '']
            ]
    ),
    addAllowance() {
        this.allowances.push({ allowance_name: '', amount: 0, notes: '' });
    },
    removeAllowance(idx) {
        this.allowances.splice(idx, 1);
    },

    // Modal tambah histori gaji manual
    salaryHistoryModal: false,

    employmentStatus: @js(old('employment_status', $employee->employment_status)),
    contractType: @js(old('contract_type', $employee->contract_type ?? 'PKWT'))
}" class="space-y-6">

    <!-- FORM HEADER -->
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 text-brand-primary p-2 flex items-center justify-center flex-shrink-0 border border-teal-100">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="max-h-full object-contain">
            </div>
            <div>
                <span class="text-[11px] font-bold text-brand-primary tracking-wider uppercase">Mode Pengeditan Data</span>
                <h2 class="text-xl font-extrabold text-brand-navy">EDIT BIODATA: {{ $employee->full_name }}</h2>
                <p class="text-xs text-slate-400">ID Karyawan: <span class="font-mono font-bold text-slate-700">{{ $employee->employee_id }}</span> &bull; Status: {{ $employee->employment_status }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('employees.show', $employee) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Batal
            </a>
            <button type="button" @click="document.getElementById('editForm').submit()" class="px-5 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold shadow-md shadow-brand-primary/20 transition flex items-center gap-2">
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
            <span>Terdapat beberapa kesalahan input:</span>
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
    <form id="editForm" method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

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

            <!-- Foto Preview & Ganti Row -->
            <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div class="relative w-28 h-36 rounded-2xl bg-white border border-slate-300 overflow-hidden flex items-center justify-center flex-shrink-0 shadow-sm">
                    <template x-if="photoPreview">
                        <img :src="photoPreview" alt="Foto Karyawan" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!photoPreview">
                        <div class="text-center p-2 text-slate-400">
                            <i class="fa-solid fa-camera text-2xl mb-1 text-slate-300"></i>
                            <span class="block text-[10px] font-semibold">Belum Ada Foto</span>
                        </div>
                    </template>
                </div>

                <div class="flex-1 space-y-2 text-xs">
                    <span class="block font-bold text-slate-700">Pas Foto Karyawan (Ukuran 3x4)</span>
                    <p class="text-slate-400 text-[11px]">Format: JPG, JPEG, PNG. Mengunggah foto baru akan secara otomatis mengganti dan menghapus foto lama.</p>
                    <input type="file" name="photo" id="edit_photo" accept="image/*" @change="updatePhotoPreview" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-primary file:text-white hover:file:bg-brand-primary-hover file:cursor-pointer">
                </div>
            </div>

            <!-- Biodata Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap Sesuai KTP <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="Laki-laki" {{ old('gender', $employee->gender) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender', $employee->gender) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $employee->birth_place) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '') }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Agama <span class="text-red-500">*</span></label>
                    <select name="religion" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        @foreach($religions as $rel)
                        <option value="{{ $rel }}" {{ old('religion', $employee->religion) === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Suku / Asal Etnis <span class="text-red-500">*</span></label>
                    <select name="ethnicity" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="Papua" {{ old('ethnicity', $employee->ethnicity) === 'Papua' ? 'selected' : '' }}>Papua (OAP)</option>
                        <option value="Non Papua" {{ old('ethnicity', $employee->ethnicity) === 'Non Papua' ? 'selected' : '' }}>Non Papua</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Pernikahan</label>
                    <select name="marital_status" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="">Pilih Status</option>
                        <option value="Belum Menikah" {{ old('marital_status', $employee->marital_status) === 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="Menikah" {{ old('marital_status', $employee->marital_status) === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                        <option value="Cerai Hidup" {{ old('marital_status', $employee->marital_status) === 'Cerai Hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                        <option value="Cerai Mati" {{ old('marital_status', $employee->marital_status) === 'Cerai Mati' ? 'selected' : '' }}>Cerai Mati</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. HP / WhatsApp Aktif</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email Pribadi</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" name="nik" value="{{ old('nik', $employee->nik) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Kartu Keluarga (No. KK)</label>
                    <input type="text" name="kk_number" value="{{ old('kk_number', $employee->kk_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Dokumen KTP: Preview + Download + Ganti File Sesuai Revisi #8 & #9 -->
                @php $ktpDoc = $employee->getDocument('ktp'); @endphp
                <div class="sm:col-span-2 lg:col-span-1 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Dokumen KTP</label>

                    @if($ktpDoc)
                    <div class="p-2.5 rounded-xl bg-white border border-teal-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-teal-800">File saat ini:</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $ktpDoc->formatted_size }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs text-slate-700 truncate font-mono" title="{{ $ktpDoc->original_name }}">{{ $ktpDoc->original_name }}</span>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" @click="openFilePreview('KTP', '{{ $ktpDoc->url }}', '{{ $ktpDoc->original_name }}')" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded hover:bg-teal-100">Pratinjau</button>
                                <a href="{{ route('documents.download', $ktpDoc) }}" class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded hover:bg-slate-200">Unduh</a>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <button type="button" @click="showUpload['ktp'] = !showUpload['ktp']" class="text-teal-700 font-bold hover:underline">
                                <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Ganti File KTP
                            </button>
                        </div>
                    </div>
                    @endif

                    <div x-show="!{{ $ktpDoc ? 'true' : 'false' }} || showUpload['ktp']" class="space-y-1.5">
                        <input type="file" name="doc_ktp" id="edit_doc_ktp" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_ktp')" class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                        <template x-if="filePreviews['doc_ktp']">
                            <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs">
                                <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_ktp'].name"></span>
                                <button type="button" @click="openFilePreview('KTP Baru', filePreviews['doc_ktp'].url, filePreviews['doc_ktp'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Dokumen KK: Preview + Download + Ganti File -->
                @php $kkDoc = $employee->getDocument('kk'); @endphp
                <div class="sm:col-span-2 lg:col-span-1 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Dokumen Kartu Keluarga (KK)</label>

                    @if($kkDoc)
                    <div class="p-2.5 rounded-xl bg-white border border-teal-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-teal-800">File saat ini:</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $kkDoc->formatted_size }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs text-slate-700 truncate font-mono" title="{{ $kkDoc->original_name }}">{{ $kkDoc->original_name }}</span>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" @click="openFilePreview('Kartu Keluarga', '{{ $kkDoc->url }}', '{{ $kkDoc->original_name }}')" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded hover:bg-teal-100">Pratinjau</button>
                                <a href="{{ route('documents.download', $kkDoc) }}" class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded hover:bg-slate-200">Unduh</a>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <button type="button" @click="showUpload['kk'] = !showUpload['kk']" class="text-teal-700 font-bold hover:underline">
                                <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Ganti File KK
                            </button>
                        </div>
                    </div>
                    @endif

                    <div x-show="!{{ $kkDoc ? 'true' : 'false' }} || showUpload['kk']" class="space-y-1.5">
                        <input type="file" name="doc_kk" id="edit_doc_kk" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_kk')" class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                        <template x-if="filePreviews['doc_kk']">
                            <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs">
                                <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_kk'].name"></span>
                                <button type="button" @click="openFilePreview('KK Baru', filePreviews['doc_kk'].url, filePreviews['doc_kk'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap Sesuai KTP</label>
                    <textarea name="ktp_address" rows="2" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('ktp_address', $employee->ktp_address) }}</textarea>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Domisili Saat Ini</label>
                    <textarea name="domicile_address" rows="2" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('domicile_address', $employee->domicile_address) }}</textarea>
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
        @php $contact = $employee->emergencyContacts->first(); @endphp
        <div x-show="activeTab === 'kontak'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-brand-navy">B. KONTAK DARURAT (EMERGENCY CONTACT)</h3>
                <p class="text-xs text-slate-400">Pihak keluarga atau kerabat yang wajib dihubungi perusahaan dalam situasi darurat</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap Kontak Darurat</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $contact?->name) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Hubungan Keluarga</label>
                    <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $contact?->relationship) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Telepon Kontak Utama</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $contact?->phone) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Telepon Alternatif</label>
                    <input type="text" name="emergency_contact_alt_phone" value="{{ old('emergency_contact_alt_phone', $contact?->alternative_phone) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Tempat Tinggal Kontak Darurat</label>
                    <input type="text" name="emergency_contact_address" value="{{ old('emergency_contact_address', $contact?->address) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'pribadi'" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-xl text-slate-600">
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
                    <p class="text-xs text-slate-400">Riwayat jenjang pendidikan formal dari tingkat dasar hingga perguruan tinggi</p>
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
                                <input type="text" :name="`educations[${idx}][institution_name]`" x-model="edu.institution_name" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jurusan / Program Studi</label>
                                <input type="text" :name="`educations[${idx}][major]`" x-model="edu.major" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Tahun Lulus</label>
                                <input type="text" :name="`educations[${idx}][graduation_year]`" x-model="edu.graduation_year" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nomor Ijazah</label>
                                <input type="text" :name="`educations[${idx}][certificate_number]`" x-model="edu.certificate_number" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block font-bold text-slate-600 mb-1">Dokumen Ijazah</label>
                                <input type="hidden" :name="`educations[${idx}][document_path]`" :value="edu.document_path">

                                <template x-if="edu.document_path">
                                    <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-medium text-teal-800 text-[11px]">✓ Dokumen Ijazah Tersedia</span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="openFilePreview('Ijazah ' + edu.level, '{{ asset('storage') }}/' + edu.document_path, 'Ijazah')" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                            <a :href="'{{ asset('storage') }}/' + edu.document_path" target="_blank" download class="px-2 py-0.5 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                                        </div>
                                    </div>
                                </template>

                                <input type="file" :name="`educations[${idx}][document]`" :id="`edit_edu_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `edu_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-200">

                                <template x-if="filePreviews[`edu_${idx}`]">
                                    <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                        <span class="font-medium truncate text-[11px]" x-text="'File baru: ' + filePreviews[`edu_${idx}`].name"></span>
                                        <button type="button" @click="openFilePreview('Ijazah Baru', filePreviews[`edu_${idx}`].url, filePreviews[`edu_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
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
                                <input type="text" :name="`work_experiences[${idx}][company_name]`" x-model="exp.company_name" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jabatan / Posisi</label>
                                <input type="text" :name="`work_experiences[${idx}][position]`" x-model="exp.position" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Periode (Tahun - Tahun)</label>
                                <input type="text" :name="`work_experiences[${idx}][period]`" x-model="exp.period" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-600 mb-1">Alasan Berhenti / Resign</label>
                                <input type="text" :name="`work_experiences[${idx}][reason_for_leaving]`" x-model="exp.reason_for_leaving" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Upload Dokumen / Paklaring</label>
                                <input type="hidden" :name="`work_experiences[${idx}][document_path]`" :value="exp.document_path">

                                <template x-if="exp.document_path">
                                    <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-medium text-teal-800 text-[11px]">✓ Dokumen Paklaring Ada</span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="openFilePreview('Paklaring ' + (exp.company_name || ''), '{{ asset('storage') }}/' + exp.document_path, 'Paklaring')" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                            <a :href="'{{ asset('storage') }}/' + exp.document_path" target="_blank" download class="px-2 py-0.5 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                                        </div>
                                    </div>
                                </template>

                                <input type="file" :name="`work_experiences[${idx}][document]`" :id="`edit_exp_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `exp_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-200">

                                <template x-if="filePreviews[`exp_${idx}`]">
                                    <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                        <span class="font-medium truncate text-[11px]" x-text="'File baru: ' + filePreviews[`exp_${idx}`].name"></span>
                                        <button type="button" @click="openFilePreview('Paklaring Baru', filePreviews[`exp_${idx}`].url, filePreviews[`exp_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
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

            <!-- Repeater Keahlian (Skills) Sesuai Revisi #6 -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-screwdriver-wrench text-teal-600"></i>
                            <span>Daftar Keahlian Karyawan (Skills List)</span>
                        </h4>
                        <p class="text-[11px] text-slate-400">Dapat menambahkan beberapa keahlian dengan tingkat kemahiran</p>
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
                                <input type="text" :name="`skills[${idx}][skill_name]`" x-model="skill.skill_name" placeholder="Nama keahlian..." class="w-full p-2 rounded-xl border border-slate-200">
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
                                    <input type="text" :name="`skills[${idx}][notes]`" x-model="skill.notes" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>
                                <button type="button" @click="removeSkill(idx)" class="text-slate-400 hover:text-red-500 p-2 text-xs" title="Hapus">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Ringkasan Teks Lama -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-3 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ringkasan Keahlian Utama (Opsional)</label>
                    <textarea name="skills_summary" rows="2" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('skills_summary', $employee->skills_summary) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alat / Software yang Dikuasai (Opsional)</label>
                    <textarea name="tools_software" rows="2" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">{{ old('tools_software', $employee->tools_software) }}</textarea>
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
                                <input type="text" :name="`certificates[${idx}][certificate_name]`" x-model="cert.certificate_name" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nomor Sertifikat</label>
                                <input type="text" :name="`certificates[${idx}][certificate_number]`" x-model="cert.certificate_number" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Masa Berlaku (s/d)</label>
                                <input type="text" :name="`certificates[${idx}][valid_until]`" x-model="cert.valid_until" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block font-bold text-slate-600 mb-1">Dokumen Sertifikat</label>
                                    <input type="hidden" :name="`certificates[${idx}][document_path]`" :value="cert.document_path">

                                    <template x-if="cert.document_path">
                                        <div class="p-1.5 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mb-1">
                                            <span class="font-medium text-teal-800 text-[10px]">✓ Ada Berkas</span>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="openFilePreview('Sertifikat ' + (cert.certificate_name || ''), '{{ asset('storage') }}/' + cert.document_path, 'Sertifikat')" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                                <a :href="'{{ asset('storage') }}/' + cert.document_path" target="_blank" download class="px-2 py-0.5 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                                            </div>
                                        </div>
                                    </template>

                                    <input type="file" :name="`certificates[${idx}][document]`" :id="`edit_cert_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `cert_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:bg-slate-200">

                                    <template x-if="filePreviews[`cert_${idx}`]">
                                        <div class="p-1.5 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                            <span class="font-medium truncate text-[10px]" x-text="'Baru: ' + filePreviews[`cert_${idx}`].name"></span>
                                            <button type="button" @click="openFilePreview('Sertifikat Baru', filePreviews[`cert_${idx}`].url, filePreviews[`cert_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- NPWP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NPWP / NIK Pajak</label>
                    <input type="text" name="npwp_number" value="{{ old('npwp_number', $employee->npwp_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Dokumen NPWP Sesuai Revisi #8 & #9 -->
                @php $npwpDoc = $employee->getDocument('npwp'); @endphp
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Kartu NPWP</label>
                    @if($npwpDoc)
                    <div class="p-2.5 rounded-xl bg-white border border-teal-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-teal-800">File saat ini:</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $npwpDoc->formatted_size }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs text-slate-700 truncate font-mono">{{ $npwpDoc->original_name }}</span>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" @click="openFilePreview('NPWP', '{{ $npwpDoc->url }}', '{{ $npwpDoc->original_name }}')" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <a href="{{ route('documents.download', $npwpDoc) }}" class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <button type="button" @click="showUpload['npwp'] = !showUpload['npwp']" class="text-teal-700 font-bold hover:underline">
                                <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Ganti File NPWP
                            </button>
                        </div>
                    </div>
                    @endif

                    <div x-show="!{{ $npwpDoc ? 'true' : 'false' }} || showUpload['npwp']" class="space-y-1.5">
                        <input type="file" name="doc_npwp" id="edit_doc_npwp" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_npwp')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                        <template x-if="filePreviews['doc_npwp']">
                            <div class="p-2 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs">
                                <span class="font-medium truncate text-[11px]" x-text="filePreviews['doc_npwp'].name"></span>
                                <button type="button" @click="openFilePreview('NPWP Baru', filePreviews['doc_npwp'].url, filePreviews['doc_npwp'].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PTKP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status PTKP</label>
                    <select name="ptkp_status" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white">
                        <option value="">Pilih Status PTKP</option>
                        @foreach($ptkpOptions as $ptkp)
                        <option value="{{ $ptkp }}" {{ old('ptkp_status', $employee->ptkp_status) === $ptkp ? 'selected' : '' }}>{{ $ptkp }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- BPJS Kesehatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. BPJS Kesehatan</label>
                    <input type="text" name="bpjs_kes_number" value="{{ old('bpjs_kes_number', $employee->bpjs_kes_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Dokumen BPJS Kesehatan -->
                @php $bpjsKesDoc = $employee->getDocument('bpjs_kes'); @endphp
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Kartu BPJS Kesehatan</label>
                    @if($bpjsKesDoc)
                    <div class="p-2.5 rounded-xl bg-white border border-teal-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-teal-800">File saat ini:</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $bpjsKesDoc->formatted_size }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs text-slate-700 truncate font-mono">{{ $bpjsKesDoc->original_name }}</span>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" @click="openFilePreview('BPJS Kesehatan', '{{ $bpjsKesDoc->url }}', '{{ $bpjsKesDoc->original_name }}')" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <a href="{{ route('documents.download', $bpjsKesDoc) }}" class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <button type="button" @click="showUpload['bpjs_kes'] = !showUpload['bpjs_kes']" class="text-teal-700 font-bold hover:underline">
                                <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Ganti File
                            </button>
                        </div>
                    </div>
                    @endif

                    <div x-show="!{{ $bpjsKesDoc ? 'true' : 'false' }} || showUpload['bpjs_kes']" class="space-y-1.5">
                        <input type="file" name="doc_bpjs_kes" id="edit_doc_bpjs_kes" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_bpjs_kes')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                    </div>
                </div>

                <div></div> <!-- Spacer -->

                <!-- BPJS TK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. BPJS Ketenagakerjaan</label>
                    <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number', $employee->bpjs_tk_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Dokumen BPJS TK -->
                @php $bpjsTkDoc = $employee->getDocument('bpjs_tk'); @endphp
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Kartu BPJS TK</label>
                    @if($bpjsTkDoc)
                    <div class="p-2.5 rounded-xl bg-white border border-teal-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-teal-800">File saat ini:</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $bpjsTkDoc->formatted_size }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs text-slate-700 truncate font-mono">{{ $bpjsTkDoc->original_name }}</span>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" @click="openFilePreview('BPJS Ketenagakerjaan', '{{ $bpjsTkDoc->url }}', '{{ $bpjsTkDoc->original_name }}')" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <a href="{{ route('documents.download', $bpjsTkDoc) }}" class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <button type="button" @click="showUpload['bpjs_tk'] = !showUpload['bpjs_tk']" class="text-teal-700 font-bold hover:underline">
                                <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Ganti File
                            </button>
                        </div>
                    </div>
                    @endif

                    <div x-show="!{{ $bpjsTkDoc ? 'true' : 'false' }} || showUpload['bpjs_tk']" class="space-y-1.5">
                        <input type="file" name="doc_bpjs_tk" id="edit_doc_bpjs_tk" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_bpjs_tk')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                    </div>
                </div>

                <div></div> <!-- Spacer -->

                <!-- Data Bank -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Rekening</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Pemilik Rekening</label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $employee->bank_account_holder) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Dokumen Buku Rekening -->
                @php $bankDoc = $employee->getDocument('bank_book'); @endphp
                <div class="sm:col-span-2 lg:col-span-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Buku Rekening (Halaman Depan)</label>
                    @if($bankDoc)
                    <div class="p-2.5 rounded-xl bg-white border border-teal-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-teal-800">File saat ini:</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $bankDoc->formatted_size }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs text-slate-700 truncate font-mono">{{ $bankDoc->original_name }}</span>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" @click="openFilePreview('Buku Rekening', '{{ $bankDoc->url }}', '{{ $bankDoc->original_name }}')" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <a href="{{ route('documents.download', $bankDoc) }}" class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <button type="button" @click="showUpload['bank_book'] = !showUpload['bank_book']" class="text-teal-700 font-bold hover:underline">
                                <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Ganti File Buku Rekening
                            </button>
                        </div>
                    </div>
                    @endif

                    <div x-show="!{{ $bankDoc ? 'true' : 'false' }} || showUpload['bank_book']" class="space-y-1.5">
                        <input type="file" name="doc_bank_book" id="edit_doc_bank_book" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_bank_book')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                    </div>
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
                        <p class="text-[11px] text-slate-400">Dukungan multi SIM dan lisensi operasional pertambangan</p>
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
                                    <input type="text" :name="`licenses[${idx}][license_number]`" x-model="lic.license_number" class="w-full p-2 rounded-xl border border-slate-200">
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
                                    <input type="text" :name="`licenses[${idx}][notes]`" x-model="lic.notes" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-slate-600 mb-1">Dokumen SIM / License</label>
                                    <input type="hidden" :name="`licenses[${idx}][document_path]`" :value="lic.document_path">

                                    <template x-if="lic.document_path">
                                        <div class="p-1.5 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mb-1">
                                            <span class="font-medium text-teal-800 text-[10px]">✓ Ada Berkas Dokumen</span>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="openFilePreview('SIM ' + lic.license_type, '{{ asset('storage') }}/' + lic.document_path, 'SIM')" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                                <a :href="'{{ asset('storage') }}/' + lic.document_path" target="_blank" download class="px-2 py-0.5 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                                            </div>
                                        </div>
                                    </template>

                                    <input type="file" :name="`licenses[${idx}][document]`" :id="`edit_lic_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `lic_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-200">

                                    <template x-if="filePreviews[`lic_${idx}`]">
                                        <div class="p-1.5 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                            <span class="font-medium truncate text-[10px]" x-text="'Baru: ' + filePreviews[`lic_${idx}`].name"></span>
                                            <button type="button" @click="openFilePreview('SIM Baru', filePreviews[`lic_${idx}`].url, filePreviews[`lic_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
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
                    <input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono font-bold text-brand-navy">
                </div>

                <!-- Dokumen ID Card -->
                @php $idCardDoc = $employee->getDocument('id_card'); @endphp
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Upload ID Card</label>
                    @if($idCardDoc)
                    <div class="p-2.5 rounded-xl bg-white border border-teal-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-teal-800">File saat ini:</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $idCardDoc->formatted_size }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs text-slate-700 truncate font-mono">{{ $idCardDoc->original_name }}</span>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" @click="openFilePreview('ID Card', '{{ $idCardDoc->url }}', '{{ $idCardDoc->original_name }}')" class="px-2 py-1 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                <a href="{{ route('documents.download', $idCardDoc) }}" class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <button type="button" @click="showUpload['id_card'] = !showUpload['id_card']" class="text-teal-700 font-bold hover:underline">
                                <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Ganti File
                            </button>
                        </div>
                    </div>
                    @endif

                    <div x-show="!{{ $idCardDoc ? 'true' : 'false' }} || showUpload['id_card']" class="space-y-1.5">
                        <input type="file" name="doc_id_card" id="edit_doc_id_card" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_id_card')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                    </div>
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
                    <input type="text" name="position" value="{{ old('position', $employee->position) }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Departemen Sesuai Revisi #1 -->
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
                        <option value="Lowland" {{ old('work_location', $employee->work_location) === 'Lowland' ? 'selected' : '' }}>Lowland (Timika, Kuala Kencana, Portsite)</option>
                        <option value="Highland" {{ old('work_location', $employee->work_location) === 'Highland' ? 'selected' : '' }}>Highland (Mile 68, Tembagapura, Grasberg)</option>
                    </select>
                </div>

                <!-- Project / Lokasi Fisik -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Project / Penempatan Spesifik</label>
                    <input type="text" name="project_location" value="{{ old('project_location', $employee->project_location) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Tanggal Masuk (Join Date) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Masuk (Join Date) <span class="text-red-500">*</span></label>
                    <input type="date" name="join_date" value="{{ old('join_date', $employee->join_date ? $employee->join_date->format('Y-m-d') : '') }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
                </div>

                <!-- Tanggal Keluar (Leave Date) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tanggal Keluar (Leave Date)
                        <span class="text-[10px] text-slate-400 font-normal">(Wajib jika status Tidak Aktif)</span>
                    </label>
                    <input type="date" name="leave_date" value="{{ old('leave_date', $employee->leave_date ? $employee->leave_date->format('Y-m-d') : '') }}" :required="employmentStatus === 'Tidak Aktif'" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary">
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
                        <p class="text-[11px] text-slate-400">Total riwayat perpanjangan: <strong x-text="contracts.length + ' Kontrak'"></strong></p>
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
                                    <input type="text" :name="`contracts[${idx}][contract_number]`" x-model="c.contract_number" class="w-full p-2 rounded-xl border border-slate-200">
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
                                    <input type="text" :name="`contracts[${idx}][position]`" x-model="c.position" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Departemen</label>
                                    <input type="text" :name="`contracts[${idx}][department]`" x-model="c.department" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Lokasi / Proyek</label>
                                    <input type="text" :name="`contracts[${idx}][project_location]`" x-model="c.project_location" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Keterangan</label>
                                    <input type="text" :name="`contracts[${idx}][notes]`" x-model="c.notes" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Dokumen Kontrak</label>
                                    <input type="hidden" :name="`contracts[${idx}][document_path]`" :value="c.document_path">

                                    <template x-if="c.document_path">
                                        <div class="p-1.5 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mb-1">
                                            <span class="font-medium text-teal-800 text-[10px]">✓ Dokumen Kontrak Ada</span>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="openFilePreview('Kontrak ' + (idx + 1), '{{ asset('storage') }}/' + c.document_path, 'Kontrak')" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                                <a :href="'{{ asset('storage') }}/' + c.document_path" target="_blank" download class="px-2 py-0.5 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                                            </div>
                                        </div>
                                    </template>

                                    <input type="file" :name="`contracts[${idx}][document]`" :id="`edit_cnt_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `cnt_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:bg-slate-200">

                                    <template x-if="filePreviews[`cnt_${idx}`]">
                                        <div class="p-1.5 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                            <span class="font-medium truncate text-[10px]" x-text="'Baru: ' + filePreviews[`cnt_${idx}`].name"></span>
                                            <button type="button" @click="openFilePreview('Kontrak Baru', filePreviews[`cnt_${idx}`].url, filePreviews[`cnt_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
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
        <!-- SEKSI H: GAJI & CUTI (ROLE ACCESS SECURED) -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'gaji'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">

            @if(Auth::user()->isSuperAdmin())
            <!-- H.1 DATA GAJI & HISTORI (SUPER ADMIN ONLY) Sesuai Revisi #2, #3, #4 -->
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-brand-navy flex items-center gap-2">
                        <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                        <span>H.1 DATA GAJI KARYAWAN SAAT INI</span>
                        <span class="text-[10px] bg-purple-100 text-purple-700 font-bold px-2 py-0.5 rounded-full border border-purple-200">Khusus Super Admin</span>
                    </h3>
                    <p class="text-xs text-slate-400">Pembaruan gaji tidak akan menimpa data lama; perubahan dicatat sebagai histori</p>
                </div>

                <!-- Checkbox Force Record Salary Change -->
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-purple-800 bg-purple-50 px-3 py-1.5 rounded-xl border border-purple-200">
                    <input type="checkbox" name="create_salary_history_entry" value="1" class="w-4 h-4 rounded text-purple-600 border-purple-300">
                    <span>Catat Sebagai Kenaikan/Histori Baru</span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Line Grade (Grade A - F) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Line Grade <span class="text-red-500">*</span></label>
                    <select name="line_grade" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-bold text-brand-navy">
                        <option value="">Pilih Grade</option>
                        @foreach($grades as $grd)
                        <option value="{{ $grd }}" {{ old('line_grade', $employee->line_grade) === $grd ? 'selected' : '' }}>{{ $grd }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Level (Level 1 - 5) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Level Jabatan <span class="text-red-500">*</span></label>
                    <select name="salary_level" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-bold text-brand-navy">
                        <option value="">Pilih Level</option>
                        @foreach($levels as $lvl)
                        <option value="{{ $lvl }}" {{ old('salary_level', $employee->salary_level) === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Gaji Pokok -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Gaji Pokok (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" step="1000" min="0" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono font-bold text-slate-800">
                </div>

                <!-- Gaji Per Jam -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Gaji Per Jam (Rp)</label>
                    <input type="number" step="100" min="0" name="hourly_rate" value="{{ old('hourly_rate', $employee->hourly_rate) }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono font-bold text-slate-800">
                </div>

                <!-- Tanggal Mulai Berlaku Perubahan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Berlaku Perubahan</label>
                    <input type="date" name="salary_effective_date" value="{{ date('Y-m-d') }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <!-- Keterangan / Alasan Perubahan Gaji -->
                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Keterangan / Alasan Perubahan Gaji</label>
                    <input type="text" name="salary_change_notes" placeholder="Contoh: Kenaikan berkala tahunan / Promosi jabatan..." class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <!-- Posisi Saat Ini -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Posisi / Kategori Gaji</label>
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

                <!-- Dokumen Slip Gaji -->
                @php $slipDoc = $employee->getDocument('salary_slip'); @endphp
                <div class="sm:col-span-2 lg:col-span-4 p-3.5 rounded-2xl bg-purple-50/50 border border-purple-200 space-y-2">
                    <label class="block text-xs font-bold text-purple-900">Upload Slip Gaji 3 Bulan Terakhir</label>

                    @if($slipDoc)
                    <div class="p-2.5 rounded-xl bg-white border border-purple-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-purple-800">File slip gaji saat ini:</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $slipDoc->formatted_size }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs text-slate-700 truncate font-mono">{{ $slipDoc->original_name }}</span>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" @click="openFilePreview('Slip Gaji', '{{ $slipDoc->url }}', '{{ $slipDoc->original_name }}')" class="px-2 py-1 text-[10px] font-bold text-purple-700 bg-purple-50 rounded">Pratinjau</button>
                                <a href="{{ route('documents.download', $slipDoc) }}" class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-purple-100 flex items-center justify-between text-[11px]">
                            <button type="button" @click="showUpload['salary_slip'] = !showUpload['salary_slip']" class="text-purple-700 font-bold hover:underline">
                                <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Ganti File Slip Gaji
                            </button>
                        </div>
                    </div>
                    @endif

                    <div x-show="!{{ $slipDoc ? 'true' : 'false' }} || showUpload['salary_slip']" class="space-y-1.5">
                        <input type="file" name="doc_salary_slip" id="edit_doc_salary_slip" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_salary_slip')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                    </div>
                </div>
            </div>

            <!-- Repeater Tunjangan Lainnya -->
            <div class="space-y-4 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider">Tunjangan Lainnya (Allowances)</h4>
                        <p class="text-[11px] text-slate-400">Rincian tunjangan bulanan karyawan</p>
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
                                <input type="text" :name="`allowances[${idx}][allowance_name]`" x-model="alw.allowance_name" class="w-full p-2 rounded-xl border border-slate-200">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nominal (Rp/Bulan)</label>
                                <input type="number" step="1000" min="0" :name="`allowances[${idx}][amount]`" x-model="alw.amount" class="w-full p-2 rounded-xl border border-slate-200 font-mono">
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block font-bold text-slate-600 mb-1">Keterangan</label>
                                    <input type="text" :name="`allowances[${idx}][notes]`" x-model="alw.notes" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>
                                <button type="button" @click="removeAllowance(idx)" class="text-slate-400 hover:text-red-500 p-2 text-xs" title="Hapus">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- TABEL HISTORI PERUBAHAN GAJI Sesuai Revisi #4 -->
            <div class="space-y-3 pt-6 border-t-2 border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-brand-navy uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-brand-primary"></i>
                            <span>Histori Riwayat Perubahan Gaji (Gaji Awal &rarr; Gaji Akhir)</span>
                        </h4>
                        <p class="text-[11px] text-slate-400">Seluruh riwayat perjalanan kompensasi tersimpan permanen dan tidak terhapus</p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-slate-100/80 font-bold text-slate-700 uppercase text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">Periode</th>
                                <th class="py-2.5 px-3 text-center">Grade</th>
                                <th class="py-2.5 px-3 text-center">Level</th>
                                <th class="py-2.5 px-3 text-right">Gaji Pokok</th>
                                <th class="py-2.5 px-3 text-right">Gaji / Jam</th>
                                <th class="py-2.5 px-3 text-right">Tunjangan</th>
                                <th class="py-2.5 px-3">Keterangan / Alasan</th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($employee->salaryHistories as $hist)
                            <tr class="{{ $hist->is_current ? 'bg-teal-50/50 font-semibold' : 'hover:bg-slate-50' }}">
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-800">
                                    {{ $hist->period ?: ($hist->start_date ? $hist->start_date->format('Y') : '-') }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded font-bold text-[10px] bg-slate-200/80 text-slate-700">
                                        {{ $hist->line_grade ?: '-' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded font-bold text-[10px] bg-slate-200/80 text-slate-700">
                                        {{ $hist->salary_level ?: '-' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-brand-navy">
                                    Rp {{ number_format($hist->basic_salary, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono">
                                    Rp {{ number_format($hist->hourly_rate, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono">
                                    Rp {{ number_format($hist->allowances_total, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-slate-600 max-w-xs truncate">
                                    {{ $hist->notes ?: '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($hist->is_current)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Gaji Saat Ini
                                    </span>
                                    @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500">
                                        Arsip
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-slate-400">Belum ada catatan histori perubahan gaji.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                        <p class="text-[11px] text-slate-400">Dapat menambahkan catatan cuti berkali-kali</p>
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
                                    <input type="text" :name="`leaves[${idx}][airline]`" x-model="lv.airline" placeholder="Garuda / Batik Air..." class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Jumlah Hari</label>
                                    <input type="number" min="1" :name="`leaves[${idx}][total_days]`" x-model="lv.total_days" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Keterangan / Keperluan</label>
                                    <input type="text" :name="`leaves[${idx}][notes]`" x-model="lv.notes" class="w-full p-2 rounded-xl border border-slate-200">
                                </div>

                                <div class="sm:col-span-2 lg:col-span-5">
                                    <label class="block font-bold text-slate-600 mb-1">Upload Form Cuti / Tiket</label>
                                    <input type="hidden" :name="`leaves[${idx}][document_path]`" :value="lv.document_path">

                                    <template x-if="lv.document_path">
                                        <div class="p-1.5 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mb-1">
                                            <span class="font-medium text-teal-800 text-[10px]">✓ Ada Berkas Tiket/Form</span>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="openFilePreview('Tiket / Cuti ' + (idx + 1), '{{ asset('storage') }}/' + lv.document_path, 'Tiket')" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                                                <a :href="'{{ asset('storage') }}/' + lv.document_path" target="_blank" download class="px-2 py-0.5 text-[10px] font-bold text-slate-600 bg-slate-100 rounded">Unduh</a>
                                            </div>
                                        </div>
                                    </template>

                                    <input type="file" :name="`leaves[${idx}][document]`" :id="`edit_lv_doc_${idx}`" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileInput($event, `lv_${idx}`)" class="w-full text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-slate-200">

                                    <template x-if="filePreviews[`lv_${idx}`]">
                                        <div class="p-1.5 rounded-xl bg-white border border-teal-200 flex items-center justify-between text-xs mt-1">
                                            <span class="font-medium truncate text-[10px]" x-text="'Baru: ' + filePreviews[`lv_${idx}`].name"></span>
                                            <button type="button" @click="openFilePreview('Tiket Baru', filePreviews[`lv_${idx}`].url, filePreviews[`lv_${idx}`].name)" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
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
                @php $empSignDoc = $employee->getDocument('employee_signature'); @endphp
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                        <span class="text-xs font-bold text-brand-navy uppercase">Tanda Tangan Karyawan</span>
                        <span class="text-[10px] text-slate-400">Yang Membuat Pernyataan</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Karyawan</label>
                        <input type="text" name="employee_signature_name" value="{{ old('employee_signature_name', $employee->employee_signature_name) }}" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Berkas TTD Karyawan</label>
                        @if($empSignDoc)
                        <div class="p-2.5 rounded-xl bg-white border border-teal-200 space-y-1.5 mb-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-teal-800">File TTD saat ini:</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $empSignDoc->formatted_size }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-700 truncate font-mono">{{ $empSignDoc->original_name }}</span>
                                <button type="button" @click="openFilePreview('TTD Karyawan', '{{ $empSignDoc->url }}', 'TTD')" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                            </div>
                        </div>
                        @endif

                        <input type="file" name="doc_employee_signature" id="edit_doc_employee_signature" accept=".jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_employee_signature')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
                    </div>
                </div>

                <!-- Tanda Tangan HRD / Pemeriksa -->
                @php $hrdSignDoc = $employee->getDocument('hrd_signature'); @endphp
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                        <span class="text-xs font-bold text-brand-navy uppercase">Tanda Tangan HRD / Pemeriksa</span>
                        <span class="text-[10px] text-teal-600 font-bold">Verifikator</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama HRD / Pemeriksa</label>
                        <input type="text" name="hrd_signature_name" value="{{ old('hrd_signature_name', $employee->hrd_signature_name) }}" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Berkas TTD HRD</label>
                        @if($hrdSignDoc)
                        <div class="p-2.5 rounded-xl bg-white border border-teal-200 space-y-1.5 mb-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-teal-800">File TTD saat ini:</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $hrdSignDoc->formatted_size }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-700 truncate font-mono">{{ $hrdSignDoc->original_name }}</span>
                                <button type="button" @click="openFilePreview('TTD HRD', '{{ $hrdSignDoc->url }}', 'TTD HRD')" class="px-2 py-0.5 text-[10px] font-bold text-brand-primary bg-teal-50 rounded">Pratinjau</button>
                            </div>
                        </div>
                        @endif

                        <input type="file" name="doc_hrd_signature" id="edit_doc_hrd_signature" accept=".jpg,.jpeg,.png" @change="handleFileInput($event, 'doc_hrd_signature')" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:bg-white file:border">
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
                    <span>Simpan Perubahan Biodata</span>
                </button>
            </div>
        </div>

    </form>

    <!-- QUICK ADD DEPARTMENT MODAL Sesuai Revisi #1 -->
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
