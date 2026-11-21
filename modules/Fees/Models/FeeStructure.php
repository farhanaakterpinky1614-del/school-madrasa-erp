<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;

class FeeStructure extends Model
{
    use BelongsToTenant;

    public const FREQ_MONTHLY = 'monthly';

    public const FREQ_TERM = 'term';

    public const FREQ_ANNUAL = 'annual';

    public const FREQ_ONE_TIME = 'one_time';

    public const FREQUENCIES = [
        self::FREQ_MONTHLY,
        self::FREQ_TERM,
        self::FREQ_ANNUAL,
        self::FREQ_ONE_TIME,
    ];

    protected $fillable = [
        'tenant_id', 'fee_category_id', 'academic_year_id', 'school_class_id',
        'name', 'amount', 'frequency', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }
}
