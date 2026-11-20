<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Identity\Models\BranchPermission;
use Modules\Identity\Models\ClassPermission;
use Modules\Identity\Models\LoginHistory;
use Modules\Identity\Models\TenantMembership;
use Modules\Tenancy\Models\Tenant;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const TYPE_PLATFORM_ADMIN = 'platform_admin';

    public const TYPE_TENANT_ADMIN = 'tenant_admin';

    public const TYPE_OWNER = 'owner';

    public const TYPE_STAFF = 'staff';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'is_platform_admin',
        'user_type',
        'is_active',
        'last_login_at',
        'password_changed_at',
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
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(TenantMembership::class);
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function branchPermissions(): HasMany
    {
        return $this->hasMany(BranchPermission::class);
    }

    public function classPermissions(): HasMany
    {
        return $this->hasMany(ClassPermission::class);
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

    /**
     * Branch permission foundation helper (Phase 04+ will pass real branch ids).
     */
    public function hasBranchPermission(string $permission, ?int $branchId = null): bool
    {
        if ($this->isTenantAdmin() || $this->can($permission)) {
            return true;
        }

        $query = $this->branchPermissions()->where('permission', $permission);

        if ($branchId !== null) {
            $query->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });
        }

        return $query->exists();
    }

    /**
     * Class permission foundation helper (Teacher own-scope for Phase 04+).
     */
    public function hasClassPermission(string $permission, ?int $classId = null): bool
    {
        if ($this->isTenantAdmin() || $this->can($permission)) {
            return true;
        }

        $query = $this->classPermissions()->where('permission', $permission);

        if ($classId !== null) {
            $query->where(function ($q) use ($classId) {
                $q->whereNull('class_id')->orWhere('class_id', $classId);
            });
        }

        return $query->exists();
    }
}
