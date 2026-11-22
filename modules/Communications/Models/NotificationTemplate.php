<?php

namespace Modules\Communications\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    use BelongsToTenant;

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNELS = [
        self::CHANNEL_SMS,
        self::CHANNEL_EMAIL,
    ];

    protected $fillable = [
        'tenant_id', 'name', 'code', 'channel', 'subject', 'body',
        'placeholders_help', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Replace {{placeholder}} tokens in subject/body.
     *
     * @param  array<string, string|int|float|null>  $vars
     */
    public function render(array $vars): array
    {
        $replace = [];
        foreach ($vars as $key => $value) {
            $replace['{{'.$key.'}}'] = (string) ($value ?? '');
        }

        return [
            'subject' => $this->subject !== null ? strtr($this->subject, $replace) : null,
            'body' => strtr($this->body, $replace),
        ];
    }
}
