<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicYear;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $years = AcademicYear::query()->orderByDesc('starts_on')->paginate(20);

        return view('tenant.setup.years.index', compact('years'));
    }

    public function create(): View
    {
        return view('tenant.setup.years.create');
    }

    public function store(Request $request, CurrentTenant $currentTenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'is_current' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_current'] = $request->boolean('is_current');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_current']) {
            AcademicYear::query()->where('tenant_id', $currentTenant->id())->update(['is_current' => false]);
        }

        AcademicYear::query()->create($data);

        return redirect()->route('tenant.years.index')->with('status', __('ui.year_saved'));
    }

    public function edit(AcademicYear $year): View
    {
        $this->assertTenantOwned($year);
        return view('tenant.setup.years.edit', ['year' => $year]);
    }

    public function update(Request $request, AcademicYear $year, CurrentTenant $currentTenant): RedirectResponse
    {
        $this->assertTenantOwned($year);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'is_current' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_current'] = $request->boolean('is_current');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_current']) {
            AcademicYear::query()->where('tenant_id', $currentTenant->id())->whereKeyNot($year->id)->update(['is_current' => false]);
        }

        $year->update($data);

        return redirect()->route('tenant.years.index')->with('status', __('ui.year_saved'));
    }

    public function destroy(AcademicYear $year): RedirectResponse
    {
        $this->assertTenantOwned($year);
        $year->delete();

        return redirect()->route('tenant.years.index')->with('status', __('ui.year_deleted'));
    }
}
