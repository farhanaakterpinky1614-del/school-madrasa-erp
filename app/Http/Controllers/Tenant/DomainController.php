<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Platform\Services\FeatureAccess;
use Modules\Tenancy\Models\TenantDomain;
use Modules\Tenancy\Services\TenantResolver;

class DomainController extends Controller
{
    public function index(CurrentTenant $currentTenant, FeatureAccess $features): View
    {
        $tenant = $currentTenant->require();
        $domains = TenantDomain::query()->orderByDesc('is_primary')->orderBy('host')->get();

        return view('tenant.domains.index', [
            'tenant' => $tenant,
            'domains' => $domains,
            'canCustomDomain' => $features->enabled($tenant, 'custom_domain'),
        ]);
    }

    public function store(Request $request, CurrentTenant $currentTenant, FeatureAccess $features, TenantResolver $resolver): RedirectResponse
    {
        $tenant = $currentTenant->require();

        if (! $features->enabled($tenant, 'custom_domain')) {
            abort(403, 'Custom domains require the Enterprise plan.');
        }

        $data = $request->validate([
            'host' => ['required', 'string', 'max:255', 'unique:tenant_domains,host'],
        ]);

        $host = strtolower(trim($data['host']));

        if ($resolver->isCentralHost($host) || $resolver->isPlatformHost($host)) {
            return back()->withErrors(['host' => 'This host is reserved by the platform.']);
        }

        $domain = TenantDomain::query()->create([
            'host' => $host,
            'type' => TenantDomain::TYPE_CUSTOM,
            'is_primary' => false,
            'verification_status' => TenantDomain::STATUS_PENDING,
        ]);

        // TODO(Phase 15): kick off real DNS TXT / HTTP verification instead of stub.
        $domain->beginVerification();

        return back()->with('status', 'Custom domain added (verification pending — DNS verify stubbed).');
    }

    public function stubVerify(TenantDomain $domain, CurrentTenant $currentTenant, FeatureAccess $features): RedirectResponse
    {
        $tenant = $currentTenant->require();

        abort_unless((int) $domain->tenant_id === (int) $tenant->id, 404);

        if (! $features->enabled($tenant, 'custom_domain')) {
            abort(403);
        }

        // TODO(Phase 15): replace with verified DNS/HTTP challenge.
        $domain->markVerifiedStub();

        return back()->with('status', 'Domain marked verified (stub). Full DNS verification ships later.');
    }
}
