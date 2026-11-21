<?php

namespace Modules\Examinations\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Models\Section;
use Modules\Students\Models\Student;

class Mark extends Model
{
    use BelongsToTenant;

    public const ENTRY_DRAFT = 'draft';

    public const ENTRY_SUBMITTED = 'submitted';

    public const ENTRY_STATUSES = [
        self::ENTRY_DRAFT,
        self::ENTRY_SUBMITTED,
    ];

    protected $fillable = [
        'tenant_id', 'exam_id', 'exam_subject_id', 'student_id', 'section_id',
        'marks_obtained', 'grade', 'gpa', 'is_absent', 'remarks', 'entry_status', 'entered_by',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'decimal:2',
            'gpa' => 'decimal:2',
            'is_absent' => 'boolean',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
