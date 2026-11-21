<?php

namespace App\Http\Controllers\Tenant\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Library\Concerns\LoadsLibraryFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Library\Models\LibrarySetting;
use Modules\Library\Services\CirculationService;

class LibrarySettingController extends Controller
{
    use LoadsLibraryFormOptions;

    public function __construct(protected CirculationService $circulation) {}

    public function edit(): View
    {
        return view('tenant.library.settings.edit', array_merge($this->libraryFormOptions(), [
            'settings' => $this->circulation->settings(),
        ]));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = $this->circulation->settings();
        $this->assertTenantOwned($settings);

        $data = $request->validate([
            'fine_mode' => ['required', Rule::in(LibrarySetting::FINE_MODES)],
            'fine_daily_rate' => ['required', 'numeric', 'min:0'],
            'fine_fixed_amount' => ['required', 'numeric', 'min:0'],
            'default_loan_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $settings->update($data);

        return back()->with('status', __('ui.library_settings_saved'));
    }
}
