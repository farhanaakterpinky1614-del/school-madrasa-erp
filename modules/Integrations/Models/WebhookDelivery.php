<?php

namespace Modules\Integrations\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';

    public const STATUS_STUBBED = 'stubbed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'webhook_subscription_id',
        'event',
        'payload',
        'status',
        'attempt',
        'response_code',
        'response_body',
        'error',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'delivered_at' => 'datetime',
            'attempt' => 'integer',
            'response_code' => 'integer',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class, 'webhook_subscription_id');
    }
}
