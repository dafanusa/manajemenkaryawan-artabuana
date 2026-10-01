<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) return '-';
        if ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 2) . ' MB';
        } elseif ($this->file_size >= 1024) {
            return number_format($this->file_size / 1024, 1) . ' KB';
        }
        return $this->file_size . ' B';
    }
}
