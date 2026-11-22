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
     * Begin DNS TXT / HTTP challenge verification (Phase 16).
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
     * Mark verified after DomainVerificationService succeeds.
     */
    public function markVerified(): void
    {
        $this->forceFill([
            'verification_status' => self::STATUS_VERIFIED,
            'verified_at' => now(),
        ])->save();
    }

    /**
     * @deprecated Use DomainVerificationService::verify() — kept for backward-compatible routes.
     */
    public function markVerifiedStub(): void
    {
        $this->markVerified();
    }
}
