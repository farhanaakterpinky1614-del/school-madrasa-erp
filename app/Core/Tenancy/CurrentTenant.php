<?php

namespace App\Core\Tenancy;

use Modules\Tenancy\Models\Tenant;

class CurrentTenant
{
    protected ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;

        if ($tenant) {
            app()->instance('currentTenant', $tenant);
            config(['permission.team_id' => $tenant->id]);
        } else {
            app()->forgetInstance('currentTenant');
            config(['permission.team_id' => null]);
        }
    }

    public function clear(): void
    {
        $this->set(null);
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }

    public function require(): Tenant
    {
        if (! $this->tenant) {
            abort(404, 'Tenant context is required.');
        }

        return $this->tenant;
    }
}
