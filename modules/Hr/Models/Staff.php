<?php

namespace Modules\Hr\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academics\Models\Branch;
use Modules\Academics\Models\Department;

class Staff extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $table = 'staff';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_TERMINATED = 'terminated';

    public const TYPE_EMPLOYEE = 'employee';

    public const TYPE_TEACHER = 'teacher';

    public const TYPE_BOTH = 'both';

    protected $fillable = [
        'tenant_id', 'user_id', 'designation_id', 'department_id', 'branch_id',
        'employee_code', 'first_name', 'last_name', 'name_bn', 'gender', 'date_of_birth',
        'phone', 'email', 'nid_no', 'address', 'city', 'division', 'photo_path',
        'staff_type', 'is_teacher', 'joined_on', 'left_on', 'status',
        'bank_name', 'bank_account', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joined_on' => 'date',
            'left_on' => 'date',
            'is_teacher' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StaffDocument::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function currentSalary(): HasOne
    {
        return $this->hasOne(StaffSalary::class)->where('is_current', true)->latestOfMany();
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(StaffSalary::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function scopeTeachers($query)
    {
        return $query->where(function ($q) {
            $q->where('is_teacher', true)
                ->orWhereIn('staff_type', [self::TYPE_TEACHER, self::TYPE_BOTH]);
        });
    }
}
