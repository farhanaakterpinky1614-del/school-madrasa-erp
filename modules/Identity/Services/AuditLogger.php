<?php

namespace Modules\Identity\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Identity\Models\AuditLog;

class AuditLogger
{
    public function __construct(
        protected CurrentTenant $currentTenant,
    ) {}

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public function log(
        string $action,
        ?Model $auditable = null,
        ?array $old = null,
        ?array $new = null,
        ?string $description = null,
        ?int $tenantId = null,
    ): AuditLog {
        $request = request();

        return AuditLog::queryWithoutTenantScope()->create([
            'tenant_id' => $tenantId ?? $this->currentTenant->id() ?? Auth::user()?->tenant_id,
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'description' => $description,
        ]);
    }
}
