<?php

namespace Modules\Students\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\Models\AdmissionApplication;

class Student extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public const STATUS_ALUMNI = 'alumni';

    public const STATUS_TRANSFERRED = 'transferred';

    protected $fillable = [
        'tenant_id', 'student_code', 'admission_number', 'first_name', 'last_name', 'name_bn',
        'gender', 'date_of_birth', 'blood_group', 'religion', 'nationality',
        'birth_certificate_no', 'nid_no', 'phone', 'email', 'address', 'city', 'division',
        'photo_path', 'status', 'enrolled_on', 'withdrawn_on', 'alumni_on', 'withdrawal_reason',
        'custom_fields', 'notes', 'qr_payload',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'enrolled_on' => 'date',
            'withdrawn_on' => 'date',
            'alumni_on' => 'date',
            'custom_fields' => 'array',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withPivot(['relationship', 'is_primary', 'is_emergency', 'can_pickup', 'tenant_id'])
            ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class)->where('is_current', true)->latestOfMany();
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(StudentTransfer::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(StudentPromotion::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdmissionApplication::class);
    }
}
