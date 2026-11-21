<?php

namespace Modules\Academics\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'shift_id', 'name', 'code', 'sort_order',
        'starts_at', 'ends_at', 'is_break', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_break' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function routines(): HasMany
    {
        return $this->hasMany(ClassRoutine::class);
    }
}
