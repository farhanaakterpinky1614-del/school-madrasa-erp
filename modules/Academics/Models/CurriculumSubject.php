<?php

namespace Modules\Academics\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumSubject extends Model
{
    use BelongsToTenant;

    protected $table = 'curriculum_subject';

    protected $fillable = [
        'tenant_id', 'curriculum_id', 'subject_id', 'school_class_id', 'is_compulsory', 'weekly_periods',
    ];

    protected function casts(): array
    {
        return ['is_compulsory' => 'boolean'];
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
