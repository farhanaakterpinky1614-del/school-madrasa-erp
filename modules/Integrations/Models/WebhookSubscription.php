<?php

namespace Modules\Integrations\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookSubscription extends Model
{
    use BelongsToTenant;

    public const EVENT_FEE_PAID = 'fee.paid';

    public const EVENT_ATTENDANCE_MARKED = 'attendance.marked';

    public const EVENT_ADMISSION_STATUS = 'admission.status_changed';

    public const EVENTS = [
        self::EVENT_FEE_PAID,
        self::EVENT_ATTENDANCE_MARKED,
        self::EVENT_ADMISSION_STATUS,
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'target_url',
        'secret',
        'events',
        'is_active',
        'last_delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'last_delivered_at' => 'datetime',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function listensFor(string $event): bool
    {
        return in_array($event, $this->events ?? [], true);
    }
}
