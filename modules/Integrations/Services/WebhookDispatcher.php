<?php

namespace Modules\Integrations\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Integrations\Models\WebhookDelivery;
use Modules\Integrations\Models\WebhookSubscription;
use Modules\Platform\Services\FeatureAccess;
use Modules\Tenancy\Models\Tenant;

/**
 * Outbound webhook delivery stubs — records deliveries; optional HTTP POST when configured.
 */
class WebhookDispatcher
{
    public function __construct(
        protected FeatureAccess $features,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return list<WebhookDelivery>
     */
    public function dispatch(Tenant $tenant, string $event, array $payload): array
    {
        if (! $this->features->enabled($tenant, 'webhooks')) {
            return [];
        }

        $subs = WebhookSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get()
            ->filter(fn (WebhookSubscription $sub) => $sub->listensFor($event));

        $deliveries = [];

        foreach ($subs as $sub) {
            $deliveries[] = $this->deliver($tenant, $sub, $event, $payload);
        }

        return $deliveries;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function deliver(Tenant $tenant, WebhookSubscription $sub, string $event, array $payload): WebhookDelivery
    {
        $body = [
            'event' => $event,
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'occurred_at' => now()->toIso8601String(),
            'data' => $payload,
        ];

        $delivery = WebhookDelivery::query()->create([
            'tenant_id' => $tenant->id,
            'webhook_subscription_id' => $sub->id,
            'event' => $event,
            'payload' => $body,
            'status' => WebhookDelivery::STATUS_PENDING,
            'attempt' => 1,
        ]);

        $mode = (string) config('sme.webhooks.delivery_mode', 'stub');

        try {
            if ($mode === 'http') {
                $headers = [
                    'Content-Type' => 'application/json',
                    'X-SME-Event' => $event,
                    'X-SME-Delivery' => (string) $delivery->id,
                ];

                if ($sub->secret) {
                    $headers['X-SME-Signature'] = hash_hmac('sha256', json_encode($body), $sub->secret);
                }

                $response = Http::timeout((int) config('sme.webhooks.timeout_seconds', 5))
                    ->withHeaders($headers)
                    ->post($sub->target_url, $body);

                $delivery->forceFill([
                    'status' => $response->successful() ? WebhookDelivery::STATUS_STUBBED : WebhookDelivery::STATUS_FAILED,
                    'response_code' => $response->status(),
                    'response_body' => mb_substr($response->body(), 0, 2000),
                    'error' => $response->successful() ? null : 'Non-2xx response',
                    'delivered_at' => now(),
                ])->save();
            } else {
                // Default stub: log + mark stubbed (no live HTTP required for demo/CI).
                Log::info('webhook.stub', [
                    'delivery_id' => $delivery->id,
                    'url' => $sub->target_url,
                    'event' => $event,
                ]);

                $delivery->forceFill([
                    'status' => WebhookDelivery::STATUS_STUBBED,
                    'response_code' => 200,
                    'response_body' => 'stubbed',
                    'delivered_at' => now(),
                ])->save();
            }

            $sub->forceFill(['last_delivered_at' => now()])->save();
        } catch (\Throwable $e) {
            $delivery->forceFill([
                'status' => WebhookDelivery::STATUS_FAILED,
                'error' => mb_substr($e->getMessage(), 0, 500),
                'delivered_at' => now(),
            ])->save();
        }

        return $delivery->fresh();
    }
}
