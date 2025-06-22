<?php

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerGroupFactory extends Factory
{
    protected $model = CustomerGroup::class;

    public function definition(): array
    {
        $groupTypes = [
            [
                'name' => 'Regular Customers',
                'description' => 'Standard customer group with no special discounts',
                'discount_percentage' => 0,
            ],
            [
                'name' => 'VIP Customers',
                'description' => 'High-value customers with exclusive benefits',
                'discount_percentage' => 5,
            ],
            [
                'name' => 'Wholesale Customers',
                'description' => 'Bulk purchasing customers and resellers',
                'discount_percentage' => 10,
            ],
            [
                'name' => 'Mechanic Partners',
                'description' => 'Professional mechanics and repair shops',
                'discount_percentage' => 8,
            ],
            [
                'name' => 'Corporate Accounts',
                'description' => 'Corporate fleet management accounts',
                'discount_percentage' => 12,
            ],
            [
                'name' => 'Motorcycle Clubs',
                'description' => 'Registered motorcycle clubs and organizations',
                'discount_percentage' => 6,
            ],
        ];

        $selectedGroup = $this->faker->randomElement($groupTypes);

        return [
            'name' => $selectedGroup['name'],
            'description' => $selectedGroup['description'],
            'discount_percentage' => $selectedGroup['discount_percentage'],
            'is_active' => true,
        ];
    }

    public function regular(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Regular Customers',
            'description' => 'Standard customer group with no special discounts',
            'discount_percentage' => 0,
        ]);
    }

    public function vip(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'VIP Customers',
            'description' => 'High-value customers with exclusive benefits',
            'discount_percentage' => 5,
        ]);
    }

    public function wholesale(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Wholesale Customers',
            'description' => 'Bulk purchasing customers and resellers',
            'discount_percentage' => 10,
        ]);
    }

    public function mechanic(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Mechanic Partners',
            'description' => 'Professional mechanics and repair shops',
            'discount_percentage' => 8,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
