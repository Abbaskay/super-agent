<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default-tenant'],
            [
                'name' => 'Default Workspace',
                'plan' => 'business',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@superagent.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'subscription' => 'business',
                'tenant_id' => $tenant->id,
                'role' => 'owner',
            ]
        );
    }
}
