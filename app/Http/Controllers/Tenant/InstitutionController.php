<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Services\InstitutionTypePreset;
use Modules\Tenancy\Models\Institution;

class InstitutionController extends Controller
{
    public function edit(CurrentTenant $currentTenant, InstitutionTypePreset $presets): View
    {
        $tenant = $currentTenant->require();
        $institution = $tenant->institution;

        abort_unless($institution, 404);

        return view('tenant.institution.edit', [
            'tenant' => $tenant,
            'institution' => $institution,
            'institutionTypes' => config('sme.institution_types'),
            'preset' => $presets->for($institution->institution_type),
        ]);
    }

    public function update(Request $request, CurrentTenant $currentTenant): RedirectResponse
    {
        $tenant = $currentTenant->require();
        $institution = Institution::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'institution_type' => ['required', Rule::in(array_keys(config('sme.institution_types')))],
            'eiin' => ['nullable', 'string', 'max:32'],
            'board' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'string', 'max:255'],
            'motto' => ['nullable', 'string', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'primary_color' => ['nullable', 'string', 'max:16'],
            'secondary_color' => ['nullable', 'string', 'max:16'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'division' => ['nullable', 'string', 'max:120'],
        ]);

        // tenant_id intentionally omitted — BelongsToTenant protects writes.
        $institution->update($data);
        $tenant->update([
            'name' => $data['name'],
            'institution_type' => $data['institution_type'],
        ]);

        return back()->with('status', 'Institution profile updated.');
    }
}
