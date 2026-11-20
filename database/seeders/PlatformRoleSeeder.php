<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Identity\Services\RoleProvisioner;
use Spatie\Permission\Models\Role;

class PlatformRoleSeeder extends Seeder
{
    public function run(): void
    {
        $provisioner = app(RoleProvisioner::class);
        $provisioner->seedPlatformRoles();

        // Pivot tenant_id cannot be null when teams=true — use platform team 0.
        setPermissionsTeamId(RoleProvisioner::platformTeamId());

        $admin = User::query()
            ->where('email', 'superadmin@sme.local')
            ->where('is_platform_admin', true)
            ->first();

        if ($admin) {
            $role = Role::findByName('Super Admin', 'web');
            if (! $admin->hasRole($role)) {
                $admin->assignRole($role);
            }
        }
    }
}
