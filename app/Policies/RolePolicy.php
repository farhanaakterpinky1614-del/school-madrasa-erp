<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isTenantAdmin() || $actor->can('settings.roles.view');
    }

    public function view(User $actor, Role $role): bool
    {
        return $this->sameTenant($actor, $role)
            && ($actor->isTenantAdmin() || $actor->can('settings.roles.view'));
    }

    public function create(User $actor): bool
    {
        return $actor->isTenantAdmin() || $actor->can('settings.roles.create');
    }

    public function update(User $actor, Role $role): bool
    {
        return $this->sameTenant($actor, $role)
            && ($actor->isTenantAdmin() || $actor->can('settings.roles.update'));
    }

    public function delete(User $actor, Role $role): bool
    {
        if ($role->name === 'Institution Admin') {
            return false;
        }

        return $this->sameTenant($actor, $role)
            && ($actor->isTenantAdmin() || $actor->can('settings.roles.delete'));
    }

    protected function sameTenant(User $actor, Role $role): bool
    {
        return (int) $actor->tenant_id === (int) $role->tenant_id;
    }
}
