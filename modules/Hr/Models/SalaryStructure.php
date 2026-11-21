<?php

namespace Modules\Hr\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryStructure extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'basic_salary', 'house_rent', 'medical',
        'conveyance', 'other_allowance', 'deductions', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'house_rent' => 'decimal:2',
            'medical' => 'decimal:2',
            'conveyance' => 'decimal:2',
            'other_allowance' => 'decimal:2',
            'deductions' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function gross(): float
    {
        return (float) $this->basic_salary
            + (float) $this->house_rent
            + (float) $this->medical
            + (float) $this->conveyance
            + (float) $this->other_allowance;
    }

    public function net(): float
    {
        return $this->gross() - (float) $this->deductions;
    }

    public function staffSalaries(): HasMany
    {
        return $this->hasMany(StaffSalary::class);
    }
}
