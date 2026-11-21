<?php

namespace App\Http\Controllers\Tenant\Examinations;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\GradingScale;
use Modules\Examinations\Models\GradingScheme;

class GradingSchemeController extends Controller
{
    public function index(): View
    {
        $schemes = GradingScheme::query()
            ->with('gradingScale.bands')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(20);

        $scales = GradingScale::query()->where('is_active', true)->orderBy('name')->get();

        return view('tenant.examinations.schemes.index', compact('schemes', 'scales'));
    }

    public function store(Request $request, CurrentTenant $currentTenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'grading_scale_id' => ['required', 'integer', Rule::exists('grading_scales', 'id')],
            'description' => ['nullable', 'string'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['code'] = $data['code'] ?: null;
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_default']) {
            GradingScheme::query()->where('tenant_id', $currentTenant->id())->update(['is_default' => false]);
        }

        GradingScheme::query()->create($data);

        return back()->with('status', __('ui.grading_scheme_saved'));
    }

    public function destroy(GradingScheme $scheme): RedirectResponse
    {
        $this->assertTenantOwned($scheme);
        $scheme->delete();

        return back()->with('status', __('ui.grading_scheme_deleted'));
    }
}
