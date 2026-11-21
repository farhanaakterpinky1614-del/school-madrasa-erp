<?php

namespace Modules\Students\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'name_bn', 'phone', 'email', 'nid_no', 'occupation', 'address',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withPivot(['relationship', 'is_primary', 'is_emergency', 'can_pickup', 'tenant_id'])
            ->withTimestamps();
    }
}
