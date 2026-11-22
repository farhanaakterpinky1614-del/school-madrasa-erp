<?php

namespace Modules\Examinations\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Models\Subject;

class ExamSubject extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'exam_id', 'subject_id', 'full_marks', 'pass_marks', 'weight',
        'sort_order', 'is_optional',
    ];

    protected function casts(): array
    {
        return [
            'full_marks' => 'decimal:2',
            'pass_marks' => 'decimal:2',
            'weight' => 'decimal:2',
            'is_optional' => 'boolean',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }
}
