<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'join_date' => 'date',
            'leave_date' => 'date',
            'last_leave_date' => 'date',
            'basic_salary' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
        ];
    }

    public function departmentRelation(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function salaryHistories(): HasMany
    {
        return $this->hasMany(EmployeeSalaryHistory::class)->orderByDesc('id');
    }

    public function currentSalaryHistory(): HasOne
    {
        return $this->hasOne(EmployeeSalaryHistory::class)->where('is_current', true);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class)->orderByDesc('leave_start_date');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class)->orderBy('contract_sequence');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(EmployeeLicense::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(EmployeeCertificate::class);
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(EmployeeAllowance::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function getDocument(string $type)
    {
        return $this->documents->firstWhere('document_type', $type);
    }

    public function hasDocument(string $type): bool
    {
        return $this->documents->contains('document_type', $type);
    }

    /**
     * Required documents checklist for compliance
     */
    public static function requiredDocumentTypes(): array
    {
        return [
            'ktp' => 'KTP',
            'kk' => 'Kartu Keluarga',
            'photo' => 'Pas Foto 3x4',
            'npwp' => 'NPWP',
            'bpjs_kes' => 'BPJS Kesehatan',
            'bpjs_tk' => 'BPJS Ketenagakerjaan',
            'bank_book' => 'Buku Rekening',
        ];
    }

    public function missingDocuments(): array
    {
        $required = self::requiredDocumentTypes();
        $uploaded = $this->documents->pluck('document_type')->toArray();
        $missing = [];

        foreach ($required as $key => $label) {
            if (!in_array($key, $uploaded)) {
                $missing[$key] = $label;
            }
        }

        return $missing;
    }

    public function isDocumentsComplete(): bool
    {
        return count($this->missingDocuments()) === 0;
    }
}
