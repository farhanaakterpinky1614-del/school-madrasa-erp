<?php

namespace App\Http\Controllers\Tenant\Admissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Admissions\Models\AdmissionFormField;
use Modules\Admissions\Models\AdmissionFormTemplate;

class FormTemplateController extends Controller
{
    public function index(): View
    {
        $templates = AdmissionFormTemplate::query()->withCount('fields')->orderByDesc('is_default')->orderBy('name')->paginate(20);

        return view('tenant.admissions.forms.index', compact('templates'));
    }

    public function create(): View
    {
        return view('tenant.admissions.forms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['code'] = ($data['code'] ?? null) ?: null;
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_default']) {
            AdmissionFormTemplate::query()->update(['is_default' => false]);
        }

        $template = AdmissionFormTemplate::query()->create($data);

        return redirect()->route('tenant.admission-forms.edit', $template)->with('status', __('ui.form_template_saved'));
    }

    public function edit(AdmissionFormTemplate $admission_form): View
    {
        $this->assertTenantOwned($admission_form);
        $admission_form->load('fields');

        return view('tenant.admissions.forms.edit', ['template' => $admission_form]);
    }

    public function update(Request $request, AdmissionFormTemplate $admission_form): RedirectResponse
    {
        $this->assertTenantOwned($admission_form);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['code'] = ($data['code'] ?? null) ?: null;
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_default']) {
            AdmissionFormTemplate::query()->whereKeyNot($admission_form->id)->update(['is_default' => false]);
        }

        $admission_form->update($data);

        return redirect()->route('tenant.admission-forms.edit', $admission_form)->with('status', __('ui.form_template_saved'));
    }

    public function storeField(Request $request, AdmissionFormTemplate $admission_form): RedirectResponse
    {
        $this->assertTenantOwned($admission_form);
        $data = $request->validate([
            'field_key' => ['required', 'string', 'max:64', 'alpha_dash'],
            'label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', 'in:text,number,date,select,textarea,checkbox,file'],
            'options' => ['nullable', 'string'],
            'is_required' => ['sometimes', 'boolean'],
            'is_visible' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'applies_to' => ['required', 'in:online,offline,both'],
        ]);

        $options = null;
        if (! empty($data['options'])) {
            $options = collect(explode(',', $data['options']))->map(fn ($v) => trim($v))->filter()->values()->all();
        }

        AdmissionFormField::query()->create([
            'admission_form_template_id' => $admission_form->id,
            'field_key' => $data['field_key'],
            'label' => $data['label'],
            'field_type' => $data['field_type'],
            'options' => $options,
            'is_required' => $request->boolean('is_required'),
            'is_visible' => $request->boolean('is_visible', true),
            'sort_order' => $data['sort_order'] ?? 0,
            'applies_to' => $data['applies_to'],
        ]);

        return redirect()->route('tenant.admission-forms.edit', $admission_form)->with('status', __('ui.form_field_saved'));
    }

    public function destroyField(AdmissionFormTemplate $admission_form, AdmissionFormField $field): RedirectResponse
    {
        $this->assertTenantOwned($admission_form);
        $this->assertTenantOwned($field);
        abort_unless((int) $field->admission_form_template_id === (int) $admission_form->id, 404);
        $field->delete();

        return redirect()->route('tenant.admission-forms.edit', $admission_form)->with('status', __('ui.form_field_deleted'));
    }
}
