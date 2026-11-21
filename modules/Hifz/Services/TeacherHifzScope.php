<?php

namespace Modules\Hifz\Services;

use App\Models\User;
use Modules\Hifz\Models\HifzProfile;
use Modules\Hr\Models\Staff;

/**
 * Resolves ustaz/teacher own-scope for Hifz profiles.
 * Broad roles (admins / Muhtamim with hifz.profile.manage) are not scoped.
 */
class TeacherHifzScope
{
    public function isScoped(User $user): bool
    {
        return $user->can('hifz.progress.update')
            && ! $user->can('hifz.profile.manage');
    }

    public function staffFor(User $user): ?Staff
    {
        return Staff::query()
            ->where('user_id', $user->id)
            ->where('status', Staff::STATUS_ACTIVE)
            ->first();
    }

    public function canAccessProfile(User $user, HifzProfile $profile): bool
    {
        if (! $this->isScoped($user)) {
            return true;
        }

        $staff = $this->staffFor($user);
        if ($staff === null) {
            return false;
        }

        return (int) $profile->assigned_teacher_id === (int) $staff->id;
    }

    /**
     * Apply assigned-teacher filter when the user is scoped.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Modules\Hifz\Models\HifzProfile>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Hifz\Models\HifzProfile>
     */
    public function constrainProfiles($query, User $user)
    {
        if (! $this->isScoped($user)) {
            return $query;
        }

        $staff = $this->staffFor($user);
        if ($staff === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('assigned_teacher_id', $staff->id);
    }
}
