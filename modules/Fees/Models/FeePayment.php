<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    use BelongsToTenant;

    public const METHOD_CASH = 'cash';

    public const METHOD_BANK = 'bank';

    public const METHOD_BKASH = 'bkash';

    public const METHOD_NAGAD = 'nagad';

    public const METHODS = [
        self::METHOD_CASH,
        self::METHOD_BANK,
        self::METHOD_BKASH,
        self::METHOD_NAGAD,
    ];

    protected $fillable = [
        'tenant_id', 'fee_invoice_id', 'payment_number', 'amount', 'method',
        'paid_on', 'reference', 'notes', 'received_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
