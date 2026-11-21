<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\HomeworkItem;

class HomeworkController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(): View
    {
        $items = HomeworkItem::query()
            ->with(['schoolClass', 'section', 'subject', 'staff'])
            ->orderByDesc('due_on')
            ->orderByDesc('id')
            ->paginate(20);

        return view('tenant.academics.homework.index', compact('items'));
    }

    public function create(): View
    {
        return view('tenant.academics.homework.create', $this->academicFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        HomeworkItem::query()->create($this->validated($request));

        return redirect()->route('tenant.homework.index')->with('status', __('ui.homework_saved'));
    }

    public function edit(HomeworkItem $homework): View
    {
        $this->assertTenantOwned($homework);

        return view('tenant.academics.homework.edit', array_merge(
            $this->academicFormOptions(),
            ['item' => $homework]
        ));
    }

    public function update(Request $request, HomeworkItem $homework): RedirectResponse
    {
        $this->assertTenantOwned($homework);
        $homework->update($this->validated($request));

        return redirect()->route('tenant.homework.index')->with('status', __('ui.homework_saved'));
    }

    public function destroy(HomeworkItem $homework): RedirectResponse
    {
        $this->assertTenantOwned($homework);
        $homework->delete();

        return redirect()->route('tenant.homework.index')->with('status', __('ui.homework_deleted'));
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
            'status' => ['required', Rule::in([
                HomeworkItem::STATUS_DRAFT,
                HomeworkItem::STATUS_PUBLISHED,
                HomeworkItem::STATUS_CLOSED,
            ])],
        ]);
    }
}
