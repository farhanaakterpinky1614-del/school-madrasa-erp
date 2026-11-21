<?php

namespace Modules\Academics\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyllabusTopic extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'syllabus_id', 'parent_id', 'title', 'description',
        'sort_order', 'estimated_periods', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function syllabus(): BelongsTo
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function lessonPlans(): HasMany
    {
        return $this->hasMany(LessonPlan::class);
    }
}
