<?php

namespace Modules\Settings\Services;

use Modules\Settings\Models\InstitutionSetting;
use Modules\Tenancy\Models\Tenant;

class InstitutionSettingsService
{
    /**
     * Default institution settings (Bangladesh SaaS defaults).
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'locale' => config('sme.default_locale', 'en'),
            'currency' => config('sme.currency', 'BDT'),
            'timezone' => config('sme.timezone', 'Asia/Dhaka'),
            'date_format' => 'd/m/Y',
            'working_days' => ['sun', 'mon', 'tue', 'wed', 'thu'],
            'branding' => [
                'primary_color' => '#4F46E5',
                'secondary_color' => '#0F172A',
                'logo_path' => null,
            ],
            'terminology' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forTenant(Tenant $tenant): array
    {
        $stored = InstitutionSetting::queryWithoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->pluck('value', 'key')
            ->all();

        $merged = array_replace_recursive($this->defaults(), [
            'locale' => $tenant->locale_default ?: config('sme.default_locale', 'en'),
            'currency' => $tenant->currency ?: config('sme.currency', 'BDT'),
            'timezone' => $tenant->timezone ?: config('sme.timezone', 'Asia/Dhaka'),
        ]);

        foreach ($stored as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = array_replace_recursive($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function putMany(Tenant $tenant, array $settings): void
    {
        foreach ($settings as $key => $value) {
            InstitutionSetting::queryWithoutTenantScope()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'key' => $key],
                ['value' => $value]
            );
        }
    }
}
