<?php

namespace Modules\Communications\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use BelongsToTenant;

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_IN_APP = 'in_app';

    public const CHANNELS = [
        self::CHANNEL_SMS,
        self::CHANNEL_EMAIL,
        self::CHANNEL_IN_APP,
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_STUBBED = 'stubbed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_SENT,
        self::STATUS_FAILED,
        self::STATUS_STUBBED,
    ];

    protected $fillable = [
        'tenant_id', 'notification_template_id', 'channel', 'recipient',
        'recipient_user_id', 'subject', 'body', 'status', 'driver',
        'error_message', 'meta', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
