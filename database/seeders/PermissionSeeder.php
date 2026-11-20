<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Services\RoleProvisioner;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(RoleProvisioner::class)->seedPermissionCatalog();
    }
}
