<?php

namespace Modules\Integrations\Services;

use App\Core\Tenancy\CurrentTenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Integrations\Models\WebhookSubscription;
use Modules\Platform\Services\ThemePack;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo webhook subscription + appearance pack hook for Phase 16.
 */
class DemoHardeningProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected WebhookDispatcher $dispatcher,
        protected ThemePack $themePack,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $sub = WebhookSubscription::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Demo accounting hook',
                ],
                [
                    'target_url' => 'https://hooks.example.test/sme/demo',
                    'secret' => 'demo-webhook-secret',
                    'events' => WebhookSubscription::EVENTS,
                    'is_active' => true,
                ]
            );

            $this->dispatcher->deliver($tenant, $sub, WebhookSubscription::EVENT_FEE_PAID, [
                'test' => true,
                'message' => 'Demo seed delivery',
            ]);

            if ($tenant->institution) {
                $this->themePack->setPack($tenant, $tenant->institution, 'indigo_slate');
            }

            // Ensure demo admin has a named Sanctum token for API smoke (plain text discarded).
            $admin = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('email', 'admin@demo-school.test')
                ->first();

            if ($admin && $admin->tokens()->where('name', 'demo-seed')->doesntExist()) {
                $admin->createToken('demo-seed', ['*']);
            }
        });
    }
}
