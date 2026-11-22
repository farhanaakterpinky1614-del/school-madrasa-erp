<?php

namespace Modules\Hostel\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostelRoom extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'hostel_id', 'room_number', 'floor', 'bed_count',
        'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(HostelBed::class);
    }

    public function occupiedBedsCount(): int
    {
        return $this->beds()->where('status', HostelBed::STATUS_OCCUPIED)->count();
    }
}
