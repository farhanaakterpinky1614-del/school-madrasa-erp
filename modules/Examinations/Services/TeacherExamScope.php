<?php

namespace Modules\Examinations\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Models\ExamSubject;
use Modules\Hr\Models\Staff;
use Modules\Hr\Models\TeacherAssignment;

/**
 * Resolves teacher own-scope for marks entry.
 * Broad markers (admins / officers with exams.publish.manage) are not scoped.
 */
class TeacherExamScope
{
    public function isScoped(User $user): bool
    {
        return $user->can('exams.marks.manage')
            && ! $user->can('exams.publish.manage');
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
     * @return array{class_ids: list<int>, subject_ids: list<int>, pairs: list<array{class_id: int, section_id: int|null, subject_id: int|null}>}
     */
    public function allowedScope(User $user): array
    {
        $assignments = $this->assignmentsFor($user);
        $classIds = $assignments->pluck('school_class_id')->unique()->values()->all();
        $subjectIds = $assignments->pluck('subject_id')->filter()->unique()->values()->all();
        $pairs = $assignments->map(fn (TeacherAssignment $a) => [
            'class_id' => (int) $a->school_class_id,
            'section_id' => $a->section_id !== null ? (int) $a->section_id : null,
            'subject_id' => $a->subject_id !== null ? (int) $a->subject_id : null,
        ])->unique(fn ($p) => $p['class_id'].':'.($p['section_id'] ?? 'null').':'.($p['subject_id'] ?? 'null'))
            ->values()
            ->all();

        return [
            'class_ids' => $classIds,
            'subject_ids' => $subjectIds,
            'pairs' => $pairs,
        ];
    }

    public function canAccessExam(User $user, Exam $exam): bool
    {
        if (! $this->isScoped($user)) {
            return true;
        }

        $assignments = $this->assignmentsFor($user);
        if ($assignments->isEmpty()) {
            return false;
        }

        return $assignments->contains(
            fn (TeacherAssignment $a) => (int) $a->school_class_id === (int) $exam->school_class_id
        );
    }

    public function canEnterMarks(User $user, Exam $exam, ExamSubject $examSubject, ?int $sectionId): bool
    {
        if (! $this->isScoped($user)) {
            return true;
        }

        if (! $this->canAccessExam($user, $exam)) {
            return false;
        }

        $assignments = $this->assignmentsFor($user);
        if ($assignments->isEmpty()) {
            return false;
        }

        return $assignments->contains(function (TeacherAssignment $a) use ($exam, $examSubject, $sectionId) {
            if ((int) $a->school_class_id !== (int) $exam->school_class_id) {
                return false;
            }
            if ($a->section_id !== null && $sectionId !== null
                && (int) $a->section_id !== $sectionId) {
                return false;
            }
            if ($a->subject_id !== null
                && (int) $a->subject_id !== (int) $examSubject->subject_id) {
                return false;
            }

            return true;
        });
    }
}
