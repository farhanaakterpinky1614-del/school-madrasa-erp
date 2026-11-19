<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Platform\Models\Plan;
use Modules\Platform\Models\PlanFeature;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => Plan::STARTER,
                'name' => 'Starter',
                'description' => 'Small institutions getting started.',
                'price_monthly_bdt' => 0,
                'trial_days' => 14,
                'sort_order' => 1,
                'features' => [
                    'max_students' => 200,
                    'max_staff' => 20,
                    'max_storage_mb' => 1024,
                    'module_library' => false,
                    'module_hostel' => false,
                    'module_transport' => false,
                    'module_hifz' => true,
                    'custom_domain' => false,
                    'api_access' => false,
                ],
            ],
            [
                'code' => Plan::PROFESSIONAL,
                'name' => 'Professional',
                'description' => 'Growing schools and madrasas.',
                'price_monthly_bdt' => 2999,
                'trial_days' => 14,
                'sort_order' => 2,
                'features' => [
                    'max_students' => 2000,
                    'max_staff' => 150,
                    'max_storage_mb' => 10240,
                    'module_library' => true,
                    'module_hostel' => true,
                    'module_transport' => true,
                    'module_hifz' => true,
                    'custom_domain' => false,
                    'api_access' => false,
                ],
            ],
            [
                'code' => Plan::ENTERPRISE,
                'name' => 'Enterprise',
                'description' => 'Multi-campus institutions with custom domain.',
                'price_monthly_bdt' => 9999,
                'trial_days' => 30,
                'sort_order' => 3,
                'features' => [
                    'max_students' => null,
                    'max_staff' => null,
                    'max_storage_mb' => 102400,
                    'module_library' => true,
                    'module_hostel' => true,
                    'module_transport' => true,
                    'module_hifz' => true,
                    'custom_domain' => true,
                    'api_access' => true,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $plan = Plan::query()->updateOrCreate(
                ['code' => $planData['code']],
                [...$planData, 'is_active' => true],
            );

            foreach ($features as $key => $value) {
                $enabled = is_bool($value) ? $value : true;
                $limit = is_bool($value) ? null : $value;

                PlanFeature::query()->updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_key' => $key],
                    ['enabled' => $enabled, 'limit_value' => $limit],
                );
            }
        }
    }
}
