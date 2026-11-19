<?php

namespace Modules\Platform\Services;

use Modules\Platform\Models\ModuleRegistry;
use Modules\Platform\Models\Plan;
use Modules\Tenancy\Models\Tenant;

class FeatureAccess
{
    public function planFor(Tenant $tenant): ?Plan
    {
        return $tenant->plan ?? $tenant->currentSubscription?->plan;
    }

    public function enabled(Tenant $tenant, string $featureKey): bool
    {
        $plan = $this->planFor($tenant);

        if (! $plan) {
            return false;
        }

        $feature = $plan->features()->where('feature_key', $featureKey)->first();

        return $feature?->enabled ?? false;
    }

    public function limit(Tenant $tenant, string $featureKey): ?int
    {
        $plan = $this->planFor($tenant);

        if (! $plan) {
            return 0;
        }

        $feature = $plan->features()->where('feature_key', $featureKey)->first();

        if (! $feature || ! $feature->enabled) {
            return 0;
        }

        return $feature->limit_value;
    }

    /**
     * @return list<array{key: string, name: string, description: ?string, accessible: bool, is_core: bool}>
     */
    public function modulesFor(Tenant $tenant): array
    {
        $modules = ModuleRegistry::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $modules->map(function (ModuleRegistry $module) use ($tenant) {
            $accessible = $module->is_core
                || $module->feature_key === null
                || $this->enabled($tenant, $module->feature_key);

            return [
                'key' => $module->key,
                'name' => $module->name,
                'description' => $module->description,
                'accessible' => $accessible,
                'is_core' => $module->is_core,
            ];
        })->all();
    }

    public function assertModule(Tenant $tenant, string $moduleKey): void
    {
        $modules = collect($this->modulesFor($tenant))->keyBy('key');
        $module = $modules->get($moduleKey);

        if (! $module || ! $module['accessible']) {
            abort(403, 'This module is not available on your plan.');
        }
    }
}
