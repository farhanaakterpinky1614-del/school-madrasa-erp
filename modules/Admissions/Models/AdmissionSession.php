<?php

namespace Modules\Admissions\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;

class AdmissionSession extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'academic_year_id', 'school_class_id', 'admission_form_template_id',
        'name', 'code', 'public_slug', 'opens_at', 'closes_at', 'capacity',
        'allow_online', 'allow_offline', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'allow_online' => 'boolean',
            'allow_offline' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(AdmissionFormTemplate::class, 'admission_form_template_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    public function isOpenForOnline(): bool
    {
        if (! $this->is_active || ! $this->allow_online) {
            return false;
        }

        $now = now();
        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }
        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }
}
