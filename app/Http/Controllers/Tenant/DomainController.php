<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Platform\Services\FeatureAccess;
use Modules\Tenancy\Models\TenantDomain;
use Modules\Tenancy\Services\DomainVerificationService;
use Modules\Tenancy\Services\TenantResolver;

class DomainController extends Controller
{
    public function index(
        CurrentTenant $currentTenant,
        FeatureAccess $features,
        DomainVerificationService $verification,
    ): View {
        $tenant = $currentTenant->require();
        $domains = TenantDomain::query()->orderByDesc('is_primary')->orderBy('host')->get();

        return view('tenant.domains.index', [
            'tenant' => $tenant,
            'domains' => $domains,
            'canCustomDomain' => $features->enabled($tenant, 'custom_domain'),
            'verification' => $verification,
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

        $domain->beginVerification();

        return back()->with('status', 'Custom domain added. Add the DNS TXT record, then run Verify.');
    }

    public function verify(
        TenantDomain $domain,
        CurrentTenant $currentTenant,
        FeatureAccess $features,
        DomainVerificationService $verification,
    ): RedirectResponse {
        $tenant = $currentTenant->require();

        abort_unless((int) $domain->tenant_id === (int) $tenant->id, 404);

        if (! $features->enabled($tenant, 'custom_domain')) {
            abort(403);
        }

        $result = $verification->verify($domain);

        if ($result['verified']) {
            return back()->with('status', 'Domain verified via '.$result['method'].'.');
        }

        return back()->withErrors(['host' => 'Verification failed: '.$result['detail']]);
    }

    /**
     * @deprecated Prefer verify() — kept so old bookmarks/tests keep working.
     */
    public function stubVerify(
        TenantDomain $domain,
        CurrentTenant $currentTenant,
        FeatureAccess $features,
        DomainVerificationService $verification,
    ): RedirectResponse {
        return $this->verify($domain, $currentTenant, $features, $verification);
    }
}
