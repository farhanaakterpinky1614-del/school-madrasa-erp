<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\LessonPlan;

class LessonPlanController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(): View
    {
        $plans = LessonPlan::query()
            ->with(['schoolClass', 'section', 'subject', 'staff', 'syllabusTopic'])
            ->orderByDesc('planned_on')
            ->orderByDesc('id')
            ->paginate(20);

        return view('tenant.academics.lesson-plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('tenant.academics.lesson-plans.create', $this->academicFormOptions(true));
    }

    public function store(Request $request): RedirectResponse
    {
        LessonPlan::query()->create($this->validated($request));

        return redirect()->route('tenant.lesson-plans.index')->with('status', __('ui.lesson_plan_saved'));
    }

    public function edit(LessonPlan $lessonPlan): View
    {
        $this->assertTenantOwned($lessonPlan);

        return view('tenant.academics.lesson-plans.edit', array_merge(
            $this->academicFormOptions(true),
            ['plan' => $lessonPlan]
        ));
    }

    public function update(Request $request, LessonPlan $lessonPlan): RedirectResponse
    {
        $this->assertTenantOwned($lessonPlan);
        $lessonPlan->update($this->validated($request));

        return redirect()->route('tenant.lesson-plans.index')->with('status', __('ui.lesson_plan_saved'));
    }

    public function destroy(LessonPlan $lessonPlan): RedirectResponse
    {
        $this->assertTenantOwned($lessonPlan);
        $lessonPlan->delete();

        return redirect()->route('tenant.lesson-plans.index')->with('status', __('ui.lesson_plan_deleted'));
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
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')],
            'syllabus_topic_id' => ['nullable', 'integer', Rule::exists('syllabus_topics', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'objectives' => ['nullable', 'string'],
            'activities' => ['nullable', 'string'],
            'resources' => ['nullable', 'string'],
            'homework_notes' => ['nullable', 'string'],
            'planned_on' => ['nullable', 'date'],
            'status' => ['required', Rule::in([LessonPlan::STATUS_DRAFT, LessonPlan::STATUS_PUBLISHED])],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
