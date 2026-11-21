<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\LearningMaterial;

class LearningMaterialController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(): View
    {
        $materials = LearningMaterial::query()
            ->with(['schoolClass', 'subject', 'staff'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('tenant.academics.materials.index', compact('materials'));
    }

    public function create(): View
    {
        return view('tenant.academics.materials.create', $this->academicFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['uploaded_by'] = $request->user()?->id;
        $data['is_published'] = $request->boolean('is_published', true);

        if ($request->hasFile('file')) {
            $tenantId = $request->user()?->tenant_id;
            $path = $request->file('file')->store("tenants/{$tenantId}/learning-materials", 'local');
            $data['file_path'] = $path;
            $data['mime'] = $request->file('file')->getMimeType();
            $data['size'] = $request->file('file')->getSize();
            $data['material_type'] = LearningMaterial::TYPE_FILE;
        }

        LearningMaterial::query()->create($data);

        return redirect()->route('tenant.materials.index')->with('status', __('ui.material_saved'));
    }

    public function destroy(LearningMaterial $material): RedirectResponse
    {
        $this->assertTenantOwned($material);
        if ($material->file_path) {
            Storage::disk('local')->delete($material->file_path);
        }
        $material->delete();

        return redirect()->route('tenant.materials.index')->with('status', __('ui.material_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'material_type' => ['required', Rule::in([
                LearningMaterial::TYPE_NOTE,
                LearningMaterial::TYPE_FILE,
                LearningMaterial::TYPE_LINK,
            ])],
            'external_url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'max:10240'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
    }
}
