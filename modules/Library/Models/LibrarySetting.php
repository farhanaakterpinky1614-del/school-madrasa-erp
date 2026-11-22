<?php

namespace Modules\Library\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    use BelongsToTenant;

    public const FINE_MODE_DAILY = 'daily';

    public const FINE_MODE_FIXED = 'fixed';

    public const FINE_MODES = [
        self::FINE_MODE_DAILY,
        self::FINE_MODE_FIXED,
    ];

    protected $fillable = [
        'tenant_id', 'fine_mode', 'fine_daily_rate', 'fine_fixed_amount', 'default_loan_days',
    ];

    protected function casts(): array
    {
        return [
            'fine_daily_rate' => 'decimal:2',
            'fine_fixed_amount' => 'decimal:2',
            'default_loan_days' => 'integer',
        ];
    }
}
