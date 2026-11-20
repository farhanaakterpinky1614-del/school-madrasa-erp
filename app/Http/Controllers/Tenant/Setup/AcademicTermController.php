<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicTerm;
use Modules\Academics\Models\AcademicYear;

class AcademicTermController extends Controller
{
    public function index(): View
    {
        $terms = AcademicTerm::query()->with('academicYear')->orderBy('sort_order')->paginate(20);

        return view('tenant.setup.terms.index', compact('terms'));
    }

    public function create(): View
    {
        $years = AcademicYear::query()->orderByDesc('starts_on')->get();

        return view('tenant.setup.terms.create', compact('years'));
    }

    public function store(Request $request, CurrentTenant $currentTenant): RedirectResponse
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_current' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_current'] = $request->boolean('is_current');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['is_current']) {
            AcademicTerm::query()->where('tenant_id', $currentTenant->id())->update(['is_current' => false]);
        }

        AcademicTerm::query()->create($data);

        return redirect()->route('tenant.terms.index')->with('status', __('ui.term_saved'));
    }

    public function edit(AcademicTerm $term): View
    {
        $this->assertTenantOwned($term);
        $years = AcademicYear::query()->orderByDesc('starts_on')->get();

        return view('tenant.setup.terms.edit', compact('term', 'years'));
    }

    public function update(Request $request, AcademicTerm $term, CurrentTenant $currentTenant): RedirectResponse
    {
        $this->assertTenantOwned($term);
        $data = $request->validate([
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_current' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_current'] = $request->boolean('is_current');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['is_current']) {
            AcademicTerm::query()->where('tenant_id', $currentTenant->id())->whereKeyNot($term->id)->update(['is_current' => false]);
        }

        $term->update($data);

        return redirect()->route('tenant.terms.index')->with('status', __('ui.term_saved'));
    }

    public function destroy(AcademicTerm $term): RedirectResponse
    {
        $this->assertTenantOwned($term);
        $term->delete();

        return redirect()->route('tenant.terms.index')->with('status', __('ui.term_deleted'));
    }
}
