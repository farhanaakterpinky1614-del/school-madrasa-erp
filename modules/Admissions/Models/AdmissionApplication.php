<?php

namespace Modules\Admissions\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Models\SchoolClass;
use Modules\Students\Models\Student;

class AdmissionApplication extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_ENROLLED = 'enrolled';

    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'tenant_id', 'admission_session_id', 'application_number', 'channel', 'status',
        'first_name', 'last_name', 'name_bn', 'gender', 'date_of_birth', 'phone', 'email', 'address',
        'guardian_name', 'guardian_phone', 'guardian_relation', 'school_class_id', 'form_payload',
        'submitted_at', 'reviewed_at', 'reviewed_by', 'decision_notes', 'student_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'form_payload' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AdmissionSession::class, 'admission_session_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AdmissionApplicationDocument::class);
    }
}
