<?php

namespace Modules\Tenancy\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Platform\Models\Plan;
use Modules\Platform\Models\TenantSubscription;
use Modules\Platform\Models\TenantUsageMetric;

class Tenant extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'name',
        'slug',
        'institution_type',
        'status',
        'plan_id',
        'timezone',
        'locale_default',
        'currency',
        'settings',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function institution(): HasOne
    {
        return $this->hasOne(Institution::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(TenantDomain::class)->where('is_primary', true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class)->latestOfMany();
    }

    public function usageMetrics(): HasMany
    {
        return $this->hasMany(TenantUsageMetric::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }
}
