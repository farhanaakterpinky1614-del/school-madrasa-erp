<?php

namespace Modules\Identity\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Tenancy\Models\Tenant;

class LoginHistory extends Model
{
    use BelongsToTenant;

    public const EVENT_SUCCESS = 'success';

    public const EVENT_FAILED = 'failed';

    public const EVENT_LOGOUT = 'logout';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'email',
        'event',
        'ip_address',
        'user_agent',
        'guard',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
