<?php

namespace Modules\Reports\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class IdCardTemplate extends Model
{
    use BelongsToTenant;

    public const TYPE_STUDENT = 'student';

    public const TYPE_STAFF = 'staff';

    public const TYPES = [
        self::TYPE_STUDENT,
        self::TYPE_STAFF,
    ];

    protected $fillable = [
        'tenant_id', 'name', 'card_type', 'show_photo', 'show_qr',
        'primary_color', 'secondary_color', 'footer_text', 'is_default', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'show_photo' => 'boolean',
            'show_qr' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
