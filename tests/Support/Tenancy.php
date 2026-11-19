<?php

namespace Tests\Support;

use App\Core\Tenancy\CurrentTenant;
use App\Models\User;
use Database\Seeders\ModuleRegistrySeeder;
use Database\Seeders\PlanSeeder;
use Modules\Tenancy\Models\Tenant;
use Modules\Tenancy\Services\TenantRegistrar;

trait Tenancy
{
    protected function seedPlansAndModules(): void
    {
        $this->seed(PlanSeeder::class);
        $this->seed(ModuleRegistrySeeder::class);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{tenant: Tenant, user: User, institution: \Modules\Tenancy\Models\Institution}
     */
    protected function registerTenant(array $overrides = []): array
    {
        $this->seedPlansAndModules();

        $slug = $overrides['slug'] ?? 'school-'.fake()->unique()->numerify('###');

        return app(TenantRegistrar::class)->register([
            'institution_name' => $overrides['institution_name'] ?? 'Demo School',
            'slug' => $slug,
            'institution_type' => $overrides['institution_type'] ?? 'school',
            'admin_name' => $overrides['admin_name'] ?? 'Tenant Admin',
            'admin_email' => $overrides['admin_email'] ?? fake()->unique()->safeEmail(),
            'admin_password' => $overrides['admin_password'] ?? 'password123',
            'plan_code' => $overrides['plan_code'] ?? 'starter',
            'phone' => $overrides['phone'] ?? null,
        ]);
    }

    protected function actingAsPlatformAdmin(): User
    {
        $user = User::factory()->create([
            'tenant_id' => null,
            'is_platform_admin' => true,
            'user_type' => User::TYPE_PLATFORM_ADMIN,
            'email' => 'super-'.fake()->unique()->numerify('###').'@sme.test',
        ]);

        $this->actingAs($user);

        return $user;
    }

    protected function bindTenant(Tenant $tenant): void
    {
        app(CurrentTenant::class)->set($tenant);
    }

    protected function withTenantHost(string $slug): static
    {
        $base = config('sme.tenancy.base_domain');

        return $this->withServerVariables([
            'HTTP_HOST' => $slug.'.'.$base,
            'SERVER_NAME' => $slug.'.'.$base,
        ]);
    }
}
