<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\GradingScale;
use Modules\Academics\Models\GradingScaleBand;

class GradingScaleController extends Controller
{
    public function index(): View
    {
        $scales = GradingScale::query()->withCount('bands')->orderByDesc('is_default')->orderBy('name')->paginate(20);

        return view('tenant.setup.grading.index', compact('scales'));
    }

    public function create(): View
    {
        return view('tenant.setup.grading.create');
    }

    public function store(Request $request, CurrentTenant $currentTenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
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
            GradingScale::query()->where('tenant_id', $currentTenant->id())->update(['is_default' => false]);
        }

        $scale = GradingScale::query()->create($data);

        return redirect()->route('tenant.grading.edit', $scale)->with('status', __('ui.grading_saved'));
    }

    public function edit(GradingScale $grading): View
    {
        $this->assertTenantOwned($grading);
        $grading->load('bands');

        return view('tenant.setup.grading.edit', ['scale' => $grading]);
    }

    public function update(Request $request, GradingScale $grading, CurrentTenant $currentTenant): RedirectResponse
    {
        $this->assertTenantOwned($grading);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
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
            GradingScale::query()->where('tenant_id', $currentTenant->id())->whereKeyNot($grading->id)->update(['is_default' => false]);
        }

        $grading->update($data);

        return redirect()->route('tenant.grading.edit', $grading)->with('status', __('ui.grading_saved'));
    }

    public function storeBand(Request $request, GradingScale $grading): RedirectResponse
    {
        $this->assertTenantOwned($grading);
        $data = $request->validate([
            'grade' => ['required', 'string', 'max:16'],
            'min_mark' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_mark' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_mark'],
            'gpa' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'remark' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        GradingScaleBand::query()->create([
            ...$data,
            'grading_scale_id' => $grading->id,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('status', __('ui.grading_band_saved'));
    }

    public function destroyBand(GradingScale $grading, GradingScaleBand $band): RedirectResponse
    {
        $this->assertTenantOwned($grading);
        $this->assertTenantOwned($band);
        abort_unless((int) $band->grading_scale_id === (int) $grading->id, 404);
        $band->delete();

        return back()->with('status', __('ui.grading_band_deleted'));
    }

    public function destroy(GradingScale $grading): RedirectResponse
    {
        $this->assertTenantOwned($grading);
        $grading->delete();

        return redirect()->route('tenant.grading.index')->with('status', __('ui.grading_deleted'));
    }
}
