<?php

namespace Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Tenancy\Models\Tenant;

class TenantUsageMetric extends Model
{
    public const STUDENTS = 'students';

    public const STAFF = 'staff';

    public const STORAGE_MB = 'storage_mb';

    protected $fillable = [
        'tenant_id',
        'metric_key',
        'current_value',
    ];

    protected function casts(): array
    {
        return [
            'current_value' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
