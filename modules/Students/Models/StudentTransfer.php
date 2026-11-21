<?php

namespace Modules\Students\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;

class StudentTransfer extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'student_id', 'academic_year_id', 'from_enrollment_id', 'to_enrollment_id',
        'from_class_id', 'from_section_id', 'to_class_id', 'to_section_id',
        'transferred_on', 'reason', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['transferred_on' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id');
    }

    public function fromSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'from_section_id');
    }

    public function toSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
