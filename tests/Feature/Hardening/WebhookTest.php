<?php

use Modules\Integrations\Models\WebhookDelivery;
use Modules\Integrations\Models\WebhookSubscription;
use Modules\Integrations\Services\WebhookDispatcher;

it('isolates webhook subscriptions by tenant', function () {
    $a = $this->registerTenant(['slug' => 'wh-a', 'plan_code' => 'professional', 'admin_email' => 'wha@test.test']);
    $b = $this->registerTenant(['slug' => 'wh-b', 'plan_code' => 'professional', 'admin_email' => 'whb@test.test']);

    $this->bindTenant($a['tenant']);
    WebhookSubscription::query()->create([
        'name' => 'A hook',
        'target_url' => 'https://a.example/hook',
        'events' => [WebhookSubscription::EVENT_FEE_PAID],
        'is_active' => true,
    ]);

    $this->bindTenant($b['tenant']);
    expect(WebhookSubscription::query()->count())->toBe(0);

    $this->bindTenant($a['tenant']);
    expect(WebhookSubscription::query()->count())->toBe(1);
});

it('stubs webhook delivery for subscribed events', function () {
    $registered = $this->registerTenant(['slug' => 'wh-del', 'plan_code' => 'professional', 'admin_email' => 'whdel@test.test']);
    $this->bindTenant($registered['tenant']);

    $sub = WebhookSubscription::query()->create([
        'name' => 'Fee hook',
        'target_url' => 'https://hooks.example/fee',
        'events' => [WebhookSubscription::EVENT_FEE_PAID],
        'is_active' => true,
        'secret' => 'sec',
    ]);

    $deliveries = app(WebhookDispatcher::class)->dispatch(
        $registered['tenant'],
        WebhookSubscription::EVENT_FEE_PAID,
        ['amount' => 100]
    );

    expect($deliveries)->toHaveCount(1)
        ->and($deliveries[0]->status)->toBe(WebhookDelivery::STATUS_STUBBED)
        ->and($deliveries[0]->webhook_subscription_id)->toBe($sub->id);
});

it('institution admin can open webhooks ui', function () {
    $registered = $this->registerTenant(['slug' => 'wh-ui', 'plan_code' => 'professional', 'admin_email' => 'whui@test.test']);

    $this->actingAs($registered['user'])
        ->withTenantHost('wh-ui')
        ->get(route('tenant.webhooks.index'))
        ->assertOk();
});

it('starter plan cannot manage webhooks', function () {
    $registered = $this->registerTenant(['slug' => 'wh-starter', 'plan_code' => 'starter']);

    $this->actingAs($registered['user'])
        ->withTenantHost('wh-starter')
        ->post(route('tenant.webhooks.store'), [
            'name' => 'Nope',
            'target_url' => 'https://example.com/h',
            'events' => [WebhookSubscription::EVENT_FEE_PAID],
        ])
        ->assertForbidden();
});
