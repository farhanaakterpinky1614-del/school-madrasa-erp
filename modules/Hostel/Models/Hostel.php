<?php

namespace Modules\Hostel\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Fees\Models\FeeCategory;
use Modules\Fees\Models\FeeStructure;

class Hostel extends Model
{
    use BelongsToTenant;

    public const GENDER_MALE = 'male';

    public const GENDER_FEMALE = 'female';

    public const GENDER_MIXED = 'mixed';

    public const GENDER_TYPES = [
        self::GENDER_MALE,
        self::GENDER_FEMALE,
        self::GENDER_MIXED,
    ];

    protected $fillable = [
        'tenant_id', 'name', 'code', 'gender_type', 'capacity_notes',
        'monthly_fee_amount', 'currency', 'fee_category_id', 'fee_structure_id',
        'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'monthly_fee_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(HostelRoom::class);
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }
}
