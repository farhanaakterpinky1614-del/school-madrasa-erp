<?php

namespace Modules\Library\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryFine extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_WAIVED = 'waived';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_WAIVED,
    ];

    protected $fillable = [
        'tenant_id', 'library_issue_id', 'amount', 'currency', 'status',
        'fine_mode', 'rate_applied', 'days_overdue', 'calculated_on',
        'settled_on', 'fees_note', 'notes', 'settled_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'rate_applied' => 'decimal:2',
            'days_overdue' => 'integer',
            'calculated_on' => 'date',
            'settled_on' => 'date',
        ];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(LibraryIssue::class, 'library_issue_id');
    }

    public function settler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
