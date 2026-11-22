<?php

namespace Modules\Library\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBook extends Model
{
    use BelongsToTenant;

    public const LANG_EN = 'en';

    public const LANG_BN = 'bn';

    public const LANG_AR = 'ar';

    public const LANGUAGES = [
        self::LANG_EN,
        self::LANG_BN,
        self::LANG_AR,
    ];

    protected $fillable = [
        'tenant_id', 'title', 'author', 'isbn', 'category', 'subject_tags',
        'publisher', 'language', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function copies(): HasMany
    {
        return $this->hasMany(LibraryCopy::class);
    }

    public function availableCopiesCount(): int
    {
        return $this->copies()->where('status', LibraryCopy::STATUS_AVAILABLE)->count();
    }
}
