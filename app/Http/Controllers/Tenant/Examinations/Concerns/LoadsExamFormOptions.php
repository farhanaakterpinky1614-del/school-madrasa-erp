<?php

namespace App\Http\Controllers\Tenant\Examinations\Concerns;

use App\Models\User;
use Modules\Academics\Models\AcademicTerm;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Subject;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Models\GradingScheme;
use Modules\Examinations\Services\TeacherExamScope;

trait LoadsExamFormOptions
{
    /**
     * @return array<string, mixed>
     */
    protected function examFormOptions(?User $user = null): array
    {
        $user ??= request()->user();
        $scope = app(TeacherExamScope::class);
        $scoped = $user && $scope->isScoped($user);
        $allowed = $scoped ? $scope->allowedScope($user) : null;

        $classes = SchoolClass::query()->orderBy('numeric_level')->get();
        if ($scoped && $allowed !== null) {
            $classes = $classes->whereIn('id', $allowed['class_ids'] ?: [0])->values();
        }

        return [
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'terms' => AcademicTerm::query()->orderBy('id')->get(),
            'classes' => $classes,
            'sections' => Section::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'schemes' => GradingScheme::query()->where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get(),
            'statuses' => Exam::STATUSES,
            'scoped' => $scoped,
            'allowed' => $allowed,
        ];
    }
}
