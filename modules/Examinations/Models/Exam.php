<?php

namespace Modules\Examinations\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Models\AcademicTerm;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;

class Exam extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_ONGOING = 'ongoing';

    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_ONGOING,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'tenant_id', 'name', 'code', 'academic_year_id', 'academic_term_id', 'school_class_id',
        'grading_scheme_id', 'starts_on', 'ends_on', 'status', 'board_name', 'board_roll_prefix',
        'board_meta', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'board_meta' => 'array',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function gradingScheme(): BelongsTo
    {
        return $this->belongsTo(GradingScheme::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(ExamSubject::class)->orderBy('sort_order');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(ResultPublication::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublished(?int $sectionId = null): bool
    {
        $query = $this->publications()->whereNotNull('published_at');

        if ($sectionId !== null) {
            return $query->where(function ($q) use ($sectionId) {
                $q->whereNull('section_id')->orWhere('section_id', $sectionId);
            })->exists();
        }

        return $query->whereNull('section_id')->exists()
            || $query->exists();
    }
}
