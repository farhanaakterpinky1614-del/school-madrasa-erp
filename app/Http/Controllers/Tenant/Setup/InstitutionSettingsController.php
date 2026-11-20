<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Services\InstitutionTypePreset;
use Modules\Settings\Services\InstitutionSettingsService;

class InstitutionSettingsController extends Controller
{
    public function edit(
        CurrentTenant $currentTenant,
        InstitutionSettingsService $settings,
        InstitutionTypePreset $presets,
    ): View {
        $tenant = $currentTenant->require();
        $values = $settings->forTenant($tenant);
        $preset = $presets->for($tenant->institution_type);

        return view('tenant.setup.settings.edit', [
            'tenant' => $tenant,
            'settings' => $values,
            'preset' => $preset,
            'locales' => config('sme.locales'),
        ]);
    }

    public function update(
        Request $request,
        CurrentTenant $currentTenant,
        InstitutionSettingsService $settings,
    ): RedirectResponse {
        $tenant = $currentTenant->require();

        $data = $request->validate([
            'locale' => ['required', Rule::in(array_keys(config('sme.locales')))],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'max:64'],
            'date_format' => ['required', 'string', 'max:32'],
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['string', Rule::in(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'])],
            'primary_color' => ['nullable', 'string', 'max:16'],
            'secondary_color' => ['nullable', 'string', 'max:16'],
        ]);

        $settings->putMany($tenant, [
            'locale' => $data['locale'],
            'currency' => strtoupper($data['currency']),
            'timezone' => $data['timezone'],
            'date_format' => $data['date_format'],
            'working_days' => array_values($data['working_days'] ?? []),
            'branding' => [
                'primary_color' => $data['primary_color'] ?? '#4F46E5',
                'secondary_color' => $data['secondary_color'] ?? '#0F172A',
            ],
        ]);

        $tenant->update([
            'locale_default' => $data['locale'],
            'currency' => strtoupper($data['currency']),
            'timezone' => $data['timezone'],
        ]);

        if ($institution = $tenant->institution) {
            $institution->update([
                'primary_color' => $data['primary_color'] ?? $institution->primary_color,
                'secondary_color' => $data['secondary_color'] ?? $institution->secondary_color,
            ]);
        }

        return back()->with('status', __('ui.settings_saved'));
    }
}
