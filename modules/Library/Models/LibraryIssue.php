<?php

namespace Modules\Library\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Hr\Models\Staff;
use Modules\Students\Models\Student;

class LibraryIssue extends Model
{
    use BelongsToTenant;

    public const STATUS_ISSUED = 'issued';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_LOST = 'lost';

    public const STATUSES = [
        self::STATUS_ISSUED,
        self::STATUS_RETURNED,
        self::STATUS_LOST,
    ];

    public const BORROWER_STUDENT = 'student';

    public const BORROWER_STAFF = 'staff';

    public const BORROWER_TYPES = [
        self::BORROWER_STUDENT,
        self::BORROWER_STAFF,
    ];

    protected $fillable = [
        'tenant_id', 'library_copy_id', 'student_id', 'staff_id', 'borrower_type',
        'issued_on', 'due_on', 'returned_on', 'status', 'is_overdue', 'notes',
        'issued_by', 'returned_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'due_on' => 'date',
            'returned_on' => 'date',
            'is_overdue' => 'boolean',
        ];
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(LibraryCopy::class, 'library_copy_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function fines(): HasMany
    {
        return $this->hasMany(LibraryFine::class);
    }

    public function borrowerName(): string
    {
        if ($this->borrower_type === self::BORROWER_STAFF) {
            return $this->staff?->full_name ?? '—';
        }

        return $this->student?->full_name ?? '—';
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }
}
