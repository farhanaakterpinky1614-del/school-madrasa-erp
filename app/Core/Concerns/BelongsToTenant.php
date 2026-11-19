<?php

namespace App\Core\Concerns;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Tenancy\Models\Tenant;

/**
 * Row-level tenancy: global scope + force tenant_id from context on create.
 * Never trust client-supplied tenant_id.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantId = app(CurrentTenant::class)->id();

            if ($tenantId !== null) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
            }
        });

        static::creating(function (Model $model): void {
            $tenantId = app(CurrentTenant::class)->id();

            if ($tenantId === null) {
                return;
            }

            // Always overwrite — ignore request / mass-assignment tenant_id.
            $model->setAttribute('tenant_id', $tenantId);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Query without the tenant global scope (platform admin use).
     */
    public static function queryWithoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
