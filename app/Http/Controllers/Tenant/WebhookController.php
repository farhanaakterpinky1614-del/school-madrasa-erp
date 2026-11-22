<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Integrations\Models\WebhookDelivery;
use Modules\Integrations\Models\WebhookSubscription;
use Modules\Integrations\Services\WebhookDispatcher;
use Modules\Platform\Services\FeatureAccess;

class WebhookController extends Controller
{
    public function index(CurrentTenant $currentTenant, FeatureAccess $features): View
    {
        $tenant = $currentTenant->require();

        return view('tenant.webhooks.index', [
            'tenant' => $tenant,
            'canWebhooks' => $features->enabled($tenant, 'webhooks'),
            'subscriptions' => WebhookSubscription::query()->orderByDesc('id')->get(),
            'deliveries' => WebhookDelivery::query()->with('subscription')->orderByDesc('id')->limit(50)->get(),
            'events' => WebhookSubscription::EVENTS,
        ]);
    }

    public function store(Request $request, CurrentTenant $currentTenant, FeatureAccess $features): RedirectResponse
    {
        $tenant = $currentTenant->require();
        abort_unless($features->enabled($tenant, 'webhooks'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'target_url' => ['required', 'url', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', Rule::in(WebhookSubscription::EVENTS)],
            'secret' => ['nullable', 'string', 'max:128'],
        ]);

        WebhookSubscription::query()->create([
            'name' => $data['name'],
            'target_url' => $data['target_url'],
            'secret' => $data['secret'] ?: Str::random(32),
            'events' => array_values($data['events']),
            'is_active' => true,
        ]);

        return back()->with('status', 'Webhook subscription created.');
    }

    public function destroy(WebhookSubscription $webhook, CurrentTenant $currentTenant, FeatureAccess $features): RedirectResponse
    {
        $tenant = $currentTenant->require();
        abort_unless($features->enabled($tenant, 'webhooks'), 403);
        abort_unless((int) $webhook->tenant_id === (int) $tenant->id, 404);

        $webhook->delete();

        return back()->with('status', 'Webhook subscription removed.');
    }

    public function test(
        WebhookSubscription $webhook,
        CurrentTenant $currentTenant,
        FeatureAccess $features,
        WebhookDispatcher $dispatcher,
    ): RedirectResponse {
        $tenant = $currentTenant->require();
        abort_unless($features->enabled($tenant, 'webhooks'), 403);
        abort_unless((int) $webhook->tenant_id === (int) $tenant->id, 404);

        $event = $webhook->events[0] ?? WebhookSubscription::EVENT_FEE_PAID;
        $dispatcher->deliver($tenant, $webhook, $event, [
            'test' => true,
            'message' => 'SME webhook test ping',
        ]);

        return back()->with('status', 'Test delivery stubbed.');
    }
}
