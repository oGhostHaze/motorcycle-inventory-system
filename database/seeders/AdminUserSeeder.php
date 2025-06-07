<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@motoshop.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@motoshop.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'permissions' => [
                    'manage_users',
                    'manage_inventory',
                    'process_sales',
                    'view_reports',
                    'manage_suppliers',
                    'manage_customers',
                    'manage_products',
                    'manage_warehouses',
                    'manage_settings',
                    'view_analytics'
                ],
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Admin user created/updated: {$adminUser->email}");

        // Create a manager user
        $managerUser = User::updateOrCreate(
            ['email' => 'manager@motoshop.com'],
            [
                'name' => 'Manager User',
                'email' => 'manager@motoshop.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
                'permissions' => [
                    'manage_inventory',
                    'process_sales',
                    'view_reports',
                    'manage_suppliers',
                    'manage_customers',
                    'manage_products',
                    'view_analytics'
                ],
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Manager user created/updated: {$managerUser->email}");

        // Create a cashier user
        $cashierUser = User::updateOrCreate(
            ['email' => 'cashier@motoshop.com'],
            [
                'name' => 'Cashier User',
                'email' => 'cashier@motoshop.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'is_active' => true,
                'permissions' => [
                    'process_sales',
                    'view_products',
                    'manage_customers'
                ],
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Cashier user created/updated: {$cashierUser->email}");
    }
}
