<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Platform\Services\FeatureAccess;

class ApiTokenController extends Controller
{
    public function index(CurrentTenant $currentTenant, FeatureAccess $features): View
    {
        $tenant = $currentTenant->require();
        $user = auth()->user();

        return view('tenant.api.tokens', [
            'tenant' => $tenant,
            'canApi' => $features->enabled($tenant, 'api_access'),
            'tokens' => $user?->tokens()->orderByDesc('id')->get() ?? collect(),
            'plainTextToken' => session('plain_text_token'),
        ]);
    }

    public function store(Request $request, CurrentTenant $currentTenant, FeatureAccess $features): RedirectResponse
    {
        $tenant = $currentTenant->require();
        abort_unless($features->enabled($tenant, 'api_access'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $token = $request->user()->createToken($data['name'], ['*']);

        return redirect()
            ->route('tenant.api.tokens.index')
            ->with('status', 'API token created. Copy it now — it will not be shown again.')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, int $tokenId, CurrentTenant $currentTenant, FeatureAccess $features): RedirectResponse
    {
        $tenant = $currentTenant->require();
        abort_unless($features->enabled($tenant, 'api_access'), 403);

        $request->user()->tokens()->whereKey($tokenId)->delete();

        return back()->with('status', 'API token revoked.');
    }
}
