<?php

namespace Modules\Settings\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class InstitutionSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'key', 'value',
    ];

    protected function casts(): array
    {
        // json (not array) — values may be scalars (locale) or nested arrays (branding).
        return ['value' => 'json'];
    }
}
