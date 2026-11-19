<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SessionManager
{
    /**
     * @return Collection<int, object{id: string, ip_address: ?string, user_agent: ?string, last_activity: int}>
     */
    public function listForUser(int $userId): Collection
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity']);
    }

    public function revoke(string $sessionId, int $userId): bool
    {
        return DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function revokeOthers(int $userId, string $currentSessionId): int
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }

    public function revokeAllForUser(int $userId): int
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->delete();
    }
}
