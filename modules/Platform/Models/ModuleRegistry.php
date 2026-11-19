<?php

namespace Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRegistry extends Model
{
    protected $table = 'module_registry';

    protected $fillable = [
        'key',
        'name',
        'description',
        'feature_key',
        'is_core',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
