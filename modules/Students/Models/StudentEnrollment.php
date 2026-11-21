<?php

namespace Modules\Students\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Shift;

class StudentEnrollment extends Model
{
    use BelongsToTenant;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_TRANSFERRED = 'transferred';

    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'tenant_id', 'student_id', 'academic_year_id', 'school_class_id', 'section_id', 'shift_id',
        'roll_number', 'status', 'is_current', 'enrolled_on', 'ended_on',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'enrolled_on' => 'date',
            'ended_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
