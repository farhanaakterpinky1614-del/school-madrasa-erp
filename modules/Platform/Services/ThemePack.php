<?php

namespace Modules\Platform\Services;

use Modules\Tenancy\Models\Institution;
use Modules\Tenancy\Models\Tenant;

/**
 * Premium UI pack hook points — feature flags only; no full redesign shipped.
 */
class ThemePack
{
    public function __construct(
        protected FeatureAccess $features,
    ) {}

    /**
     * @return array<string, array{label: string, description: string, premium: bool}>
     */
    public function catalogue(): array
    {
        return (array) config('sme.ui_packs.packs', []);
    }

    public function defaultPack(): string
    {
        return (string) config('sme.ui_packs.default', 'indigo_slate');
    }

    public function canUsePremium(Tenant $tenant): bool
    {
        return $this->features->enabled($tenant, 'premium_ui_packs');
    }

    public function activePack(Tenant $tenant, ?Institution $institution = null): string
    {
        $institution ??= $tenant->institution;
        $settings = $institution?->settings ?? [];
        $pack = $settings['theme_pack'] ?? $this->defaultPack();

        $catalogue = $this->catalogue();
        if (! isset($catalogue[$pack])) {
            return $this->defaultPack();
        }

        if (($catalogue[$pack]['premium'] ?? false) && ! $this->canUsePremium($tenant)) {
            return $this->defaultPack();
        }

        return $pack;
    }

    /**
     * Persist pack selection when allowed.
     */
    public function setPack(Tenant $tenant, Institution $institution, string $pack): string
    {
        $catalogue = $this->catalogue();
        if (! isset($catalogue[$pack])) {
            $pack = $this->defaultPack();
        }

        if (($catalogue[$pack]['premium'] ?? false) && ! $this->canUsePremium($tenant)) {
            $pack = $this->defaultPack();
        }

        $settings = $institution->settings ?? [];
        $settings['theme_pack'] = $pack;
        $institution->forceFill(['settings' => $settings])->save();

        return $pack;
    }

    /**
     * CSS class / data attribute hook for layouts.
     */
    public function bodyClass(Tenant $tenant, ?Institution $institution = null): string
    {
        $pack = $this->activePack($tenant, $institution);

        return 'sme-theme-pack-'.$pack;
    }
}
