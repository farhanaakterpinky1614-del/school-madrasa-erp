<?php

namespace Modules\Tenancy\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantDomain extends Model
{
    use BelongsToTenant;

    public const TYPE_SUBDOMAIN = 'subdomain';

    public const TYPE_CUSTOM = 'custom';

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'host',
        'type',
        'is_primary',
        'verification_status',
        'verification_token',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function isVerified(): bool
    {
        return $this->verification_status === self::STATUS_VERIFIED;
    }

    /**
     * Stub for Phase 15 DNS/HTTP verification.
     * TODO(Phase 15): verify DNS TXT or HTTP challenge before marking custom domains verified.
     */
    public function beginVerification(): void
    {
        $this->forceFill([
            'verification_status' => self::STATUS_PENDING,
            'verification_token' => Str::random(40),
            'verified_at' => null,
        ])->save();
    }

    /**
     * Dev/local stub activation — production must complete DNS verification first.
     * TODO(Phase 15): remove stub path; only activate after real verification.
     */
    public function markVerifiedStub(): void
    {
        $this->forceFill([
            'verification_status' => self::STATUS_VERIFIED,
            'verified_at' => now(),
        ])->save();
    }
}
