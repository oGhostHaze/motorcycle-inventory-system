<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\ProductBrand;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // public function run()
    // {
    //     $this->call([
    //         MotorcycleBrandSeeder::class,
    //         MotorcycleModelSeeder::class,
    //         CategorySeeder::class,
    //         SubcategorySeeder::class,
    //         ProductBrandSeeder::class,
    //         WarehouseSeeder::class,
    //         SupplierSeeder::class,
    //         ProductSeeder::class,
    //         UserSeeder::class,
    //     ]);
    // }
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'permissions' => User::getDefaultPermissions('admin'),
            'email_verified_at' => now(),
        ]);

        // Create manager user
        User::create([
            'name' => 'Store Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'is_active' => true,
            'permissions' => User::getDefaultPermissions('manager'),
            'email_verified_at' => now(),
        ]);

        // Create cashier user
        User::create([
            'name' => 'Store Cashier',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password'),
            'role' => 'cashier',
            'is_active' => true,
            'permissions' => User::getDefaultPermissions('cashier'),
            'email_verified_at' => now(),
        ]);

        // Create main warehouse
        Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN01',
            'address' => '123 Main Street',
            'city' => 'Bacoor',
            'manager_name' => 'John Doe',
            'phone' => '+63-2-1234-5678',
            'type' => 'main',
            'is_active' => true,
        ]);

        // Create showroom
        Warehouse::create([
            'name' => 'Showroom',
            'code' => 'SHOW01',
            'address' => '456 Display Avenue',
            'city' => 'Bacoor',
            'manager_name' => 'Jane Smith',
            'phone' => '+63-2-1234-5679',
            'type' => 'retail',
            'is_active' => true,
        ]);

        // Create categories
        $engineCategory = Category::create([
            'name' => 'Engine Parts',
            'description' => 'All engine related components',
            'icon' => 'o-cog-6-tooth',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Body Parts',
            'description' => 'Motorcycle body and frame components',
            'icon' => 'o-wrench-screwdriver',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Electrical',
            'description' => 'Electrical components and accessories',
            'icon' => 'o-bolt',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Accessories',
            'description' => 'Motorcycle accessories and add-ons',
            'icon' => 'o-star',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // Create some brands
        ProductBrand::create([
            'name' => 'Honda',
            'description' => 'Official Honda motorcycle parts',
            'country' => 'Japan',
            'is_active' => true,
        ]);

        ProductBrand::create([
            'name' => 'Yamaha',
            'description' => 'Official Yamaha motorcycle parts',
            'country' => 'Japan',
            'is_active' => true,
        ]);

        ProductBrand::create([
            'name' => 'Suzuki',
            'description' => 'Official Suzuki motorcycle parts',
            'country' => 'Japan',
            'is_active' => true,
        ]);

        ProductBrand::create([
            'name' => 'Kawasaki',
            'description' => 'Official Kawasaki motorcycle parts',
            'country' => 'Japan',
            'is_active' => true,
        ]);

        ProductBrand::create([
            'name' => 'Generic',
            'description' => 'Generic aftermarket parts',
            'country' => 'Various',
            'is_active' => true,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin credentials: admin@example.com / password');
        $this->command->info('Manager credentials: manager@example.com / password');
        $this->command->info('Cashier credentials: cashier@example.com / password');
    }
}
