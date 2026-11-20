<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\Curriculum;
use Modules\Academics\Models\CurriculumSubject;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Subject;

class CurriculumController extends Controller
{
    public function index(): View
    {
        $curricula = Curriculum::query()->withCount('subjectLinks')->orderByDesc('is_default')->orderBy('name')->paginate(20);

        return view('tenant.setup.curricula.index', compact('curricula'));
    }

    public function create(): View
    {
        return view('tenant.setup.curricula.create', [
            'subjects' => Subject::query()->orderBy('name')->get(),
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request, CurrentTenant $currentTenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'board' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }


        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_default']) {
            Curriculum::query()->where('tenant_id', $currentTenant->id())->update(['is_default' => false]);
        }

        $curriculum = Curriculum::query()->create(collect($data)->except(['subject_id', 'school_class_id'])->all());

        if (! empty($data['subject_id'])) {
            CurriculumSubject::query()->create([
                'curriculum_id' => $curriculum->id,
                'subject_id' => $data['subject_id'],
                'school_class_id' => $data['school_class_id'] ?? null,
                'is_compulsory' => true,
            ]);
        }

        return redirect()->route('tenant.curricula.edit', $curriculum)->with('status', __('ui.curriculum_saved'));
    }

    public function edit(Curriculum $curriculum): View
    {
        $this->assertTenantOwned($curriculum);
        $curriculum->load(['subjectLinks.subject', 'subjectLinks.schoolClass']);

        return view('tenant.setup.curricula.edit', [
            'curriculum' => $curriculum,
            'subjects' => Subject::query()->orderBy('name')->get(),
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Curriculum $curriculum, CurrentTenant $currentTenant): RedirectResponse
    {
        $this->assertTenantOwned($curriculum);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'board' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }


        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_default']) {
            Curriculum::query()->where('tenant_id', $currentTenant->id())->whereKeyNot($curriculum->id)->update(['is_default' => false]);
        }

        $curriculum->update($data);

        return redirect()->route('tenant.curricula.edit', $curriculum)->with('status', __('ui.curriculum_saved'));
    }

    public function attachSubject(Request $request, Curriculum $curriculum): RedirectResponse
    {
        $this->assertTenantOwned($curriculum);
        $data = $request->validate([
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
            'is_compulsory' => ['sometimes', 'boolean'],
            'weekly_periods' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        CurriculumSubject::query()->updateOrCreate(
            [
                'curriculum_id' => $curriculum->id,
                'subject_id' => $data['subject_id'],
                'school_class_id' => $data['school_class_id'] ?? null,
            ],
            [
                'is_compulsory' => $request->boolean('is_compulsory', true),
                'weekly_periods' => $data['weekly_periods'] ?? null,
            ]
        );

        return back()->with('status', __('ui.curriculum_subject_attached'));
    }

    public function detachSubject(Curriculum $curriculum, CurriculumSubject $link): RedirectResponse
    {
        $this->assertTenantOwned($curriculum);
        $this->assertTenantOwned($link);
        abort_unless((int) $link->curriculum_id === (int) $curriculum->id, 404);
        $link->delete();

        return back()->with('status', __('ui.curriculum_subject_removed'));
    }

    public function destroy(Curriculum $curriculum): RedirectResponse
    {
        $this->assertTenantOwned($curriculum);
        $curriculum->delete();

        return redirect()->route('tenant.curricula.index')->with('status', __('ui.curriculum_deleted'));
    }
}
