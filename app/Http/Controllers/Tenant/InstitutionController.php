<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Tenancy\Models\Institution;

class InstitutionController extends Controller
{
    public function edit(CurrentTenant $currentTenant): View
    {
        $tenant = $currentTenant->require();
        $institution = $tenant->institution;

        abort_unless($institution, 404);

        return view('tenant.institution.edit', [
            'tenant' => $tenant,
            'institution' => $institution,
            'institutionTypes' => config('sme.institution_types'),
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
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'division' => ['nullable', 'string', 'max:120'],
        ]);

        // tenant_id intentionally omitted from validated data — BelongsToTenant protects writes.
        $institution->update($data);
        $tenant->update(['name' => $data['name']]);

        return back()->with('status', 'Institution profile updated.');
    }
}
