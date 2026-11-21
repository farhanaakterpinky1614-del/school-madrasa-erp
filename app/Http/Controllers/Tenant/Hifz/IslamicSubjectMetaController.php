<?php

namespace App\Http\Controllers\Tenant\Hifz;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\Subject;
use Modules\Hifz\Models\IslamicSubjectMeta;

class IslamicSubjectMetaController extends Controller
{
    public function index(): View
    {
        $metas = IslamicSubjectMeta::query()
            ->with('subject')
            ->orderBy('id')
            ->paginate(25);

        $subjects = Subject::query()
            ->whereIn('subject_type', ['islamic', 'hifz'])
            ->orderBy('name')
            ->get();

        if ($subjects->isEmpty()) {
            $subjects = Subject::query()->orderBy('name')->limit(20)->get();
        }

        return view('tenant.hifz.meta.index', [
            'metas' => $metas,
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id'),
                Rule::unique('islamic_subjects_meta', 'subject_id')
                    ->where(fn ($q) => $q->where('tenant_id', app(\App\Core\Tenancy\CurrentTenant::class)->id())),
            ],
            'kitab_name' => ['nullable', 'string', 'max:255'],
            'department_label' => ['nullable', 'string', 'max:255'],
            'qawmi_level' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ]);

        IslamicSubjectMeta::query()->create($data);

        return redirect()
            ->route('tenant.hifz.meta.index')
            ->with('status', __('ui.islamic_meta_saved'));
    }

    public function update(Request $request, IslamicSubjectMeta $meta): RedirectResponse
    {
        $this->assertTenantOwned($meta);

        $data = $request->validate([
            'kitab_name' => ['nullable', 'string', 'max:255'],
            'department_label' => ['nullable', 'string', 'max:255'],
            'qawmi_level' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ]);

        $meta->update($data);

        return redirect()
            ->route('tenant.hifz.meta.index')
            ->with('status', __('ui.islamic_meta_saved'));
    }

    public function destroy(IslamicSubjectMeta $meta): RedirectResponse
    {
        $this->assertTenantOwned($meta);
        $meta->delete();

        return redirect()
            ->route('tenant.hifz.meta.index')
            ->with('status', __('ui.islamic_meta_deleted'));
    }
}
