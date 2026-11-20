<?php

namespace App\Http\Controllers;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Route model binding may run before tenant fallback binds CurrentTenant.
     * Always assert ownership after middleware so cross-tenant IDs 404.
     */
    protected function assertTenantOwned(Model $model): void
    {
        $tenantId = app(CurrentTenant::class)->id();

        abort_unless(
            $tenantId !== null && (int) $model->getAttribute('tenant_id') === (int) $tenantId,
            404
        );
    }
}
