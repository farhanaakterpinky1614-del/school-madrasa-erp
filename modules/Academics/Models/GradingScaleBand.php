<?php

namespace Modules\Academics\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingScaleBand extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'grading_scale_id', 'grade', 'min_mark', 'max_mark', 'gpa', 'remark', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_mark' => 'decimal:2',
            'max_mark' => 'decimal:2',
            'gpa' => 'decimal:2',
        ];
    }

    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }
}
