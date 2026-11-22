<?php

namespace Modules\Communications\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Models\SchoolClass;

class Announcement extends Model
{
    use BelongsToTenant;

    public const AUDIENCE_ALL = 'all';

    public const AUDIENCE_ROLES = 'roles';

    public const AUDIENCE_CLASS = 'class';

    public const AUDIENCE_TYPES = [
        self::AUDIENCE_ALL,
        self::AUDIENCE_ROLES,
        self::AUDIENCE_CLASS,
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_UNPUBLISHED = 'unpublished';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_UNPUBLISHED,
    ];

    protected $fillable = [
        'tenant_id', 'title', 'body', 'audience_type', 'audience_roles',
        'school_class_id', 'status', 'published_at', 'scheduled_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'audience_roles' => 'array',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isVisibleNow(): bool
    {
        if ($this->status !== self::STATUS_PUBLISHED) {
            return false;
        }

        if ($this->scheduled_at !== null && $this->scheduled_at->isFuture()) {
            return false;
        }

        return true;
    }
}
