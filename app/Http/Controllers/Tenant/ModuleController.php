<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Platform\Services\FeatureAccess;

class ModuleController extends Controller
{
    public function index(CurrentTenant $currentTenant, FeatureAccess $features): View
    {
        $tenant = $currentTenant->require();

        return view('tenant.modules.index', [
            'tenant' => $tenant,
            'modules' => $features->modulesFor($tenant),
        ]);
    }
}
