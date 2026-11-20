<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        if ($actor->is_platform_admin) {
            return $actor->can('platform.users.view') || $actor->isPlatformAdmin();
        }

        return $actor->isTenantAdmin() || $actor->can('settings.users.view');
    }

    public function view(User $actor, User $target): bool
    {
        if ($actor->is_platform_admin) {
            return true;
        }

        return (int) $actor->tenant_id === (int) $target->tenant_id
            && ($actor->isTenantAdmin() || $actor->can('settings.users.view'));
    }

    public function create(User $actor): bool
    {
        if ($actor->is_platform_admin) {
            return $actor->can('platform.users.create') || $actor->isPlatformAdmin();
        }

        return $actor->isTenantAdmin() || $actor->can('settings.users.create');
    }

    public function update(User $actor, User $target): bool
    {
        if ($actor->is_platform_admin) {
            return true;
        }

        return (int) $actor->tenant_id === (int) $target->tenant_id
            && ($actor->isTenantAdmin() || $actor->can('settings.users.update'));
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        if ($actor->is_platform_admin) {
            return true;
        }

        return (int) $actor->tenant_id === (int) $target->tenant_id
            && ($actor->isTenantAdmin() || $actor->can('settings.users.delete'));
    }
}
