<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\ClassRoutine;
use Modules\Hr\Models\Staff;

class TeacherRoutineController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(Request $request): View
    {
        $staffId = $request->integer('staff_id') ?: null;
        $teachers = Staff::query()->teachers()->where('status', Staff::STATUS_ACTIVE)->orderBy('first_name')->get();

        $routines = ClassRoutine::query()
            ->with(['schoolClass', 'section', 'period', 'subject', 'room', 'staff'])
            ->when($staffId, fn ($q) => $q->where('staff_id', $staffId))
            ->whereNotNull('staff_id')
            ->orderBy('day_of_week')
            ->orderBy('period_id')
            ->paginate(40)
            ->withQueryString();

        return view('tenant.academics.teacher-routines.index', array_merge(
            $this->academicFormOptions(),
            compact('routines', 'teachers', 'staffId')
        ));
    }
}
