<?php

namespace Modules\Platform\Services;

use App\Exceptions\QuotaExceededException;
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

    /**
     * Metric key → plan feature key.
     *
     * @var array<string, string>
     */
    public const LIMIT_MAP = [
        TenantUsageMetric::STUDENTS => 'max_students',
        TenantUsageMetric::STAFF => 'max_staff',
        TenantUsageMetric::STORAGE_MB => 'max_storage_mb',
    ];

    public function __construct(
        protected FeatureAccess $features,
    ) {}

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

    public function limitFor(Tenant $tenant, string $metricKey): ?int
    {
        $featureKey = self::LIMIT_MAP[$metricKey] ?? null;

        if ($featureKey === null) {
            return null;
        }

        return $this->features->limit($tenant, $featureKey);
    }

    /**
     * Soft check: would adding $by exceed the plan hard quota?
     */
    public function wouldExceed(Tenant $tenant, string $metricKey, int $by = 1): bool
    {
        $limit = $this->limitFor($tenant, $metricKey);

        if ($limit === null) {
            return false; // unlimited
        }

        return ($this->get($tenant, $metricKey) + $by) > $limit;
    }

    /**
     * Hard quota enforcement — throws QuotaExceededException when over plan limit.
     *
     * @throws QuotaExceededException
     */
    public function assertWithinQuota(Tenant $tenant, string $metricKey, int $by = 1): void
    {
        if (! $this->wouldExceed($tenant, $metricKey, $by)) {
            return;
        }

        $limit = $this->limitFor($tenant, $metricKey);
        $current = $this->get($tenant, $metricKey);

        throw new QuotaExceededException(
            $metricKey,
            $current,
            $limit,
            "Plan quota exceeded for {$metricKey} (current {$current}, limit {$limit}).",
        );
    }

    /**
     * Assert + increment in one step for create flows.
     *
     * @throws QuotaExceededException
     */
    public function consume(Tenant $tenant, string $metricKey, int $by = 1): TenantUsageMetric
    {
        $this->assertWithinQuota($tenant, $metricKey, $by);

        return $this->increment($tenant, $metricKey, $by);
    }

    /**
     * @return array<string, array{current: int, limit: int|null, remaining: int|null, exceeded: bool}>
     */
    public function summary(Tenant $tenant, ?FeatureAccess $features = null): array
    {
        $out = [];

        foreach (self::DEFAULT_METRICS as $key) {
            $current = $this->get($tenant, $key);
            $limit = $this->limitFor($tenant, $key);
            $out[$key] = [
                'current' => $current,
                'limit' => $limit,
                'remaining' => $limit === null ? null : max(0, $limit - $current),
                'exceeded' => $limit !== null && $current > $limit,
            ];
        }

        return $out;
    }
}
