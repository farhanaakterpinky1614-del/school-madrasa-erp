<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\Shift;

class ShiftController extends Controller
{
    public function index(): View
    {
        $shifts = Shift::query()->orderBy('name')->paginate(20);

        return view('tenant.setup.shifts.index', compact('shifts'));
    }

    public function create(): View
    {
        return view('tenant.setup.shifts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        Shift::query()->create($data);

        return redirect()->route('tenant.shifts.index')->with('status', __('ui.shift_saved'));
    }

    public function edit(Shift $shift): View
    {
        $this->assertTenantOwned($shift);
        return view('tenant.setup.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $this->assertTenantOwned($shift);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $shift->update($data);

        return redirect()->route('tenant.shifts.index')->with('status', __('ui.shift_saved'));
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $this->assertTenantOwned($shift);
        $shift->delete();

        return redirect()->route('tenant.shifts.index')->with('status', __('ui.shift_deleted'));
    }
}
