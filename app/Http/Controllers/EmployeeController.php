<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\EmergencyContact;
use App\Models\Employee;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeCertificate;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEducation;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query()->with('documents');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('project_location', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($status = $request->input('status')) {
            $query->where('employment_status', $status);
        }

        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }

        if ($ethnicity = $request->input('ethnicity')) {
            $query->where('ethnicity', $ethnicity);
        }

        if ($religion = $request->input('religion')) {
            $query->where('religion', $religion);
        }

        if ($workLocation = $request->input('work_location')) {
            $query->where('work_location', $workLocation);
        }

        if ($department = $request->input('department')) {
            $query->where('department', $department);
        }

        // Filter incomplete documents
        if ($request->boolean('incomplete_docs')) {
            $allEmployees = $query->get();
            $incompleteIds = $allEmployees->filter(function ($emp) {
                return !$emp->isDocumentsComplete();
            })->pluck('id');

            $query = Employee::whereIn('id', $incompleteIds)->with('documents');
        }

        // Sort
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['full_name', 'employee_id', 'position', 'department', 'join_date', 'employment_status', 'created_at'];

        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $employees = $query->paginate(10)->withQueryString();

        // Unique filter options
        $departments = Employee::distinct()->whereNotNull('department')->pluck('department');
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];

        return view('employees.index', compact('employees', 'departments', 'religions'));
    }

    public function create()
    {
        // Suggest ID
        $year = date('Y');
        $lastEmp = Employee::latest('id')->first();
        $nextNum = $lastEmp ? ($lastEmp->id + 1) : 1;
        $suggestedId = sprintf("ABP-%s-%03d", $year, $nextNum);

        $departments = [
            'Mining Operations',
            'Human Resources',
            'Finance & Accounting',
            'Health Safety Environment',
            'Supply Chain & Logistics',
            'Maintenance',
            'Engineering',
            'General Services',
        ];

        $ptkpOptions = ['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'];
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];

        return view('employees.create', compact('suggestedId', 'departments', 'ptkpOptions', 'religions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Data Pribadi
            'full_name' => ['required', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'religion' => ['required', 'string'],
            'ethnicity' => ['required', 'in:Papua,Non Papua'],
            'marital_status' => ['nullable', 'string'],
            'nik' => ['nullable', 'string', 'max:20'],
            'kk_number' => ['nullable', 'string', 'max:20'],
            'ktp_address' => ['nullable', 'string'],
            'domicile_address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],

            // Data Kepegawaian
            'employee_id' => ['required', 'string', 'max:50', 'unique:employees,employee_id'],
            'position' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'employment_status' => ['required', 'in:Aktif,Tidak Aktif'],
            'project_location' => ['nullable', 'string', 'max:255'],
            'work_location' => ['required', 'in:Highland,Lowland'],
            'join_date' => ['required', 'date'],
            'leave_date' => ['nullable', 'date', 'after_or_equal:join_date'],

            // Gaji & Cuti
            'current_position' => ['nullable', 'string', 'max:255'],
            'previous_work_years' => ['nullable', 'integer', 'min:0'],
            'previous_work_months' => ['nullable', 'integer', 'min:0', 'max:11'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'last_leave_date' => ['nullable', 'date'],

            // Keahlian & Administrasi
            'skills_summary' => ['nullable', 'string'],
            'tools_software' => ['nullable', 'string'],
            'npwp_number' => ['nullable', 'string', 'max:50'],
            'ptkp_status' => ['nullable', 'string', 'max:20'],
            'bpjs_kes_number' => ['nullable', 'string', 'max:50'],
            'bpjs_tk_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'employee_signature_name' => ['nullable', 'string', 'max:255'],
            'hrd_signature_name' => ['nullable', 'string', 'max:255'],

            // Upload files validation
            'doc_ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_kk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_id_card' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_npwp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_bpjs_kes' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_bpjs_tk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_bank_book' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_salary_slip' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_leave_form' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_employee_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'doc_hrd_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        DB::beginTransaction();
        try {
            // Handle Photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('employees/photos', 'public');
                $validated['photo'] = $photoPath;
            }

            // Create Employee
            $employee = Employee::create($validated);

            // Save Document Files
            $docTypes = [
                'doc_ktp' => 'ktp',
                'doc_kk' => 'kk',
                'doc_id_card' => 'id_card',
                'doc_npwp' => 'npwp',
                'doc_bpjs_kes' => 'bpjs_kes',
                'doc_bpjs_tk' => 'bpjs_tk',
                'doc_bank_book' => 'bank_book',
                'doc_salary_slip' => 'salary_slip',
                'doc_leave_form' => 'leave_form',
                'doc_employee_signature' => 'employee_signature',
                'doc_hrd_signature' => 'hrd_signature',
            ];

            foreach ($docTypes as $inputKey => $type) {
                if ($request->hasFile($inputKey)) {
                    $file = $request->file($inputKey);
                    $path = $file->store('employees/documents/' . $type, 'public');
                    EmployeeDocument::create([
                        'employee_id' => $employee->id,
                        'document_type' => $type,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            // If photo was uploaded, also register in employee_documents
            if (!empty($validated['photo'])) {
                EmployeeDocument::create([
                    'employee_id' => $employee->id,
                    'document_type' => 'photo',
                    'file_path' => $validated['photo'],
                    'original_name' => $request->file('photo')->getClientOriginalName(),
                    'file_size' => $request->file('photo')->getSize(),
                    'mime_type' => $request->file('photo')->getClientMimeType(),
                ]);
            }

            // Emergency Contacts
            if ($request->filled('emergency_contact_name')) {
                EmergencyContact::create([
                    'employee_id' => $employee->id,
                    'name' => $request->input('emergency_contact_name'),
                    'relationship' => $request->input('emergency_contact_relationship'),
                    'phone' => $request->input('emergency_contact_phone'),
                    'alternative_phone' => $request->input('emergency_contact_alt_phone'),
                    'address' => $request->input('emergency_contact_address'),
                ]);
            }

            // Educations (Repeater)
            if ($request->has('educations') && is_array($request->input('educations'))) {
                foreach ($request->input('educations') as $idx => $edu) {
                    if (!empty($edu['institution_name'])) {
                        $docPath = null;
                        if ($request->hasFile("educations.{$idx}.document")) {
                            $f = $request->file("educations.{$idx}.document");
                            $docPath = $f->store('employees/documents/education', 'public');
                            EmployeeDocument::create([
                                'employee_id' => $employee->id,
                                'document_type' => 'ijazah_' . strtolower($edu['level'] ?? 'edu'),
                                'file_path' => $docPath,
                                'original_name' => $f->getClientOriginalName(),
                                'file_size' => $f->getSize(),
                                'mime_type' => $f->getClientMimeType(),
                            ]);
                        }

                        EmployeeEducation::create([
                            'employee_id' => $employee->id,
                            'level' => $edu['level'] ?? 'SD',
                            'institution_name' => $edu['institution_name'],
                            'major' => $edu['major'] ?? null,
                            'graduation_year' => $edu['graduation_year'] ?? null,
                            'certificate_number' => $edu['certificate_number'] ?? null,
                            'document_path' => $docPath,
                        ]);
                    }
                }
            }

            // Work Experiences (Repeater)
            if ($request->has('work_experiences') && is_array($request->input('work_experiences'))) {
                foreach ($request->input('work_experiences') as $idx => $exp) {
                    if (!empty($exp['company_name'])) {
                        $docPath = null;
                        if ($request->hasFile("work_experiences.{$idx}.document")) {
                            $f = $request->file("work_experiences.{$idx}.document");
                            $docPath = $f->store('employees/documents/experience', 'public');
                            EmployeeDocument::create([
                                'employee_id' => $employee->id,
                                'document_type' => 'pengalaman_kerja',
                                'file_path' => $docPath,
                                'original_name' => $f->getClientOriginalName(),
                                'file_size' => $f->getSize(),
                                'mime_type' => $f->getClientMimeType(),
                            ]);
                        }

                        WorkExperience::create([
                            'employee_id' => $employee->id,
                            'company_name' => $exp['company_name'],
                            'position' => $exp['position'] ?? null,
                            'period' => $exp['period'] ?? null,
                            'reason_for_leaving' => $exp['reason_for_leaving'] ?? null,
                            'document_path' => $docPath,
                        ]);
                    }
                }
            }

            // Certificates (Repeater)
            if ($request->has('certificates') && is_array($request->input('certificates'))) {
                foreach ($request->input('certificates') as $idx => $cert) {
                    if (!empty($cert['certificate_name'])) {
                        $docPath = null;
                        if ($request->hasFile("certificates.{$idx}.document")) {
                            $f = $request->file("certificates.{$idx}.document");
                            $docPath = $f->store('employees/documents/certificate', 'public');
                            EmployeeDocument::create([
                                'employee_id' => $employee->id,
                                'document_type' => 'sertifikat',
                                'file_path' => $docPath,
                                'original_name' => $f->getClientOriginalName(),
                                'file_size' => $f->getSize(),
                                'mime_type' => $f->getClientMimeType(),
                            ]);
                        }

                        EmployeeCertificate::create([
                            'employee_id' => $employee->id,
                            'certificate_name' => $cert['certificate_name'],
                            'certificate_number' => $cert['certificate_number'] ?? null,
                            'valid_until' => $cert['valid_until'] ?? null,
                            'document_path' => $docPath,
                        ]);
                    }
                }
            }

            // Allowances (Repeater)
            if ($request->has('allowances') && is_array($request->input('allowances'))) {
                foreach ($request->input('allowances') as $allowance) {
                    if (!empty($allowance['allowance_name'])) {
                        EmployeeAllowance::create([
                            'employee_id' => $employee->id,
                            'allowance_name' => $allowance['allowance_name'],
                            'amount' => $allowance['amount'] ?? 0,
                            'notes' => $allowance['notes'] ?? null,
                        ]);
                    }
                }
            }

            ActivityLog::record(
                'tambah_karyawan',
                "Menambahkan data karyawan baru: {$employee->full_name} ({$employee->employee_id})",
                'Employee',
                $employee->id
            );

            DB::commit();

            return redirect()->route('employees.show', $employee)
                ->with('success', "Data karyawan {$employee->full_name} berhasil ditambahkan!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'emergencyContacts',
            'educations',
            'workExperiences',
            'certificates',
            'allowances',
            'documents',
        ]);

        return view('employees.show', compact('employee'));
    }

    public function print(Employee $employee)
    {
        $employee->load([
            'emergencyContacts',
            'educations',
            'workExperiences',
            'certificates',
            'allowances',
            'documents',
        ]);

        return view('employees.print', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load([
            'emergencyContacts',
            'educations',
            'workExperiences',
            'certificates',
            'allowances',
            'documents',
        ]);

        $departments = [
            'Mining Operations',
            'Human Resources',
            'Finance & Accounting',
            'Health Safety Environment',
            'Supply Chain & Logistics',
            'Maintenance',
            'Engineering',
            'General Services',
        ];

        $ptkpOptions = ['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'];
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];

        return view('employees.edit', compact('employee', 'departments', 'ptkpOptions', 'religions'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'religion' => ['required', 'string'],
            'ethnicity' => ['required', 'in:Papua,Non Papua'],
            'marital_status' => ['nullable', 'string'],
            'nik' => ['nullable', 'string', 'max:20'],
            'kk_number' => ['nullable', 'string', 'max:20'],
            'ktp_address' => ['nullable', 'string'],
            'domicile_address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],

            'employee_id' => ['required', 'string', 'max:50', 'unique:employees,employee_id,' . $employee->id],
            'position' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'employment_status' => ['required', 'in:Aktif,Tidak Aktif'],
            'project_location' => ['nullable', 'string', 'max:255'],
            'work_location' => ['required', 'in:Highland,Lowland'],
            'join_date' => ['required', 'date'],
            'leave_date' => ['nullable', 'date', 'after_or_equal:join_date'],

            'current_position' => ['nullable', 'string', 'max:255'],
            'previous_work_years' => ['nullable', 'integer', 'min:0'],
            'previous_work_months' => ['nullable', 'integer', 'min:0', 'max:11'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'last_leave_date' => ['nullable', 'date'],

            'skills_summary' => ['nullable', 'string'],
            'tools_software' => ['nullable', 'string'],
            'npwp_number' => ['nullable', 'string', 'max:50'],
            'ptkp_status' => ['nullable', 'string', 'max:20'],
            'bpjs_kes_number' => ['nullable', 'string', 'max:50'],
            'bpjs_tk_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'employee_signature_name' => ['nullable', 'string', 'max:255'],
            'hrd_signature_name' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('photo')) {
                if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                    Storage::disk('public')->delete($employee->photo);
                }
                $photoPath = $request->file('photo')->store('employees/photos', 'public');
                $validated['photo'] = $photoPath;

                // Update or create document entry for photo
                EmployeeDocument::updateOrCreate(
                    ['employee_id' => $employee->id, 'document_type' => 'photo'],
                    [
                        'file_path' => $photoPath,
                        'original_name' => $request->file('photo')->getClientOriginalName(),
                        'file_size' => $request->file('photo')->getSize(),
                        'mime_type' => $request->file('photo')->getClientMimeType(),
                    ]
                );
            }

            $employee->update($validated);

            // Documents upload
            $docTypes = [
                'doc_ktp' => 'ktp',
                'doc_kk' => 'kk',
                'doc_id_card' => 'id_card',
                'doc_npwp' => 'npwp',
                'doc_bpjs_kes' => 'bpjs_kes',
                'doc_bpjs_tk' => 'bpjs_tk',
                'doc_bank_book' => 'bank_book',
                'doc_salary_slip' => 'salary_slip',
                'doc_leave_form' => 'leave_form',
                'doc_employee_signature' => 'employee_signature',
                'doc_hrd_signature' => 'hrd_signature',
            ];

            foreach ($docTypes as $inputKey => $type) {
                if ($request->hasFile($inputKey)) {
                    $file = $request->file($inputKey);
                    $path = $file->store('employees/documents/' . $type, 'public');

                    // Delete old file if exists
                    $oldDoc = $employee->documents()->where('document_type', $type)->first();
                    if ($oldDoc && Storage::disk('public')->exists($oldDoc->file_path)) {
                        Storage::disk('public')->delete($oldDoc->file_path);
                    }

                    EmployeeDocument::updateOrCreate(
                        ['employee_id' => $employee->id, 'document_type' => $type],
                        [
                            'file_path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getClientMimeType(),
                        ]
                    );
                }
            }

            // Update Emergency Contact
            if ($request->filled('emergency_contact_name')) {
                EmergencyContact::updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'name' => $request->input('emergency_contact_name'),
                        'relationship' => $request->input('emergency_contact_relationship'),
                        'phone' => $request->input('emergency_contact_phone'),
                        'alternative_phone' => $request->input('emergency_contact_alt_phone'),
                        'address' => $request->input('emergency_contact_address'),
                    ]
                );
            }

            // Sync educations
            if ($request->has('educations')) {
                $employee->educations()->delete();
                foreach ($request->input('educations') as $edu) {
                    if (!empty($edu['institution_name'])) {
                        EmployeeEducation::create([
                            'employee_id' => $employee->id,
                            'level' => $edu['level'] ?? 'SD',
                            'institution_name' => $edu['institution_name'],
                            'major' => $edu['major'] ?? null,
                            'graduation_year' => $edu['graduation_year'] ?? null,
                            'certificate_number' => $edu['certificate_number'] ?? null,
                            'document_path' => $edu['document_path'] ?? null,
                        ]);
                    }
                }
            }

            // Sync work experiences
            if ($request->has('work_experiences')) {
                $employee->workExperiences()->delete();
                foreach ($request->input('work_experiences') as $exp) {
                    if (!empty($exp['company_name'])) {
                        WorkExperience::create([
                            'employee_id' => $employee->id,
                            'company_name' => $exp['company_name'],
                            'position' => $exp['position'] ?? null,
                            'period' => $exp['period'] ?? null,
                            'reason_for_leaving' => $exp['reason_for_leaving'] ?? null,
                            'document_path' => $exp['document_path'] ?? null,
                        ]);
                    }
                }
            }

            // Sync certificates
            if ($request->has('certificates')) {
                $employee->certificates()->delete();
                foreach ($request->input('certificates') as $cert) {
                    if (!empty($cert['certificate_name'])) {
                        EmployeeCertificate::create([
                            'employee_id' => $employee->id,
                            'certificate_name' => $cert['certificate_name'],
                            'certificate_number' => $cert['certificate_number'] ?? null,
                            'valid_until' => $cert['valid_until'] ?? null,
                            'document_path' => $cert['document_path'] ?? null,
                        ]);
                    }
                }
            }

            // Sync allowances
            if ($request->has('allowances')) {
                $employee->allowances()->delete();
                foreach ($request->input('allowances') as $allowance) {
                    if (!empty($allowance['allowance_name'])) {
                        EmployeeAllowance::create([
                            'employee_id' => $employee->id,
                            'allowance_name' => $allowance['allowance_name'],
                            'amount' => $allowance['amount'] ?? 0,
                            'notes' => $allowance['notes'] ?? null,
                        ]);
                    }
                }
            }

            ActivityLog::record(
                'edit_karyawan',
                "Memperbarui data karyawan: {$employee->full_name} ({$employee->employee_id})",
                'Employee',
                $employee->id
            );

            DB::commit();

            return redirect()->route('employees.show', $employee)
                ->with('success', "Data karyawan {$employee->full_name} berhasil diperbarui!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Employee $employee)
    {
        // Only Super Admin can delete
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang memiliki hak akses untuk menghapus data karyawan.');
        }

        $empName = $employee->full_name;
        $empId = $employee->employee_id;

        // Delete photo & documents from storage
        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }

        foreach ($employee->documents as $doc) {
            if (Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
        }

        $employee->delete();

        ActivityLog::record(
            'hapus_karyawan',
            "Menghapus data karyawan: {$empName} ({$empId})",
            'Employee'
        );

        return redirect()->route('employees.index')
            ->with('success', "Data karyawan {$empName} ({$empId}) berhasil dihapus!");
    }

    public function updateStatus(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employment_status' => ['required', 'in:Aktif,Tidak Aktif'],
            'leave_date' => ['nullable', 'date'],
        ]);

        $employee->update($validated);

        ActivityLog::record(
            'ubah_status',
            "Mengubah status karyawan {$employee->full_name} menjadi {$validated['employment_status']}",
            'Employee',
            $employee->id
        );

        return back()->with('success', "Status karyawan {$employee->full_name} berhasil diubah.");
    }

    public function export(Request $request)
    {
        $query = Employee::query();

        // Apply same filters as index
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('employment_status', $status);
        }
        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }
        if ($ethnicity = $request->input('ethnicity')) {
            $query->where('ethnicity', $ethnicity);
        }
        if ($religion = $request->input('religion')) {
            $query->where('religion', $religion);
        }
        if ($workLocation = $request->input('work_location')) {
            $query->where('work_location', $workLocation);
        }
        if ($department = $request->input('department')) {
            $query->where('department', $department);
        }

        $format = $request->input('format', 'csv');
        $employees = $query->orderBy('full_name')->get();

        ActivityLog::record('export_data', "Mengekspor data karyawan (format: " . strtoupper($format) . ", jumlah: " . $employees->count() . ")");

        if ($format === 'pdf') {
            return view('employees.export_pdf', compact('employees'));
        }

        // CSV / Excel export
        $filename = "Data_Karyawan_PT_ABP_" . date('Ymd_His') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($employees) {
            $output = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, [
                'ID Karyawan',
                'Nama Lengkap',
                'Jenis Kelamin',
                'Suku',
                'Agama',
                'Tempat Lahir',
                'Tanggal Lahir',
                'NIK / KTP',
                'No. KK',
                'No. HP / WA',
                'Email',
                'Jabatan',
                'Departemen',
                'Project / Lokasi',
                'Lokasi Kerja',
                'Status Kepegawaian',
                'Tanggal Masuk',
                'Tanggal Keluar',
                'Gaji Pokok',
                'NPWP',
                'BPJS Kesehatan',
                'BPJS TK',
                'Bank',
                'No Rekening',
            ], ';');

            foreach ($employees as $emp) {
                fputcsv($output, [
                    $emp->employee_id,
                    $emp->full_name,
                    $emp->gender,
                    $emp->ethnicity,
                    $emp->religion,
                    $emp->birth_place,
                    $emp->birth_date ? $emp->birth_date->format('d/m/Y') : '',
                    "'" . $emp->nik,
                    "'" . $emp->kk_number,
                    $emp->phone,
                    $emp->email,
                    $emp->position,
                    $emp->department,
                    $emp->project_location,
                    $emp->work_location,
                    $emp->employment_status,
                    $emp->join_date ? $emp->join_date->format('d/m/Y') : '',
                    $emp->leave_date ? $emp->leave_date->format('d/m/Y') : '',
                    $emp->basic_salary,
                    $emp->npwp_number,
                    $emp->bpjs_kes_number,
                    $emp->bpjs_tk_number,
                    $emp->bank_name,
                    "'" . $emp->bank_account_number,
                ], ';');
            }

            fclose($output);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
