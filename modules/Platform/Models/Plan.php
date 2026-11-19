<?php

namespace Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Tenancy\Models\Tenant;

class Plan extends Model
{
    public const STARTER = 'starter';

    public const PROFESSIONAL = 'professional';

    public const ENTERPRISE = 'enterprise';

    protected $fillable = [
        'code',
        'name',
        'description',
        'price_monthly_bdt',
        'trial_days',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_monthly_bdt' => 'integer',
            'trial_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function feature(string $key): ?PlanFeature
    {
        return $this->features->firstWhere('feature_key', $key);
    }
}
