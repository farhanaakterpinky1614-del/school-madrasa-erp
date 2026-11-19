<?php

namespace Modules\Tenancy\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TenantNote extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'body',
    ];
}
