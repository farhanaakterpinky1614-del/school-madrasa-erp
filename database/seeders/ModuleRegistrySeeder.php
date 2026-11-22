<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Platform\Models\ModuleRegistry;

class ModuleRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['key' => 'dashboard', 'name' => 'Dashboard', 'feature_key' => null, 'is_core' => true, 'sort_order' => 1],
            ['key' => 'identity', 'name' => 'Identity & users', 'feature_key' => null, 'is_core' => true, 'sort_order' => 2],
            ['key' => 'settings', 'name' => 'Institution settings', 'feature_key' => null, 'is_core' => true, 'sort_order' => 3],
            ['key' => 'academics', 'name' => 'Academics', 'feature_key' => null, 'is_core' => true, 'sort_order' => 10],
            ['key' => 'admissions', 'name' => 'Admissions', 'feature_key' => null, 'is_core' => true, 'sort_order' => 11],
            ['key' => 'attendance', 'name' => 'Attendance', 'feature_key' => null, 'is_core' => true, 'sort_order' => 12],
            ['key' => 'examinations', 'name' => 'Examinations', 'feature_key' => null, 'is_core' => true, 'sort_order' => 13],
            ['key' => 'fees', 'name' => 'Fees', 'feature_key' => null, 'is_core' => true, 'sort_order' => 14],
            ['key' => 'finance', 'name' => 'Finance / expenses', 'feature_key' => null, 'is_core' => true, 'sort_order' => 14],
            ['key' => 'hr', 'name' => 'HR & payroll', 'feature_key' => null, 'is_core' => true, 'sort_order' => 15],
            ['key' => 'hifz', 'name' => 'Hifz', 'feature_key' => 'module_hifz', 'is_core' => false, 'sort_order' => 20],
            ['key' => 'library', 'name' => 'Library', 'feature_key' => 'module_library', 'is_core' => false, 'sort_order' => 21],
            ['key' => 'hostel', 'name' => 'Hostel', 'feature_key' => 'module_hostel', 'is_core' => false, 'sort_order' => 22],
            ['key' => 'transport', 'name' => 'Transport', 'feature_key' => 'module_transport', 'is_core' => false, 'sort_order' => 23],
            ['key' => 'api', 'name' => 'API access', 'feature_key' => 'api_access', 'is_core' => false, 'sort_order' => 30],
            ['key' => 'custom_domain', 'name' => 'Custom domain', 'feature_key' => 'custom_domain', 'is_core' => false, 'sort_order' => 31],
            ['key' => 'webhooks', 'name' => 'Webhooks', 'feature_key' => 'webhooks', 'is_core' => false, 'sort_order' => 32],
            ['key' => 'premium_ui', 'name' => 'Premium UI packs', 'feature_key' => 'premium_ui_packs', 'is_core' => false, 'sort_order' => 33],
        ];

        foreach ($modules as $module) {
            ModuleRegistry::query()->updateOrCreate(
                ['key' => $module['key']],
                [
                    'name' => $module['name'],
                    'description' => $module['name'].' module',
                    'feature_key' => $module['feature_key'],
                    'is_core' => $module['is_core'],
                    'sort_order' => $module['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
