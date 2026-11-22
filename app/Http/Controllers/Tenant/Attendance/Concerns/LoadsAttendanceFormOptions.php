<?php

namespace App\Http\Controllers\Tenant\Attendance\Concerns;

use App\Models\User;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\ClassRoutine;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Attendance\Services\TeacherAttendanceScope;

trait LoadsAttendanceFormOptions
{
    /**
     * @return array<string, mixed>
     */
    protected function attendanceFormOptions(?User $user = null): array
    {
        $user ??= request()->user();
        $scope = app(TeacherAttendanceScope::class);
        $scoped = $user && $scope->isScoped($user);
        $allowed = $scoped ? $scope->allowedClassSections($user) : null;

        $classes = SchoolClass::query()->orderBy('numeric_level')->get();
        $sections = Section::query()->orderBy('name')->get();

        if ($scoped && $allowed !== null) {
            $classIds = $allowed['class_ids'];
            $classes = $classes->whereIn('id', $classIds)->values();
            $sections = $sections->filter(function (Section $section) use ($allowed) {
                foreach ($allowed['pairs'] as $pair) {
                    if ($pair['class_id'] !== (int) $section->school_class_id) {
                        continue;
                    }
                    if ($pair['section_id'] === null || $pair['section_id'] === (int) $section->id) {
                        return true;
                    }
                }

                return false;
            })->values();
        }

        $routines = ClassRoutine::query()
            ->with(['schoolClass', 'section', 'period', 'subject', 'staff'])
            ->where('is_active', true)
            ->when($scoped && $allowed !== null, function ($q) use ($allowed, $scope, $user) {
                $q->whereIn('school_class_id', $allowed['class_ids'] ?: [0]);
                // Further filter in PHP after load for subject/staff nuance
            })
            ->orderBy('day_of_week')
            ->orderBy('period_id')
            ->get();

        if ($scoped && $user) {
            $routines = $routines->filter(fn (ClassRoutine $r) => $scope->canMarkRoutine($user, $r))->values();
        }

        return [
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => $classes,
            'sections' => $sections,
            'routines' => $routines,
            'statuses' => StudentAttendance::STATUSES,
            'scoped' => $scoped,
            'days' => [
                0 => __('ui.day_sun'),
                1 => __('ui.day_mon'),
                2 => __('ui.day_tue'),
                3 => __('ui.day_wed'),
                4 => __('ui.day_thu'),
                5 => __('ui.day_fri'),
                6 => __('ui.day_sat'),
            ],
        ];
    }
}
