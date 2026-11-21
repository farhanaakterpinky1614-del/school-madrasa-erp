<?php

namespace Modules\Attendance\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Models\ClassRoutine;

class AttendanceQrToken extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'class_routine_id', 'token', 'valid_on', 'expires_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'valid_on' => 'date',
            'expires_at' => 'datetime',
        ];
    }

    public function classRoutine(): BelongsTo
    {
        return $this->belongsTo(ClassRoutine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
