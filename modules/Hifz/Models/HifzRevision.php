<?php

namespace Modules\Hifz\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Hr\Models\Staff;

class HifzRevision extends Model
{
    use BelongsToTenant;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_MISSED = 'missed';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_MISSED,
    ];

    protected $fillable = [
        'tenant_id', 'hifz_profile_id', 'label', 'from_juz', 'to_juz',
        'scheduled_on', 'completed_on', 'status', 'assigned_teacher_id',
        'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'from_juz' => 'integer',
            'to_juz' => 'integer',
            'scheduled_on' => 'date',
            'completed_on' => 'date',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(HifzProfile::class, 'hifz_profile_id');
    }

    public function assignedTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_teacher_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rangeLabel(): string
    {
        return 'Juz/Para '.$this->from_juz.'–'.$this->to_juz;
    }
}
