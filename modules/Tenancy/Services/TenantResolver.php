<?php

namespace Modules\Tenancy\Services;

use Illuminate\Http\Request;
use Modules\Tenancy\Models\Tenant;
use Modules\Tenancy\Models\TenantDomain;

class TenantResolver
{
    public function resolveFromRequest(Request $request): ?Tenant
    {
        $host = strtolower($request->getHost());

        if ($this->isPlatformHost($host) || $this->isCentralHost($host)) {
            return null;
        }

        $baseDomain = strtolower((string) config('sme.tenancy.base_domain'));

        if ($baseDomain !== '' && str_ends_with($host, '.'.$baseDomain)) {
            $slug = substr($host, 0, -(strlen($baseDomain) + 1));

            if ($slug === '' || str_contains($slug, '.') || $this->isReservedSlug($slug)) {
                return null;
            }

            return Tenant::query()
                ->where('slug', $slug)
                ->where('status', Tenant::STATUS_ACTIVE)
                ->first();
        }

        $domain = TenantDomain::queryWithoutTenantScope()
            ->where('host', $host)
            ->where('verification_status', TenantDomain::STATUS_VERIFIED)
            ->first();

        if (! $domain) {
            return null;
        }

        return Tenant::query()
            ->whereKey($domain->tenant_id)
            ->where('status', Tenant::STATUS_ACTIVE)
            ->first();
    }

    public function isPlatformHost(string $host): bool
    {
        $host = strtolower($host);
        $platformHosts = array_map('strtolower', config('sme.tenancy.platform_hosts', []));

        if (in_array($host, $platformHosts, true)) {
            return true;
        }

        $baseDomain = strtolower((string) config('sme.tenancy.base_domain'));

        return $baseDomain !== '' && $host === 'admin.'.$baseDomain;
    }

    public function isCentralHost(string $host): bool
    {
        $host = strtolower($host);
        $central = array_map('strtolower', config('sme.tenancy.central_domains', []));

        return in_array($host, $central, true);
    }

    public function isReservedSlug(string $slug): bool
    {
        return in_array(strtolower($slug), config('sme.tenancy.reserved_slugs', []), true);
    }

    public function subdomainHostFor(string $slug): string
    {
        return strtolower($slug).'.'.config('sme.tenancy.base_domain');
    }
}
