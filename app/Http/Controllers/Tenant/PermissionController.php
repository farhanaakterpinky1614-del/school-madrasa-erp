<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->isTenantAdmin() || $request->user()->can('settings.permissions.view'),
            403
        );

        $catalog = collect(config('identity.permissions'))
            ->reject(fn (string $p) => str_starts_with($p, 'platform.'))
            ->groupBy(fn (string $p) => explode('.', $p)[0])
            ->map(fn ($group) => $group->values()->all())
            ->all();

        return view('tenant.permissions.index', compact('catalog'));
    }
}
