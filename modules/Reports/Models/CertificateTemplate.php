<?php

namespace Modules\Reports\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    use BelongsToTenant;

    public const TYPE_TRANSFER = 'transfer';

    public const TYPE_COMPLETION = 'completion';

    public const TYPE_CHARACTER = 'character';

    public const TYPES = [
        self::TYPE_TRANSFER,
        self::TYPE_COMPLETION,
        self::TYPE_CHARACTER,
    ];

    protected $fillable = [
        'tenant_id', 'name', 'code', 'certificate_type', 'title', 'body',
        'seal_label', 'signature_label', 'show_qr', 'is_active', 'placeholders_help',
    ];

    protected function casts(): array
    {
        return [
            'show_qr' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function issues(): HasMany
    {
        return $this->hasMany(CertificateIssue::class);
    }
}
