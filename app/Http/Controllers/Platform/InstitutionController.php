<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Tenancy\Models\Institution;

class InstitutionController extends Controller
{
    public function index(): View
    {
        $institutions = Institution::queryWithoutTenantScope()
            ->with('tenant.plan')
            ->latest()
            ->paginate(20);

        return view('platform.institutions.index', compact('institutions'));
    }

    public function show(int $institution): View
    {
        $institution = Institution::queryWithoutTenantScope()
            ->with(['tenant.plan', 'tenant.domains'])
            ->findOrFail($institution);

        return view('platform.institutions.show', compact('institution'));
    }
}
