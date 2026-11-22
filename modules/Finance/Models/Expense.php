<?php

namespace Modules\Finance\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Fees\Models\FeePayment;

class Expense extends Model
{
    use BelongsToTenant;

    public const METHODS = FeePayment::METHODS;

    protected $fillable = [
        'tenant_id', 'expense_category_id', 'title', 'amount', 'spent_on',
        'payment_method', 'reference', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_on' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
