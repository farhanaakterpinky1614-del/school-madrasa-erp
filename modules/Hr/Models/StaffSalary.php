<?php

namespace Modules\Hr\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffSalary extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'staff_id', 'salary_structure_id', 'effective_from',
        'basic_override', 'is_current',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'basic_override' => 'decimal:2',
            'is_current' => 'boolean',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }
}
