<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\Period;

class PeriodController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(): View
    {
        $periods = Period::query()->with('shift')->orderBy('sort_order')->paginate(30);

        return view('tenant.academics.periods.index', compact('periods'));
    }

    public function create(): View
    {
        return view('tenant.academics.periods.create', $this->academicFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        Period::query()->create($this->validated($request));

        return redirect()->route('tenant.periods.index')->with('status', __('ui.period_saved'));
    }

    public function edit(Period $period): View
    {
        $this->assertTenantOwned($period);

        return view('tenant.academics.periods.edit', array_merge($this->academicFormOptions(), compact('period')));
    }

    public function update(Request $request, Period $period): RedirectResponse
    {
        $this->assertTenantOwned($period);
        $period->update($this->validated($request));

        return redirect()->route('tenant.periods.index')->with('status', __('ui.period_saved'));
    }

    public function destroy(Period $period): RedirectResponse
    {
        $this->assertTenantOwned($period);
        $period->delete();

        return redirect()->route('tenant.periods.index')->with('status', __('ui.period_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'shift_id' => ['nullable', 'integer', Rule::exists('shifts', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'is_break' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['code'] = ($data['code'] ?? null) ?: null;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_break'] = $request->boolean('is_break');
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
