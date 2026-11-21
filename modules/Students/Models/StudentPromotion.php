<?php

namespace Modules\Students\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;

class StudentPromotion extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'student_id', 'from_enrollment_id', 'to_enrollment_id',
        'from_year_id', 'to_year_id', 'from_class_id', 'to_class_id', 'to_section_id',
        'status', 'promoted_on', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['promoted_on' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'from_year_id');
    }

    public function toYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'to_year_id');
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id');
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
