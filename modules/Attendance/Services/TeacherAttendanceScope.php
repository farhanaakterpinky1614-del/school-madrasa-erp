<?php

namespace Modules\Attendance\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Academics\Models\ClassRoutine;
use Modules\Hr\Models\Staff;
use Modules\Hr\Models\TeacherAssignment;

/**
 * Resolves teacher own-scope for attendance marking.
 * Broad markers (admins / officers with report.view) are not scoped.
 */
class TeacherAttendanceScope
{
    public function isScoped(User $user): bool
    {
        return $user->can('attendance.daily.manage')
            && ! $user->can('attendance.report.view');
    }

    public function staffFor(User $user): ?Staff
    {
        return Staff::query()
            ->where('user_id', $user->id)
            ->where('status', Staff::STATUS_ACTIVE)
            ->first();
    }

    /**
     * @return Collection<int, TeacherAssignment>
     */
    public function assignmentsFor(User $user): Collection
    {
        $staff = $this->staffFor($user);
        if ($staff === null) {
            return collect();
        }

        return TeacherAssignment::query()
            ->where('staff_id', $staff->id)
            ->where('is_active', true)
            ->get();
    }

    /**
     * @return array{class_ids: list<int>, pairs: list<array{class_id: int, section_id: int|null}>}
     */
    public function allowedClassSections(User $user): array
    {
        $assignments = $this->assignmentsFor($user);
        $classIds = $assignments->pluck('school_class_id')->unique()->values()->all();
        $pairs = $assignments->map(fn (TeacherAssignment $a) => [
            'class_id' => (int) $a->school_class_id,
            'section_id' => $a->section_id !== null ? (int) $a->section_id : null,
        ])->unique(fn ($p) => $p['class_id'].':'.($p['section_id'] ?? 'null'))->values()->all();

        return ['class_ids' => $classIds, 'pairs' => $pairs];
    }

    public function canMarkClassSection(User $user, int $classId, ?int $sectionId): bool
    {
        if (! $this->isScoped($user)) {
            return true;
        }

        $assignments = $this->assignmentsFor($user);
        if ($assignments->isEmpty()) {
            return false;
        }

        return $assignments->contains(function (TeacherAssignment $a) use ($classId, $sectionId) {
            if ((int) $a->school_class_id !== $classId) {
                return false;
            }
            // Null section on assignment means whole class
            if ($a->section_id === null) {
                return true;
            }

            return $sectionId !== null && (int) $a->section_id === $sectionId;
        });
    }

    public function canMarkRoutine(User $user, ClassRoutine $routine): bool
    {
        if (! $this->isScoped($user)) {
            return true;
        }

        $staff = $this->staffFor($user);
        if ($staff === null) {
            return false;
        }

        if ($routine->staff_id !== null && (int) $routine->staff_id === (int) $staff->id) {
            return true;
        }

        $assignments = $this->assignmentsFor($user);

        return $assignments->contains(function (TeacherAssignment $a) use ($routine) {
            if ((int) $a->school_class_id !== (int) $routine->school_class_id) {
                return false;
            }
            if ($a->section_id !== null && $routine->section_id !== null
                && (int) $a->section_id !== (int) $routine->section_id) {
                return false;
            }
            if ($a->subject_id !== null && $routine->subject_id !== null
                && (int) $a->subject_id !== (int) $routine->subject_id) {
                return false;
            }

            return true;
        });
    }
}
