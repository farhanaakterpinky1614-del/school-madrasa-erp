<?php

namespace Modules\Identity\Services;

use App\Models\User;
use Modules\Identity\Models\LoginHistory;

class LoginHistoryRecorder
{
    public function success(User $user, string $guard = 'web'): LoginHistory
    {
        return $this->record(
            event: LoginHistory::EVENT_SUCCESS,
            user: $user,
            email: $user->email,
            tenantId: $user->tenant_id,
            guard: $guard,
        );
    }

    public function failed(?string $email, ?int $tenantId = null, string $guard = 'web'): LoginHistory
    {
        return $this->record(
            event: LoginHistory::EVENT_FAILED,
            user: null,
            email: $email,
            tenantId: $tenantId,
            guard: $guard,
        );
    }

    public function logout(User $user, string $guard = 'web'): LoginHistory
    {
        return $this->record(
            event: LoginHistory::EVENT_LOGOUT,
            user: $user,
            email: $user->email,
            tenantId: $user->tenant_id,
            guard: $guard,
        );
    }

    protected function record(
        string $event,
        ?User $user,
        ?string $email,
        ?int $tenantId,
        string $guard,
    ): LoginHistory {
        $request = request();

        return LoginHistory::queryWithoutTenantScope()->create([
            'tenant_id' => $tenantId,
            'user_id' => $user?->id,
            'email' => $email,
            'event' => $event,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'guard' => $guard,
        ]);
    }
}
