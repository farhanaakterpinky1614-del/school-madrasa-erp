<?php

namespace Modules\Identity\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Models\TenantMembership;
use Modules\Tenancy\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleProvisioner
{
    public function __construct(
        protected PermissionRegistrar $registrar,
    ) {}

    /**
     * Seed the global permission catalogue (idempotent).
     */
    public function seedPermissionCatalog(): void
    {
        foreach (config('identity.permissions') as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $this->registrar->forgetCachedPermissions();
    }

    /**
     * Seed platform roles (roles.tenant_id null; assignments use team id 0).
     */
    public function seedPlatformRoles(): void
    {
        $this->seedPermissionCatalog();

        // Role rows are global (nullable tenant_id). Pivot assignments use team 0.
        setPermissionsTeamId(null);

        foreach (config('identity.platform_roles') as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        $this->registrar->forgetCachedPermissions();
    }

    /**
     * Team id used for platform admin role pivots (Spatie teams FK is non-null).
     */
    public static function platformTeamId(): int
    {
        return 0;
    }

    /**
     * Clone tenant role templates for a tenant and optionally assign Institution Admin.
     */
    public function provisionTenantRoles(Tenant $tenant, ?User $admin = null): void
    {
        $this->seedPermissionCatalog();

        $tenantPermissions = collect(config('identity.permissions'))
            ->reject(fn (string $p) => str_starts_with($p, 'platform.'))
            ->values()
            ->all();

        setPermissionsTeamId($tenant->id);

        foreach (config('identity.tenant_role_templates') as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            $resolved = $permissions === ['*tenant*'] || in_array('*tenant*', $permissions, true)
                ? $tenantPermissions
                : $permissions;

            $role->syncPermissions($resolved);
        }

        if ($admin) {
            $adminRole = Role::findByName('Institution Admin', 'web');
            $admin->assignRole($adminRole);

            TenantMembership::queryWithoutTenantScope()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $admin->id,
                ],
                [
                    'status' => TenantMembership::STATUS_ACTIVE,
                    'joined_at' => now(),
                ]
            );
        }

        $this->registrar->forgetCachedPermissions();
    }

    /**
     * Create a custom tenant role from selected permissions (custom role builder).
     *
     * @param  list<string>  $permissions
     */
    public function createCustomRole(Tenant $tenant, string $name, array $permissions): Role
    {
        return DB::transaction(function () use ($tenant, $name, $permissions) {
            setPermissionsTeamId($tenant->id);

            $allowed = collect(config('identity.permissions'))
                ->reject(fn (string $p) => str_starts_with($p, 'platform.'))
                ->all();

            $filtered = array_values(array_intersect($permissions, $allowed));

            $role = Role::create([
                'name' => $name,
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);

            $role->syncPermissions($filtered);

            $this->registrar->forgetCachedPermissions();

            return $role;
        });
    }
}
