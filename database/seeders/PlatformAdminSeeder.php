<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@sme.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'tenant_id' => null,
                'is_platform_admin' => true,
                'user_type' => User::TYPE_PLATFORM_ADMIN,
                'email_verified_at' => now(),
            ],
        );
    }
}
