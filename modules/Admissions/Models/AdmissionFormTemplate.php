<?php

namespace Modules\Admissions\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionFormTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'description', 'is_default', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(AdmissionFormField::class)->orderBy('sort_order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AdmissionSession::class);
    }
}
