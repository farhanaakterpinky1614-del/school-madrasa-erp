<?php

namespace Modules\Tenancy\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Platform\Models\Plan;
use Modules\Platform\Models\TenantSubscription;
use Modules\Platform\Services\UsageTracker;
use Modules\Tenancy\Models\Institution;
use Modules\Tenancy\Models\Tenant;
use Modules\Tenancy\Models\TenantDomain;

class TenantRegistrar
{
    public function __construct(
        protected TenantResolver $resolver,
        protected UsageTracker $usageTracker,
    ) {}

    /**
     * @param  array{
     *     institution_name: string,
     *     slug: string,
     *     institution_type: string,
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     *     plan_code?: string,
     *     locale?: string,
     *     email?: string|null,
     *     phone?: string|null,
     * }  $data
     * @return array{tenant: Tenant, user: User, institution: Institution}
     */
    public function register(array $data): array
    {
        $slug = strtolower(trim($data['slug']));

        if ($this->resolver->isReservedSlug($slug)) {
            throw ValidationException::withMessages([
                'slug' => 'This subdomain is reserved.',
            ]);
        }

        if (! preg_match('/^[a-z0-9]([a-z0-9-]{1,61}[a-z0-9])?$/', $slug)) {
            throw ValidationException::withMessages([
                'slug' => 'Slug must be 3–63 chars: lowercase letters, numbers, hyphens.',
            ]);
        }

        if (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'This subdomain is already taken.',
            ]);
        }

        $planCode = $data['plan_code'] ?? Plan::STARTER;
        $plan = Plan::query()->where('code', $planCode)->where('is_active', true)->firstOrFail();

        return DB::transaction(function () use ($data, $slug, $plan) {
            $trialEnds = now()->addDays($plan->trial_days);

            $tenant = Tenant::query()->create([
                'name' => $data['institution_name'],
                'slug' => $slug,
                'institution_type' => $data['institution_type'],
                'status' => Tenant::STATUS_ACTIVE,
                'plan_id' => $plan->id,
                'timezone' => config('sme.timezone', 'Asia/Dhaka'),
                'locale_default' => $data['locale'] ?? 'en',
                'currency' => config('sme.currency', 'BDT'),
                'trial_ends_at' => $trialEnds,
                'settings' => [],
            ]);

            $host = $this->resolver->subdomainHostFor($slug);

            TenantDomain::queryWithoutTenantScope()->create([
                'tenant_id' => $tenant->id,
                'host' => $host,
                'type' => TenantDomain::TYPE_SUBDOMAIN,
                'is_primary' => true,
                'verification_status' => TenantDomain::STATUS_VERIFIED,
                'verified_at' => now(),
            ]);

            $institution = Institution::queryWithoutTenantScope()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['institution_name'],
                'institution_type' => $data['institution_type'],
                'email' => $data['email'] ?? $data['admin_email'],
                'phone' => $data['phone'] ?? null,
                'settings' => [],
            ]);

            TenantSubscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => TenantSubscription::STATUS_TRIALING,
                'trial_ends_at' => $trialEnds,
                'starts_at' => now(),
            ]);

            $this->usageTracker->initializeForTenant($tenant);

            $user = User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'is_platform_admin' => false,
                'user_type' => User::TYPE_TENANT_ADMIN,
                'email_verified_at' => now(),
            ]);

            return compact('tenant', 'user', 'institution');
        });
    }
}
