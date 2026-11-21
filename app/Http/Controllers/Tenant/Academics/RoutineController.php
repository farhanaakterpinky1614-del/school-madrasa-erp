<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\ClassRoutine;
use Modules\Hr\Models\Staff;

class RoutineController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(Request $request): View
    {
        $routines = ClassRoutine::query()
            ->with(['schoolClass', 'section', 'period', 'subject', 'staff', 'room', 'academicYear'])
            ->when($request->integer('class_id'), fn ($q, $id) => $q->where('school_class_id', $id))
            ->when($request->filled('day') && $request->input('day') !== '', fn ($q) => $q->where('day_of_week', (int) $request->input('day')))
            ->orderBy('day_of_week')
            ->orderBy('period_id')
            ->paginate(30)
            ->withQueryString();

        return view('tenant.academics.routines.index', array_merge($this->academicFormOptions(), compact('routines')));
    }

    public function create(): View
    {
        return view('tenant.academics.routines.create', $this->academicFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if (! empty($data['staff_id'])) {
            $staff = Staff::query()->findOrFail($data['staff_id']);
            $this->assertTenantOwned($staff);
        }
        ClassRoutine::query()->create($data);

        return redirect()->route('tenant.routines.index')->with('status', __('ui.routine_saved'));
    }

    public function destroy(ClassRoutine $routine): RedirectResponse
    {
        $this->assertTenantOwned($routine);
        $routine->delete();

        return redirect()->route('tenant.routines.index')->with('status', __('ui.routine_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')],
            'day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'period_id' => ['required', 'integer', Rule::exists('periods', 'id')],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')],
            'room_id' => ['nullable', 'integer', Rule::exists('rooms', 'id')],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
