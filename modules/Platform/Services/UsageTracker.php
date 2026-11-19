<?php

namespace Modules\Platform\Services;

use Modules\Platform\Models\TenantUsageMetric;
use Modules\Tenancy\Models\Tenant;

class UsageTracker
{
    /**
     * @var list<string>
     */
    public const DEFAULT_METRICS = [
        TenantUsageMetric::STUDENTS,
        TenantUsageMetric::STAFF,
        TenantUsageMetric::STORAGE_MB,
    ];

    public function initializeForTenant(Tenant $tenant): void
    {
        foreach (self::DEFAULT_METRICS as $key) {
            TenantUsageMetric::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'metric_key' => $key],
                ['current_value' => 0],
            );
        }
    }

    public function get(Tenant $tenant, string $metricKey): int
    {
        return (int) TenantUsageMetric::query()
            ->where('tenant_id', $tenant->id)
            ->where('metric_key', $metricKey)
            ->value('current_value');
    }

    /**
     * Stub-friendly setter — real counters will be driven by domain modules later.
     */
    public function set(Tenant $tenant, string $metricKey, int $value): TenantUsageMetric
    {
        $metric = TenantUsageMetric::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'metric_key' => $metricKey],
            ['current_value' => max(0, $value)],
        );

        return $metric;
    }

    public function increment(Tenant $tenant, string $metricKey, int $by = 1): TenantUsageMetric
    {
        $metric = TenantUsageMetric::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'metric_key' => $metricKey],
            ['current_value' => 0],
        );

        $metric->increment('current_value', $by);

        return $metric->refresh();
    }

    /**
     * @return array<string, array{current: int, limit: int|null}>
     */
    public function summary(Tenant $tenant, FeatureAccess $features): array
    {
        $limitMap = [
            TenantUsageMetric::STUDENTS => 'max_students',
            TenantUsageMetric::STAFF => 'max_staff',
            TenantUsageMetric::STORAGE_MB => 'max_storage_mb',
        ];

        $out = [];

        foreach (self::DEFAULT_METRICS as $key) {
            $out[$key] = [
                'current' => $this->get($tenant, $key),
                'limit' => $features->limit($tenant, $limitMap[$key]),
            ];
        }

        return $out;
    }
}
