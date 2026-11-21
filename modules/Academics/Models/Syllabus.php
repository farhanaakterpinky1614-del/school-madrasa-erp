<?php

namespace Modules\Academics\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Syllabus extends Model
{
    use BelongsToTenant;

    protected $table = 'syllabi';

    protected $fillable = [
        'tenant_id', 'curriculum_id', 'academic_year_id', 'school_class_id',
        'subject_id', 'title', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(SyllabusTopic::class)->orderBy('sort_order');
    }
}
