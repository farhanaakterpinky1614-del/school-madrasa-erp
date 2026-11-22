<?php

namespace Modules\Communications\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CommunicationSetting extends Model
{
    use BelongsToTenant;

    public const SMS_DRIVER_STUB = 'stub';

    public const SMS_DRIVER_LOG = 'log';

    public const SMS_DRIVER_SSL = 'ssl';

    public const SMS_DRIVER_BULK = 'bulk';

    public const SMS_DRIVERS = [
        self::SMS_DRIVER_STUB,
        self::SMS_DRIVER_LOG,
        self::SMS_DRIVER_SSL,
        self::SMS_DRIVER_BULK,
    ];

    public const EMAIL_DRIVER_STUB = 'stub';

    public const EMAIL_DRIVER_LOG = 'log';

    public const EMAIL_DRIVER_MAIL = 'mail';

    public const EMAIL_DRIVERS = [
        self::EMAIL_DRIVER_STUB,
        self::EMAIL_DRIVER_LOG,
        self::EMAIL_DRIVER_MAIL,
    ];

    protected $fillable = [
        'tenant_id', 'sms_driver', 'email_driver', 'sms_enabled', 'email_enabled', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sms_enabled' => 'boolean',
            'email_enabled' => 'boolean',
        ];
    }
}
