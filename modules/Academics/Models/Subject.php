<?php

namespace Modules\Academics\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'name_bn', 'name_ar', 'code', 'subject_type', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function curricula(): BelongsToMany
    {
        return $this->belongsToMany(Curriculum::class, 'curriculum_subject')
            ->withPivot(['tenant_id', 'school_class_id', 'is_compulsory', 'weekly_periods'])
            ->withTimestamps();
    }
}
