<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeCategory extends Model
{
    use BelongsToTenant;

    public const KIND_TUITION = 'tuition';

    public const KIND_EXAM = 'exam';

    public const KIND_HOSTEL = 'hostel';

    public const KIND_TRANSPORT = 'transport';

    public const KIND_MISC = 'misc';

    public const KINDS = [
        self::KIND_TUITION,
        self::KIND_EXAM,
        self::KIND_HOSTEL,
        self::KIND_TRANSPORT,
        self::KIND_MISC,
    ];

    protected $fillable = [
        'tenant_id', 'name', 'code', 'kind', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function structures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }
}
