<?php

namespace Modules\Academics\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Hr\Models\Staff;

class LessonPlan extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'tenant_id', 'academic_year_id', 'school_class_id', 'section_id',
        'subject_id', 'staff_id', 'syllabus_topic_id', 'title', 'objectives',
        'activities', 'resources', 'homework_notes', 'planned_on', 'status', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'planned_on' => 'date',
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function syllabusTopic(): BelongsTo
    {
        return $this->belongsTo(SyllabusTopic::class);
    }
}
