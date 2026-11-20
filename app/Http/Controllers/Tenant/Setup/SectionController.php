<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Shift;

class SectionController extends Controller
{
    public function index(): View
    {
        $sections = Section::query()->with(['schoolClass', 'shift'])->orderBy('name')->paginate(20);

        return view('tenant.setup.sections.index', compact('sections'));
    }

    public function create(): View
    {
        return view('tenant.setup.sections.create', [
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
            'shifts' => Shift::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'shift_id' => ['nullable', 'integer', Rule::exists('shifts', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        Section::query()->create($data);

        return redirect()->route('tenant.sections.index')->with('status', __('ui.section_saved'));
    }

    public function edit(Section $section): View
    {
        $this->assertTenantOwned($section);
        return view('tenant.setup.sections.edit', [
            'section' => $section,
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
            'shifts' => Shift::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $this->assertTenantOwned($section);
        $data = $request->validate([
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'shift_id' => ['nullable', 'integer', Rule::exists('shifts', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $section->update($data);

        return redirect()->route('tenant.sections.index')->with('status', __('ui.section_saved'));
    }

    public function destroy(Section $section): RedirectResponse
    {
        $this->assertTenantOwned($section);
        $section->delete();

        return redirect()->route('tenant.sections.index')->with('status', __('ui.section_deleted'));
    }
}
