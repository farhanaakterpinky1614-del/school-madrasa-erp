<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Students\Models\Student;

class FeeInvoice extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ISSUED,
        self::STATUS_PARTIAL,
        self::STATUS_PAID,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'tenant_id', 'student_id', 'fee_structure_id', 'invoice_number', 'title', 'status',
        'issue_date', 'due_date', 'subtotal', 'discount_total', 'total', 'paid_amount',
        'balance', 'currency', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(FeeDiscount::class)->orderByDesc('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class)->orderByDesc('paid_on')->orderByDesc('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ISSUED, self::STATUS_PARTIAL], true);
    }

    public function acceptsPayments(): bool
    {
        return in_array($this->status, [self::STATUS_ISSUED, self::STATUS_PARTIAL], true)
            && (float) $this->balance > 0;
    }

    public function acceptsDiscounts(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ISSUED, self::STATUS_PARTIAL], true)
            && (float) $this->paid_amount <= 0;
    }
}
