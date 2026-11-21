<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\Syllabus;
use Modules\Academics\Models\SyllabusTopic;

class SyllabusController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(): View
    {
        $syllabi = Syllabus::query()
            ->with(['schoolClass', 'subject', 'academicYear', 'curriculum'])
            ->withCount('topics')
            ->orderByDesc('id')
            ->paginate(20);

        return view('tenant.academics.syllabi.index', compact('syllabi'));
    }

    public function create(): View
    {
        return view('tenant.academics.syllabi.create', $this->academicFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $syllabus = Syllabus::query()->create($this->validated($request));

        return redirect()->route('tenant.syllabi.show', $syllabus)->with('status', __('ui.syllabus_saved'));
    }

    public function show(Syllabus $syllabus): View
    {
        $this->assertTenantOwned($syllabus);
        $syllabus->load(['topics', 'schoolClass', 'subject', 'academicYear', 'curriculum']);

        return view('tenant.academics.syllabi.show', compact('syllabus'));
    }

    public function edit(Syllabus $syllabus): View
    {
        $this->assertTenantOwned($syllabus);

        return view('tenant.academics.syllabi.edit', array_merge($this->academicFormOptions(), compact('syllabus')));
    }

    public function update(Request $request, Syllabus $syllabus): RedirectResponse
    {
        $this->assertTenantOwned($syllabus);
        $syllabus->update($this->validated($request));

        return redirect()->route('tenant.syllabi.show', $syllabus)->with('status', __('ui.syllabus_saved'));
    }

    public function destroy(Syllabus $syllabus): RedirectResponse
    {
        $this->assertTenantOwned($syllabus);
        $syllabus->delete();

        return redirect()->route('tenant.syllabi.index')->with('status', __('ui.syllabus_deleted'));
    }

    public function storeTopic(Request $request, Syllabus $syllabus): RedirectResponse
    {
        $this->assertTenantOwned($syllabus);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:500'],
            'estimated_periods' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = true;
        $data['syllabus_id'] = $syllabus->id;

        SyllabusTopic::query()->create($data);

        return redirect()->route('tenant.syllabi.show', $syllabus)->with('status', __('ui.topic_saved'));
    }

    public function destroyTopic(Syllabus $syllabus, SyllabusTopic $topic): RedirectResponse
    {
        $this->assertTenantOwned($syllabus);
        $this->assertTenantOwned($topic);
        abort_unless((int) $topic->syllabus_id === (int) $syllabus->id, 404);
        $topic->delete();

        return redirect()->route('tenant.syllabi.show', $syllabus)->with('status', __('ui.topic_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'curriculum_id' => ['nullable', 'integer', Rule::exists('curricula', 'id')],
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
