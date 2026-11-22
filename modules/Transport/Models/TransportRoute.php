<?php

namespace Modules\Transport\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Fees\Models\FeeCategory;
use Modules\Fees\Models\FeeStructure;

class TransportRoute extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'stops', 'vehicle_id', 'monthly_fee_amount',
        'currency', 'fee_category_id', 'fee_structure_id', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'monthly_fee_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return list<string>
     */
    public function stopList(): array
    {
        $raw = trim((string) $this->stops);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map(
                static fn ($item) => trim((string) $item),
                $decoded
            ), static fn ($item) => $item !== ''));
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/[\n,]+/', $raw) ?: []
        ), static fn ($item) => $item !== ''));
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TransportAssignment::class);
    }
}
