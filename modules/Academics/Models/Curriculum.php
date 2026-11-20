<?php

namespace Modules\Academics\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    use BelongsToTenant;

    protected $table = 'curricula';

    protected $fillable = [
        'tenant_id', 'name', 'code', 'board', 'description', 'is_default', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'curriculum_subject')
            ->withPivot(['tenant_id', 'school_class_id', 'is_compulsory', 'weekly_periods'])
            ->withTimestamps();
    }

    public function subjectLinks(): HasMany
    {
        return $this->hasMany(CurriculumSubject::class);
    }
}
