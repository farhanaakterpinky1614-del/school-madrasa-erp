<?php

namespace Modules\Hifz\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HifzProgressLog extends Model
{
    use BelongsToTenant;

    public const QUALITY_EXCELLENT = 'excellent';

    public const QUALITY_GOOD = 'good';

    public const QUALITY_AVERAGE = 'average';

    public const QUALITY_NEEDS_WORK = 'needs_work';

    public const QUALITIES = [
        self::QUALITY_EXCELLENT,
        self::QUALITY_GOOD,
        self::QUALITY_AVERAGE,
        self::QUALITY_NEEDS_WORK,
    ];

    protected $fillable = [
        'tenant_id', 'hifz_profile_id', 'logged_on',
        'from_juz', 'from_ayah', 'to_juz', 'to_ayah',
        'quality', 'teacher_notes', 'logged_by',
    ];

    protected function casts(): array
    {
        return [
            'logged_on' => 'date',
            'from_juz' => 'integer',
            'from_ayah' => 'integer',
            'to_juz' => 'integer',
            'to_ayah' => 'integer',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(HifzProfile::class, 'hifz_profile_id');
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function rangeLabel(): string
    {
        return sprintf(
            'Juz %d:%d → Juz %d:%d',
            $this->from_juz,
            $this->from_ayah,
            $this->to_juz,
            $this->to_ayah
        );
    }
}
