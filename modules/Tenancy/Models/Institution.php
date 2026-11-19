<?php

namespace Modules\Tenancy\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'name_bn',
        'name_ar',
        'institution_type',
        'email',
        'phone',
        'address',
        'city',
        'division',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }
}
