<?php

namespace Modules\Transport\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use BelongsToTenant;

    public const TYPE_BUS = 'bus';

    public const TYPE_VAN = 'van';

    public const TYPES = [
        self::TYPE_BUS,
        self::TYPE_VAN,
    ];

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_MAINTENANCE,
    ];

    protected $fillable = [
        'tenant_id', 'plate_number', 'type', 'capacity', 'driver_name',
        'driver_phone', 'status', 'notes',
    ];

    public function routes(): HasMany
    {
        return $this->hasMany(TransportRoute::class);
    }
}
