<?php

namespace Modules\Hr\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use BelongsToTenant;

    public const STATUS_GENERATED = 'generated';

    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'tenant_id', 'payroll_run_id', 'staff_id', 'basic', 'allowances',
        'deductions', 'net_pay', 'status',
    ];

    protected function casts(): array
    {
        return [
            'basic' => 'decimal:2',
            'allowances' => 'decimal:2',
            'deductions' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
