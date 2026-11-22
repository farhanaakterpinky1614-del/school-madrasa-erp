<?php

namespace Modules\Hifz\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Hr\Models\Staff;
use Modules\Students\Models\Student;

class HifzProfile extends Model
{
    use BelongsToTenant;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PAUSED = 'paused';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_PAUSED,
    ];

    /**
     * Progress bands for Muhtamim reports (by current juz/para).
     *
     * @return array<string, array{label: string, min: int, max: int}>
     */
    public static function progressBands(): array
    {
        return [
            'nazira_early' => ['label' => 'Juz 1–5', 'min' => 1, 'max' => 5],
            'mid' => ['label' => 'Juz 6–15', 'min' => 6, 'max' => 15],
            'advanced' => ['label' => 'Juz 16–25', 'min' => 16, 'max' => 25],
            'near_khatm' => ['label' => 'Juz 26–30', 'min' => 26, 'max' => 30],
        ];
    }

    protected $fillable = [
        'tenant_id', 'student_id', 'status', 'current_juz', 'current_ayah',
        'assigned_teacher_id', 'started_on', 'completed_on', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'current_juz' => 'integer',
            'current_ayah' => 'integer',
            'started_on' => 'date',
            'completed_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assignedTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_teacher_id');
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(HifzProgressLog::class)->orderByDesc('logged_on')->orderByDesc('id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(HifzRevision::class)->orderByDesc('scheduled_on')->orderByDesc('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progressBandKey(): string
    {
        $juz = (int) $this->current_juz;
        foreach (self::progressBands() as $key => $band) {
            if ($juz >= $band['min'] && $juz <= $band['max']) {
                return $key;
            }
        }

        return 'nazira_early';
    }

    public function positionLabel(): string
    {
        return 'Juz/Para '.$this->current_juz.', Ayah '.$this->current_ayah;
    }
}
