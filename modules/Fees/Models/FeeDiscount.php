<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeDiscount extends Model
{
    use BelongsToTenant;

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    public const TYPE_SCHOLARSHIP = 'scholarship';

    public const TYPE_WAIVER = 'waiver';

    public const TYPES = [
        self::TYPE_PERCENT,
        self::TYPE_FIXED,
        self::TYPE_SCHOLARSHIP,
        self::TYPE_WAIVER,
    ];

    protected $fillable = [
        'tenant_id', 'fee_invoice_id', 'type', 'label', 'percent', 'amount', 'notes', 'applied_by',
    ];

    protected function casts(): array
    {
        return [
            'percent' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
