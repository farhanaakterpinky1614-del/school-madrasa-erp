<?php

namespace Modules\Hr\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'tenant_id', 'period_year', 'period_month', 'status',
        'processed_at', 'processed_by', 'notes',
    ];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function periodLabel(): string
    {
        return sprintf('%04d-%02d', $this->period_year, $this->period_month);
    }
}
