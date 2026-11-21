<?php

namespace Modules\Library\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryCopy extends Model
{
    use BelongsToTenant;

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_LOST = 'lost';

    public const STATUS_DAMAGED = 'damaged';

    public const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_ISSUED,
        self::STATUS_LOST,
        self::STATUS_DAMAGED,
    ];

    public const CONDITION_NEW = 'new';

    public const CONDITION_GOOD = 'good';

    public const CONDITION_FAIR = 'fair';

    public const CONDITION_POOR = 'poor';

    public const CONDITIONS = [
        self::CONDITION_NEW,
        self::CONDITION_GOOD,
        self::CONDITION_FAIR,
        self::CONDITION_POOR,
    ];

    protected $fillable = [
        'tenant_id', 'library_book_id', 'accession_no', 'barcode',
        'condition', 'status', 'shelf_location', 'notes',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'library_book_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(LibraryIssue::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }
}
