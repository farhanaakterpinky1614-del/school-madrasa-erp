<?php

namespace Modules\Communications\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Communications\Models\Announcement;

class AnnouncementService
{
    public function publish(Announcement $announcement): Announcement
    {
        $announcement->update([
            'status' => Announcement::STATUS_PUBLISHED,
            'published_at' => $announcement->published_at ?? now(),
        ]);

        return $announcement->fresh();
    }

    public function unpublish(Announcement $announcement): Announcement
    {
        $announcement->update([
            'status' => Announcement::STATUS_UNPUBLISHED,
        ]);

        return $announcement->fresh();
    }

    /**
     * Published announcements visible to the given user (inbox foundation).
     *
     * @return Builder<Announcement>
     */
    public function inboxQueryFor(User $user): Builder
    {
        $roleNames = $user->getRoleNames()->all();

        return Announcement::query()
            ->where('status', Announcement::STATUS_PUBLISHED)
            ->where(function (Builder $q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->where(function (Builder $q) use ($roleNames) {
                $q->where('audience_type', Announcement::AUDIENCE_ALL)
                    ->orWhere(function (Builder $inner) use ($roleNames) {
                        $inner->where('audience_type', Announcement::AUDIENCE_ROLES);
                        if ($roleNames === []) {
                            $inner->whereRaw('0 = 1');

                            return;
                        }
                        $inner->where(function (Builder $rolesQ) use ($roleNames) {
                            foreach ($roleNames as $role) {
                                $rolesQ->orWhereJsonContains('audience_roles', $role);
                            }
                        });
                    })
                    // Class-targeted notices still appear in staff inbox foundation
                    // (parent/student class filtering lands with portals later).
                    ->orWhere('audience_type', Announcement::AUDIENCE_CLASS);
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * @return Collection<int, Announcement>
     */
    public function inboxFor(User $user, int $limit = 50): Collection
    {
        return $this->inboxQueryFor($user)->limit($limit)->get();
    }
}
