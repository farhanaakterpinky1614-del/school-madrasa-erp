<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\ClassworkAssignment;

class ClassworkController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(): View
    {
        $items = ClassworkAssignment::query()
            ->with(['schoolClass', 'section', 'subject', 'staff'])
            ->orderByDesc('due_on')
            ->orderByDesc('id')
            ->paginate(20);

        return view('tenant.academics.classwork.index', compact('items'));
    }

    public function create(): View
    {
        return view('tenant.academics.classwork.create', $this->academicFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        ClassworkAssignment::query()->create($this->validated($request));

        return redirect()->route('tenant.classwork.index')->with('status', __('ui.classwork_saved'));
    }

    public function edit(ClassworkAssignment $classwork): View
    {
        $this->assertTenantOwned($classwork);

        return view('tenant.academics.classwork.edit', array_merge(
            $this->academicFormOptions(),
            ['item' => $classwork]
        ));
    }

    public function update(Request $request, ClassworkAssignment $classwork): RedirectResponse
    {
        $this->assertTenantOwned($classwork);
        $classwork->update($this->validated($request));

        return redirect()->route('tenant.classwork.index')->with('status', __('ui.classwork_saved'));
    }

    public function destroy(ClassworkAssignment $classwork): RedirectResponse
    {
        $this->assertTenantOwned($classwork);
        $classwork->delete();

        return redirect()->route('tenant.classwork.index')->with('status', __('ui.classwork_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_on' => ['nullable', 'date'],
            'due_on' => ['nullable', 'date'],
            'max_marks' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'status' => ['required', Rule::in([
                ClassworkAssignment::STATUS_DRAFT,
                ClassworkAssignment::STATUS_PUBLISHED,
                ClassworkAssignment::STATUS_CLOSED,
            ])],
        ]);
    }
}
