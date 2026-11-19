<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Tenancy\Models\Tenant;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const TYPE_PLATFORM_ADMIN = 'platform_admin';

    public const TYPE_TENANT_ADMIN = 'tenant_admin';

    public const TYPE_OWNER = 'owner';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'is_platform_admin',
        'user_type',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    public function isTenantAdmin(): bool
    {
        return ! $this->is_platform_admin
            && in_array($this->user_type, [self::TYPE_TENANT_ADMIN, self::TYPE_OWNER], true);
    }
}
