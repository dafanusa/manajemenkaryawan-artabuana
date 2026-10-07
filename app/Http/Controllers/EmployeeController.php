<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\EmergencyContact;
use App\Models\Employee;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeCertificate;
use App\Models\EmployeeContract;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEducation;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLicense;
use App\Models\EmployeeSalaryHistory;
use App\Models\EmployeeSkill;
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
        $query = Employee::query()->with(['documents', 'departmentRelation']);

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

        // Unique filter options from database
        $departments = Department::where('is_active', true)->orderBy('name')->pluck('name');
        if ($departments->isEmpty()) {
            $departments = Employee::distinct()->whereNotNull('department')->pluck('department');
        }
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

        // Fetch dynamic departments from DB
        $departments = Department::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
        if (empty($departments)) {
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
        }

        $departmentList = Department::where('is_active', true)->orderBy('name')->get();
        $ptkpOptions = ['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'];
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];
        $grades = ['Grade A', 'Grade B', 'Grade C', 'Grade D', 'Grade E', 'Grade F'];
        $levels = ['Level 1', 'Level 2', 'Level 3', 'Level 4', 'Level 5'];

        return view('employees.create', compact('suggestedId', 'departments', 'departmentList', 'ptkpOptions', 'religions', 'grades', 'levels'));
    }

    public function store(Request $request)
    {
        $isSuperAdmin = Auth::user()->isSuperAdmin();

        $rules = [
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
            'contract_type' => ['nullable', 'string', 'max:50'],
            'project_location' => ['nullable', 'string', 'max:255'],
            'work_location' => ['required', 'in:Highland,Lowland'],
            'join_date' => ['required', 'date'],
            'leave_date' => ['nullable', 'date', 'after_or_equal:join_date'],

            // Kepegawaian detail & administrasi
            'current_position' => ['nullable', 'string', 'max:255'],
            'previous_work_years' => ['nullable', 'integer', 'min:0'],
            'previous_work_months' => ['nullable', 'integer', 'min:0', 'max:11'],
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
            'doc_employee_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'doc_hrd_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],

            // Repeaters
            'educations' => ['nullable', 'array'],
            'work_experiences' => ['nullable', 'array'],
            'certificates' => ['nullable', 'array'],
            'leaves' => ['nullable', 'array'],
            'skills' => ['nullable', 'array'],
            'contracts' => ['nullable', 'array'],
            'licenses' => ['nullable', 'array'],
        ];

        // Gaji fields: ONLY Super Admin can validate and set salary data
        if ($isSuperAdmin) {
            $rules['line_grade'] = ['nullable', 'in:Grade A,Grade B,Grade C,Grade D,Grade E,Grade F'];
            $rules['salary_level'] = ['nullable', 'in:Level 1,Level 2,Level 3,Level 4,Level 5'];
            $rules['basic_salary'] = ['nullable', 'numeric', 'min:0'];
            $rules['hourly_rate'] = ['nullable', 'numeric', 'min:0'];
            $rules['allowances'] = ['nullable', 'array'];
            $rules['doc_salary_slip'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'];
        }

        $validated = $request->validate($rules);

        // If not super admin, strictly ensure salary fields are not present or set to 0
        if (!$isSuperAdmin) {
            unset($validated['basic_salary'], $validated['hourly_rate'], $validated['line_grade'], $validated['salary_level'], $validated['allowances'], $validated['doc_salary_slip']);
            $validated['basic_salary'] = 0;
            $validated['hourly_rate'] = 0;
            $validated['line_grade'] = null;
            $validated['salary_level'] = null;
        }

        DB::beginTransaction();
        try {
            // Find or create department in DB
            $deptName = trim($validated['department']);
            $dept = Department::firstOrCreate(['name' => $deptName]);
            $validated['department_id'] = $dept->id;
            $validated['department'] = $dept->name;

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
                'doc_employee_signature' => 'employee_signature',
                'doc_hrd_signature' => 'hrd_signature',
            ];

            if ($isSuperAdmin) {
                $docTypes['doc_salary_slip'] = 'salary_slip';
            }

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

            // Skills (Repeater)
            if ($request->has('skills') && is_array($request->input('skills'))) {
                foreach ($request->input('skills') as $skillData) {
                    if (!empty($skillData['skill_name'])) {
                        EmployeeSkill::create([
                            'employee_id' => $employee->id,
                            'skill_name' => trim($skillData['skill_name']),
                            'proficiency_level' => $skillData['proficiency_level'] ?? null,
                            'notes' => $skillData['notes'] ?? null,
                        ]);
                    }
                }
            }

            // Contracts PKWT (Repeater)
            if ($request->has('contracts') && is_array($request->input('contracts'))) {
                foreach ($request->input('contracts') as $idx => $cData) {
                    if (!empty($cData['contract_number']) || !empty($cData['start_date'])) {
                        $docPath = null;
                        if ($request->hasFile("contracts.{$idx}.document")) {
                            $f = $request->file("contracts.{$idx}.document");
                            $docPath = $f->store('employees/documents/contracts', 'public');
                            EmployeeDocument::create([
                                'employee_id' => $employee->id,
                                'document_type' => 'kontrak_kerja',
                                'file_path' => $docPath,
                                'original_name' => $f->getClientOriginalName(),
                                'file_size' => $f->getSize(),
                                'mime_type' => $f->getClientMimeType(),
                            ]);
                        }

                        EmployeeContract::create([
                            'employee_id' => $employee->id,
                            'contract_sequence' => $idx + 1,
                            'contract_number' => $cData['contract_number'] ?? ('PKWT/' . date('Y') . '/' . sprintf('%03d', $idx + 1)),
                            'start_date' => $cData['start_date'] ?? $employee->join_date,
                            'end_date' => $cData['end_date'] ?? date('Y-m-d', strtotime($employee->join_date . ' + 1 year')),
                            'position' => $cData['position'] ?? $employee->position,
                            'department' => $cData['department'] ?? $employee->department,
                            'project_location' => $cData['project_location'] ?? $employee->project_location,
                            'notes' => $cData['notes'] ?? null,
                            'document_path' => $docPath,
                            'status' => $cData['status'] ?? 'Aktif',
                        ]);
                    }
                }
            }

            // SIM / Licenses (Repeater)
            if ($request->has('licenses') && is_array($request->input('licenses'))) {
                foreach ($request->input('licenses') as $idx => $licData) {
                    if (!empty($licData['license_type'])) {
                        $docPath = null;
                        if ($request->hasFile("licenses.{$idx}.document")) {
                            $f = $request->file("licenses.{$idx}.document");
                            $docPath = $f->store('employees/documents/licenses', 'public');
                            EmployeeDocument::create([
                                'employee_id' => $employee->id,
                                'document_type' => 'sim_license',
                                'file_path' => $docPath,
                                'original_name' => $f->getClientOriginalName(),
                                'file_size' => $f->getSize(),
                                'mime_type' => $f->getClientMimeType(),
                            ]);
                        }

                        EmployeeLicense::create([
                            'employee_id' => $employee->id,
                            'license_type' => $licData['license_type'],
                            'license_number' => $licData['license_number'] ?? '-',
                            'issue_date' => $licData['issue_date'] ?? null,
                            'expiry_date' => $licData['expiry_date'] ?? null,
                            'notes' => $licData['notes'] ?? null,
                            'document_path' => $docPath,
                        ]);
                    }
                }
            }

            // Leaves (Repeater)
            if ($request->has('leaves') && is_array($request->input('leaves'))) {
                foreach ($request->input('leaves') as $idx => $leaveData) {
                    if (!empty($leaveData['leave_start_date'])) {
                        $docPath = null;
                        if ($request->hasFile("leaves.{$idx}.document")) {
                            $f = $request->file("leaves.{$idx}.document");
                            $docPath = $f->store('employees/documents/leaves', 'public');
                            EmployeeDocument::create([
                                'employee_id' => $employee->id,
                                'document_type' => 'leave_form',
                                'file_path' => $docPath,
                                'original_name' => $f->getClientOriginalName(),
                                'file_size' => $f->getSize(),
                                'mime_type' => $f->getClientMimeType(),
                            ]);
                        }

                        $startDate = $leaveData['leave_start_date'];
                        $endDate = $leaveData['leave_end_date'] ?? $startDate;
                        $days = $leaveData['total_days'] ?? 1;

                        EmployeeLeave::create([
                            'employee_id' => $employee->id,
                            'leave_start_date' => $startDate,
                            'leave_end_date' => $endDate,
                            'airline' => $leaveData['airline'] ?? null,
                            'total_days' => $days,
                            'notes' => $leaveData['notes'] ?? null,
                            'document_path' => $docPath,
                        ]);

                        // Keep employee last_leave_date updated
                        $employee->update(['last_leave_date' => $startDate]);
                    }
                }
            }

            // Allowances & Salary History (Super Admin Only)
            if ($isSuperAdmin) {
                $totalAllowances = 0;
                $allowanceDetails = [];
                if ($request->has('allowances') && is_array($request->input('allowances'))) {
                    foreach ($request->input('allowances') as $allowance) {
                        if (!empty($allowance['allowance_name'])) {
                            $amt = (float)($allowance['amount'] ?? 0);
                            $totalAllowances += $amt;
                            $allowanceDetails[] = [
                                'allowance_name' => $allowance['allowance_name'],
                                'amount' => $amt,
                                'notes' => $allowance['notes'] ?? null,
                            ];
                            EmployeeAllowance::create([
                                'employee_id' => $employee->id,
                                'allowance_name' => $allowance['allowance_name'],
                                'amount' => $amt,
                                'notes' => $allowance['notes'] ?? null,
                            ]);
                        }
                    }
                }

                if (!empty($validated['basic_salary']) || !empty($validated['line_grade'])) {
                    $joinYear = $employee->join_date ? $employee->join_date->format('Y') : date('Y');
                    EmployeeSalaryHistory::create([
                        'employee_id' => $employee->id,
                        'line_grade' => $validated['line_grade'] ?? 'Grade A',
                        'salary_level' => $validated['salary_level'] ?? 'Level 1',
                        'basic_salary' => $validated['basic_salary'] ?? 0,
                        'hourly_rate' => $validated['hourly_rate'] ?? 0,
                        'allowances_total' => $totalAllowances,
                        'allowances_detail' => $allowanceDetails,
                        'start_date' => $employee->join_date ?? now(),
                        'end_date' => null,
                        'period' => $joinYear . '–Sekarang',
                        'notes' => 'Gaji Awal Masuk',
                        'is_current' => true,
                        'created_by' => Auth::id(),
                    ]);
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
        $relations = [
            'emergencyContacts',
            'educations',
            'workExperiences',
            'certificates',
            'departmentRelation',
            'leaves',
            'skills',
            'contracts',
            'licenses',
            'documents',
        ];

        $isSuperAdmin = Auth::user()->isSuperAdmin();

        if ($isSuperAdmin) {
            $relations[] = 'salaryHistories';
            $relations[] = 'allowances';
        }

        $employee->load($relations);

        // If not super admin, protect sensitive salary fields at backend level
        if (!$isSuperAdmin) {
            $employee->makeHidden(['basic_salary', 'hourly_rate', 'line_grade', 'salary_level']);
            $employee->basic_salary = null;
            $employee->hourly_rate = null;
            $employee->line_grade = null;
            $employee->salary_level = null;
        }

        return view('employees.show', compact('employee', 'isSuperAdmin'));
    }

    public function print(Employee $employee)
    {
        $isSuperAdmin = Auth::user()->isSuperAdmin();

        $relations = [
            'emergencyContacts',
            'educations',
            'workExperiences',
            'certificates',
            'departmentRelation',
            'leaves',
            'skills',
            'contracts',
            'licenses',
            'documents',
        ];

        if ($isSuperAdmin) {
            $relations[] = 'salaryHistories';
            $relations[] = 'allowances';
        }

        $employee->load($relations);

        if (!$isSuperAdmin) {
            $employee->makeHidden(['basic_salary', 'hourly_rate', 'line_grade', 'salary_level']);
            $employee->basic_salary = null;
            $employee->hourly_rate = null;
            $employee->line_grade = null;
            $employee->salary_level = null;
        }

        return view('employees.print', compact('employee', 'isSuperAdmin'));
    }

    public function edit(Employee $employee)
    {
        $isSuperAdmin = Auth::user()->isSuperAdmin();

        $relations = [
            'emergencyContacts',
            'educations',
            'workExperiences',
            'certificates',
            'departmentRelation',
            'leaves',
            'skills',
            'contracts',
            'licenses',
            'documents',
        ];

        if ($isSuperAdmin) {
            $relations[] = 'salaryHistories';
            $relations[] = 'allowances';
        }

        $employee->load($relations);

        if (!$isSuperAdmin) {
            $employee->makeHidden(['basic_salary', 'hourly_rate', 'line_grade', 'salary_level']);
            $employee->basic_salary = null;
            $employee->hourly_rate = null;
            $employee->line_grade = null;
            $employee->salary_level = null;
        }

        $departments = Department::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
        if (empty($departments)) {
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
        }

        $departmentList = Department::where('is_active', true)->orderBy('name')->get();
        $ptkpOptions = ['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'];
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];
        $grades = ['Grade A', 'Grade B', 'Grade C', 'Grade D', 'Grade E', 'Grade F'];
        $levels = ['Level 1', 'Level 2', 'Level 3', 'Level 4', 'Level 5'];

        return view('employees.edit', compact('employee', 'departments', 'departmentList', 'ptkpOptions', 'religions', 'grades', 'levels', 'isSuperAdmin'));
    }

    public function update(Request $request, Employee $employee)
    {
        $isSuperAdmin = Auth::user()->isSuperAdmin();

        $rules = [
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
            'contract_type' => ['nullable', 'string', 'max:50'],
            'project_location' => ['nullable', 'string', 'max:255'],
            'work_location' => ['required', 'in:Highland,Lowland'],
            'join_date' => ['required', 'date'],
            'leave_date' => ['nullable', 'date', 'after_or_equal:join_date'],

            'current_position' => ['nullable', 'string', 'max:255'],
            'previous_work_years' => ['nullable', 'integer', 'min:0'],
            'previous_work_months' => ['nullable', 'integer', 'min:0', 'max:11'],
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

            'doc_ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_kk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_id_card' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_npwp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_bpjs_kes' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_bpjs_tk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_bank_book' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_employee_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'doc_hrd_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],

            'educations' => ['nullable', 'array'],
            'work_experiences' => ['nullable', 'array'],
            'certificates' => ['nullable', 'array'],
            'leaves' => ['nullable', 'array'],
            'skills' => ['nullable', 'array'],
            'contracts' => ['nullable', 'array'],
            'licenses' => ['nullable', 'array'],
        ];

        if ($isSuperAdmin) {
            $rules['line_grade'] = ['nullable', 'in:Grade A,Grade B,Grade C,Grade D,Grade E,Grade F'];
            $rules['salary_level'] = ['nullable', 'in:Level 1,Level 2,Level 3,Level 4,Level 5'];
            $rules['basic_salary'] = ['nullable', 'numeric', 'min:0'];
            $rules['hourly_rate'] = ['nullable', 'numeric', 'min:0'];
            $rules['allowances'] = ['nullable', 'array'];
            $rules['doc_salary_slip'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'];
            $rules['salary_effective_date'] = ['nullable', 'date'];
            $rules['salary_change_notes'] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);

        // If not super admin, prevent tampering with salary
        if (!$isSuperAdmin) {
            unset(
                $validated['basic_salary'],
                $validated['hourly_rate'],
                $validated['line_grade'],
                $validated['salary_level'],
                $validated['allowances'],
                $validated['doc_salary_slip'],
                $validated['salary_effective_date'],
                $validated['salary_change_notes']
            );
        }

        DB::beginTransaction();
        try {
            // Find or create department
            $deptName = trim($validated['department']);
            $dept = Department::firstOrCreate(['name' => $deptName]);
            $validated['department_id'] = $dept->id;
            $validated['department'] = $dept->name;

            // Handle Photo upload & file replacement
            if ($request->hasFile('photo')) {
                if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                    Storage::disk('public')->delete($employee->photo);
                }
                $photoPath = $request->file('photo')->store('employees/photos', 'public');
                $validated['photo'] = $photoPath;

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

            // Documents upload with safe file replacement
            $docTypes = [
                'doc_ktp' => 'ktp',
                'doc_kk' => 'kk',
                'doc_id_card' => 'id_card',
                'doc_npwp' => 'npwp',
                'doc_bpjs_kes' => 'bpjs_kes',
                'doc_bpjs_tk' => 'bpjs_tk',
                'doc_bank_book' => 'bank_book',
                'doc_employee_signature' => 'employee_signature',
                'doc_hrd_signature' => 'hrd_signature',
            ];

            if ($isSuperAdmin) {
                $docTypes['doc_salary_slip'] = 'salary_slip';
            }

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

            // Salary & Histori tracking for Super Admin
            if ($isSuperAdmin) {
                $newBasic = (float)($validated['basic_salary'] ?? 0);
                $newHourly = (float)($validated['hourly_rate'] ?? 0);
                $newGrade = $validated['line_grade'] ?? null;
                $newLevel = $validated['salary_level'] ?? null;

                $oldBasic = (float)$employee->basic_salary;
                $oldHourly = (float)$employee->hourly_rate;
                $oldGrade = $employee->line_grade;
                $oldLevel = $employee->salary_level;

                $salaryChanged = ($newBasic != $oldBasic || $newHourly != $oldHourly || $newGrade != $oldGrade || $newLevel != $oldLevel);

                // Sync allowances
                $totalAllowances = 0;
                $allowanceDetails = [];
                if ($request->has('allowances') && is_array($request->input('allowances'))) {
                    $employee->allowances()->delete();
                    foreach ($request->input('allowances') as $allowance) {
                        if (!empty($allowance['allowance_name'])) {
                            $amt = (float)($allowance['amount'] ?? 0);
                            $totalAllowances += $amt;
                            $allowanceDetails[] = [
                                'allowance_name' => $allowance['allowance_name'],
                                'amount' => $amt,
                                'notes' => $allowance['notes'] ?? null,
                            ];
                            EmployeeAllowance::create([
                                'employee_id' => $employee->id,
                                'allowance_name' => $allowance['allowance_name'],
                                'amount' => $amt,
                                'notes' => $allowance['notes'] ?? null,
                            ]);
                        }
                    }
                }

                // If salary changed or force history flag set, preserve history and create new entry
                if ($salaryChanged || $request->boolean('create_salary_history_entry')) {
                    $effectiveDate = $request->input('salary_effective_date') ?: date('Y-m-d');

                    // Close old current history
                    $currentHistory = $employee->salaryHistories()->where('is_current', true)->first();
                    if ($currentHistory) {
                        $startYear = $currentHistory->start_date ? $currentHistory->start_date->format('Y') : date('Y');
                        $endYear = date('Y', strtotime($effectiveDate));
                        $periodStr = $startYear == $endYear ? $startYear : "{$startYear}–{$endYear}";

                        $currentHistory->update([
                            'is_current' => false,
                            'end_date' => $effectiveDate,
                            'period' => $periodStr,
                        ]);
                    }

                    $thisYear = date('Y', strtotime($effectiveDate));
                    EmployeeSalaryHistory::create([
                        'employee_id' => $employee->id,
                        'line_grade' => $newGrade,
                        'salary_level' => $newLevel,
                        'basic_salary' => $newBasic,
                        'hourly_rate' => $newHourly,
                        'allowances_total' => $totalAllowances,
                        'allowances_detail' => $allowanceDetails,
                        'start_date' => $effectiveDate,
                        'end_date' => null,
                        'period' => "{$thisYear}–Sekarang",
                        'notes' => $request->input('salary_change_notes') ?: 'Penyesuaian Gaji / Grade',
                        'is_current' => true,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Update main employee fields
            $employee->update($validated);

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
                foreach ($request->input('educations') as $idx => $edu) {
                    if (!empty($edu['institution_name'])) {
                        $docPath = $edu['document_path'] ?? null;
                        if ($request->hasFile("educations.{$idx}.document")) {
                            if ($docPath && Storage::disk('public')->exists($docPath)) {
                                Storage::disk('public')->delete($docPath);
                            }
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

            // Sync work experiences
            if ($request->has('work_experiences')) {
                $employee->workExperiences()->delete();
                foreach ($request->input('work_experiences') as $idx => $exp) {
                    if (!empty($exp['company_name'])) {
                        $docPath = $exp['document_path'] ?? null;
                        if ($request->hasFile("work_experiences.{$idx}.document")) {
                            if ($docPath && Storage::disk('public')->exists($docPath)) {
                                Storage::disk('public')->delete($docPath);
                            }
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

            // Sync certificates
            if ($request->has('certificates')) {
                $employee->certificates()->delete();
                foreach ($request->input('certificates') as $idx => $cert) {
                    if (!empty($cert['certificate_name'])) {
                        $docPath = $cert['document_path'] ?? null;
                        if ($request->hasFile("certificates.{$idx}.document")) {
                            if ($docPath && Storage::disk('public')->exists($docPath)) {
                                Storage::disk('public')->delete($docPath);
                            }
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

            // Sync skills (Repeater)
            if ($request->has('skills')) {
                $employee->skills()->delete();
                foreach ($request->input('skills') as $skillData) {
                    if (!empty($skillData['skill_name'])) {
                        EmployeeSkill::create([
                            'employee_id' => $employee->id,
                            'skill_name' => trim($skillData['skill_name']),
                            'proficiency_level' => $skillData['proficiency_level'] ?? null,
                            'notes' => $skillData['notes'] ?? null,
                        ]);
                    }
                }
            }

            // Sync contracts (Repeater PKWT)
            if ($request->has('contracts')) {
                $employee->contracts()->delete();
                foreach ($request->input('contracts') as $idx => $cData) {
                    if (!empty($cData['contract_number']) || !empty($cData['start_date'])) {
                        $docPath = $cData['document_path'] ?? null;
                        if ($request->hasFile("contracts.{$idx}.document")) {
                            if ($docPath && Storage::disk('public')->exists($docPath)) {
                                Storage::disk('public')->delete($docPath);
                            }
                            $f = $request->file("contracts.{$idx}.document");
                            $docPath = $f->store('employees/documents/contracts', 'public');
                            EmployeeDocument::create([
                                'employee_id' => $employee->id,
                                'document_type' => 'kontrak_kerja',
                                'file_path' => $docPath,
                                'original_name' => $f->getClientOriginalName(),
                                'file_size' => $f->getSize(),
                                'mime_type' => $f->getClientMimeType(),
                            ]);
                        }

                        EmployeeContract::create([
                            'employee_id' => $employee->id,
                            'contract_sequence' => $idx + 1,
                            'contract_number' => $cData['contract_number'] ?? ('PKWT/' . date('Y') . '/' . sprintf('%03d', $idx + 1)),
                            'start_date' => $cData['start_date'] ?? $employee->join_date,
                            'end_date' => $cData['end_date'] ?? date('Y-m-d', strtotime($employee->join_date . ' + 1 year')),
                            'position' => $cData['position'] ?? $employee->position,
                            'department' => $cData['department'] ?? $employee->department,
                            'project_location' => $cData['project_location'] ?? $employee->project_location,
                            'notes' => $cData['notes'] ?? null,
                            'document_path' => $docPath,
                            'status' => $cData['status'] ?? 'Aktif',
                        ]);
                    }
                }
            }

            // Sync licenses (SIM / License Repeater)
            if ($request->has('licenses')) {
                $employee->licenses()->delete();
                foreach ($request->input('licenses') as $idx => $licData) {
                    if (!empty($licData['license_type'])) {
                        $docPath = $licData['document_path'] ?? null;
                        if ($request->hasFile("licenses.{$idx}.document")) {
                            if ($docPath && Storage::disk('public')->exists($docPath)) {
                                Storage::disk('public')->delete($docPath);
                            }
                            $f = $request->file("licenses.{$idx}.document");
                            $docPath = $f->store('employees/documents/licenses', 'public');
                            EmployeeDocument::create([
                                'employee_id' => $employee->id,
                                'document_type' => 'sim_license',
                                'file_path' => $docPath,
                                'original_name' => $f->getClientOriginalName(),
                                'file_size' => $f->getSize(),
                                'mime_type' => $f->getClientMimeType(),
                            ]);
                        }

                        EmployeeLicense::create([
                            'employee_id' => $employee->id,
                            'license_type' => $licData['license_type'],
                            'license_number' => $licData['license_number'] ?? '-',
                            'issue_date' => $licData['issue_date'] ?? null,
                            'expiry_date' => $licData['expiry_date'] ?? null,
                            'notes' => $licData['notes'] ?? null,
                            'document_path' => $docPath,
                        ]);
                    }
                }
            }

            // Sync leaves (Cuti Repeater)
            if ($request->has('leaves')) {
                $employee->leaves()->delete();
                foreach ($request->input('leaves') as $idx => $leaveData) {
                    if (!empty($leaveData['leave_start_date'])) {
                        $docPath = $leaveData['document_path'] ?? null;
                        if ($request->hasFile("leaves.{$idx}.document")) {
                            if ($docPath && Storage::disk('public')->exists($docPath)) {
                                Storage::disk('public')->delete($docPath);
                            }
                            $f = $request->file("leaves.{$idx}.document");
                            $docPath = $f->store('employees/documents/leaves', 'public');
                            EmployeeDocument::create([
                                'employee_id' => $employee->id,
                                'document_type' => 'leave_form',
                                'file_path' => $docPath,
                                'original_name' => $f->getClientOriginalName(),
                                'file_size' => $f->getSize(),
                                'mime_type' => $f->getClientMimeType(),
                            ]);
                        }

                        $startDate = $leaveData['leave_start_date'];
                        $endDate = $leaveData['leave_end_date'] ?? $startDate;
                        $days = $leaveData['total_days'] ?? 1;

                        EmployeeLeave::create([
                            'employee_id' => $employee->id,
                            'leave_start_date' => $startDate,
                            'leave_end_date' => $endDate,
                            'airline' => $leaveData['airline'] ?? null,
                            'total_days' => $days,
                            'notes' => $leaveData['notes'] ?? null,
                            'document_path' => $docPath,
                        ]);

                        $employee->update(['last_leave_date' => $startDate]);
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
        $isSuperAdmin = Auth::user()->isSuperAdmin();

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

        $callback = function () use ($employees, $isSuperAdmin) {
            $output = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            $headerCols = [
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
                'Jenis Kontrak',
                'Tanggal Masuk',
                'Tanggal Keluar',
            ];

            // Only Super Admin gets salary columns
            if ($isSuperAdmin) {
                $headerCols[] = 'Grade';
                $headerCols[] = 'Level';
                $headerCols[] = 'Gaji Pokok';
                $headerCols[] = 'Gaji Per Jam';
            }

            $headerCols = array_merge($headerCols, [
                'NPWP',
                'BPJS Kesehatan',
                'BPJS TK',
                'Bank',
                'No Rekening',
            ]);

            fputcsv($output, $headerCols, ';');

            foreach ($employees as $emp) {
                $row = [
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
                    $emp->contract_type ?? 'PKWT',
                    $emp->join_date ? $emp->join_date->format('d/m/Y') : '',
                    $emp->leave_date ? $emp->leave_date->format('d/m/Y') : '',
                ];

                if ($isSuperAdmin) {
                    $row[] = $emp->line_grade ?? '-';
                    $row[] = $emp->salary_level ?? '-';
                    $row[] = $emp->basic_salary;
                    $row[] = $emp->hourly_rate;
                }

                $row[] = $emp->npwp_number;
                $row[] = $emp->bpjs_kes_number;
                $row[] = $emp->bpjs_tk_number;
                $row[] = $emp->bank_name;
                $row[] = "'" . $emp->bank_account_number;

                fputcsv($output, $row, ';');
            }

            fclose($output);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function downloadDocument(EmployeeDocument $document)
    {
        // Backend authorization: Admin HRD cannot download salary slip
        if ($document->document_type === 'salary_slip' && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Dokumen slip gaji hanya dapat diakses oleh Super Admin.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name ?? basename($document->file_path));
    }

    public function storeSalaryHistory(Request $request, Employee $employee)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat menambahkan riwayat gaji.');
        }

        $validated = $request->validate([
            'line_grade' => ['required', 'string'],
            'salary_level' => ['required', 'string'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'period' => ['nullable', 'string', 'max:50'],
        ]);

        $effectiveDate = $validated['start_date'];

        // Close old current history if setting a new current one
        if (empty($validated['end_date'])) {
            $currentHistory = $employee->salaryHistories()->where('is_current', true)->first();
            if ($currentHistory) {
                $startYear = $currentHistory->start_date ? $currentHistory->start_date->format('Y') : date('Y');
                $endYear = date('Y', strtotime($effectiveDate));
                $currentHistory->update([
                    'is_current' => false,
                    'end_date' => $effectiveDate,
                    'period' => $startYear == $endYear ? $startYear : "{$startYear}–{$endYear}",
                ]);
            }
        }

        $year = date('Y', strtotime($effectiveDate));
        $period = $validated['period'] ?: (empty($validated['end_date']) ? "{$year}–Sekarang" : "{$year}–" . date('Y', strtotime($validated['end_date'])));

        $allowanceDetails = $employee->allowances->map(function ($a) {
            return [
                'allowance_name' => $a->allowance_name,
                'amount' => (float)$a->amount,
                'notes' => $a->notes,
            ];
        })->toArray();

        EmployeeSalaryHistory::create([
            'employee_id' => $employee->id,
            'line_grade' => $validated['line_grade'],
            'salary_level' => $validated['salary_level'],
            'basic_salary' => $validated['basic_salary'],
            'hourly_rate' => $validated['hourly_rate'] ?? 0,
            'allowances_total' => $employee->allowances->sum('amount'),
            'allowances_detail' => $allowanceDetails,
            'start_date' => $effectiveDate,
            'end_date' => $validated['end_date'] ?? null,
            'period' => $period,
            'notes' => $validated['notes'] ?: 'Penetapan Perubahan Gaji',
            'is_current' => empty($validated['end_date']),
            'created_by' => Auth::id(),
        ]);

        if (empty($validated['end_date'])) {
            $employee->update([
                'line_grade' => $validated['line_grade'],
                'salary_level' => $validated['salary_level'],
                'basic_salary' => $validated['basic_salary'],
                'hourly_rate' => $validated['hourly_rate'] ?? 0,
            ]);
        }

        ActivityLog::record(
            'update_gaji',
            "Mencatat riwayat gaji baru untuk {$employee->full_name} ({$employee->employee_id}): {$validated['line_grade']} / {$validated['salary_level']}, Rp " . number_format($validated['basic_salary'], 0, ',', '.'),
            'Employee',
            $employee->id
        );

        return back()->with('success', 'Riwayat perubahan gaji berhasil disimpan!');
    }

    public function destroySalaryHistory(EmployeeSalaryHistory $history)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $employee = $history->employee;
        $history->delete();

        // If the deleted one was current, set the latest remaining as current
        if ($history->is_current && $employee) {
            $latest = $employee->salaryHistories()->orderByDesc('start_date')->first();
            if ($latest) {
                $latest->update(['is_current' => true, 'end_date' => null]);
                $employee->update([
                    'line_grade' => $latest->line_grade,
                    'salary_level' => $latest->salary_level,
                    'basic_salary' => $latest->basic_salary,
                    'hourly_rate' => $latest->hourly_rate,
                ]);
            }
        }

        return back()->with('success', 'Riwayat gaji berhasil dihapus.');
    }
}
