<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Platform\Services\ThemePack;

class AppearanceController extends Controller
{
    public function edit(CurrentTenant $currentTenant, ThemePack $themePack): View
    {
        $tenant = $currentTenant->require();
        $institution = $tenant->institution;

        return view('tenant.appearance.edit', [
            'tenant' => $tenant,
            'institution' => $institution,
            'packs' => $themePack->catalogue(),
            'activePack' => $themePack->activePack($tenant, $institution),
            'canPremium' => $themePack->canUsePremium($tenant),
        ]);
    }

    public function update(Request $request, CurrentTenant $currentTenant, ThemePack $themePack): RedirectResponse
    {
        $tenant = $currentTenant->require();
        $institution = $tenant->institution;
        abort_unless($institution, 404);

        $data = $request->validate([
            'theme_pack' => ['required', 'string', 'max:64'],
        ]);

        $pack = $themePack->setPack($tenant, $institution, $data['theme_pack']);

        return back()->with('status', "Theme pack set to {$pack}.");
    }
}
