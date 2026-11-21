<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\CurriculumSubject;

/**
 * Operational class×subject offerings (beyond subject catalogue) via curriculum_subject.
 */
class SubjectOfferingController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(): View
    {
        $offerings = CurriculumSubject::query()
            ->with(['curriculum', 'subject', 'schoolClass'])
            ->orderBy('curriculum_id')
            ->orderBy('school_class_id')
            ->paginate(30);

        return view('tenant.academics.subject-offerings.index', compact('offerings'));
    }

    public function create(): View
    {
        return view('tenant.academics.subject-offerings.create', $this->academicFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'curriculum_id' => ['required', 'integer', Rule::exists('curricula', 'id')],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
            'is_compulsory' => ['sometimes', 'boolean'],
            'weekly_periods' => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);
        $data['is_compulsory'] = $request->boolean('is_compulsory', true);

        CurriculumSubject::query()->create($data);

        return redirect()->route('tenant.subject-offerings.index')->with('status', __('ui.offering_saved'));
    }

    public function update(Request $request, CurriculumSubject $offering): RedirectResponse
    {
        $this->assertTenantOwned($offering);
        $data = $request->validate([
            'weekly_periods' => ['nullable', 'integer', 'min:1', 'max:40'],
            'is_compulsory' => ['sometimes', 'boolean'],
        ]);
        $data['is_compulsory'] = $request->boolean('is_compulsory', $offering->is_compulsory);
        $offering->update($data);

        return redirect()->route('tenant.subject-offerings.index')->with('status', __('ui.offering_saved'));
    }

    public function destroy(CurriculumSubject $offering): RedirectResponse
    {
        $this->assertTenantOwned($offering);
        $offering->delete();

        return redirect()->route('tenant.subject-offerings.index')->with('status', __('ui.offering_deleted'));
    }
}
