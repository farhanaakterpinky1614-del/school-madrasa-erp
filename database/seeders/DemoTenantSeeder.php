<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;
use Modules\Tenancy\Services\TenantRegistrar;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $registered = app(TenantRegistrar::class)->register([
            'institution_name' => 'Demo Ideal School',
            'slug' => 'demo-school',
            'institution_type' => 'school',
            'admin_name' => 'Demo Admin',
            'admin_email' => 'admin@demo-school.test',
            'admin_password' => 'password123',
            'plan_code' => 'professional',
            'locale' => 'en',
            'phone' => '01700000000',
        ]);

        $registered['institution']->update([
            'name_bn' => 'ডেমো আইডিয়াল স্কুল',
            'eiin' => '123456',
            'board' => 'Dhaka',
            'city' => 'Dhaka',
            'division' => 'Dhaka',
            'address' => 'Demo Road, Dhaka',
            'primary_color' => '#4F46E5',
            'secondary_color' => '#0F172A',
        ]);

        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
    }
}
