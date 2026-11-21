<?php

namespace App\Http\Controllers\Tenant\Hifz\Concerns;

use App\Models\User;
use Modules\Hifz\Models\HifzProfile;
use Modules\Hifz\Models\HifzProgressLog;
use Modules\Hifz\Models\HifzRevision;
use Modules\Hifz\Services\TeacherHifzScope;
use Modules\Hr\Models\Staff;
use Modules\Students\Models\Student;

trait LoadsHifzFormOptions
{
    /**
     * @return array<string, mixed>
     */
    protected function hifzFormOptions(?User $user = null): array
    {
        $user ??= request()->user();
        $scope = app(TeacherHifzScope::class);
        $scoped = $user ? $scope->isScoped($user) : false;

        $studentsQuery = Student::query()
            ->where('status', Student::STATUS_ACTIVE)
            ->orderBy('first_name')
            ->orderBy('last_name');

        $teachers = Staff::query()
            ->teachers()
            ->where('status', Staff::STATUS_ACTIVE)
            ->orderBy('first_name')
            ->get();

        return [
            'statuses' => HifzProfile::STATUSES,
            'qualities' => HifzProgressLog::QUALITIES,
            'revisionStatuses' => HifzRevision::STATUSES,
            'progressBands' => HifzProfile::progressBands(),
            'students' => $studentsQuery->get(),
            'teachers' => $teachers,
            'juzOptions' => range(1, 30),
            'scoped' => $scoped,
            'scopeStaff' => $user && $scoped ? $scope->staffFor($user) : null,
        ];
    }
}
