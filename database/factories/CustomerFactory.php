<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $filipinoFirstNames = [
            'Jose',
            'Maria',
            'Juan',
            'Ana',
            'Carlos',
            'Rosa',
            'Miguel',
            'Elena',
            'Ricardo',
            'Carmen',
            'Antonio',
            'Isabel',
            'Francisco',
            'Teresa',
            'Manuel',
            'Patricia',
            'Rafael',
            'Monica',
            'Gabriel',
            'Sofia',
            'Daniel',
            'Luz',
            'Fernando',
            'Gloria',
            'Alejandro',
            'Esperanza',
            'Luis',
            'Remedios',
            'Roberto',
            'Dolores',
            'Pedro',
            'Concepcion',
            'Diego',
            'Pilar'
        ];

        $filipinoLastNames = [
            'Santos',
            'Reyes',
            'Cruz',
            'Bautista',
            'Ocampo',
            'Garcia',
            'Mendoza',
            'Torres',
            'Tomas',
            'Andres',
            'Marquez',
            'Romualdez',
            'Mercado',
            'Aguilar',
            'Dela Cruz',
            'Gonzales',
            'Lopez',
            'Flores',
            'Villanueva',
            'Ramos',
            'Diaz',
            'Fernandez'
        ];

        $philippineCities = [
            'Manila',
            'Quezon City',
            'Caloocan',
            'Las Piñas',
            'Makati',
            'Malabon',
            'Mandaluyong',
            'Marikina',
            'Muntinlupa',
            'Navotas',
            'Parañaque',
            'Pasay',
            'Pasig',
            'San Juan',
            'Taguig',
            'Valenzuela',
            'Cebu City',
            'Davao City',
            'Iloilo City',
            'Cagayan de Oro',
            'General Santos',
            'Zamboanga City',
            'Bacolod',
            'Baguio',
            'Butuan',
            'Laoag'
        ];

        $firstName = $this->faker->randomElement($filipinoFirstNames);
        $lastName = $this->faker->randomElement($filipinoLastNames);
        $fullName = $firstName . ' ' . $lastName;

        // Determine customer type (70% individual, 30% business)
        $type = $this->faker->randomElement(['individual', 'individual', 'individual', 'business']);

        return [
            'name' => $fullName,
            'email' => strtolower(str_replace(' ', '.', $fullName)) . $this->faker->randomNumber(2) . '@email.com',
            'phone' => '09' . $this->faker->numerify('#########'),
            'address' => $this->faker->numberBetween(1, 999) . ' ' .
                $this->faker->randomElement(['Rizal St.', 'Bonifacio Ave.', 'Quezon Blvd.', 'Mabini St.', 'Luna St.', 'Aguinaldo Ave.']),
            'city' => $this->faker->randomElement($philippineCities),
            'type' => $type,
            'customer_group_id' => CustomerGroup::inRandomOrder()->first()?->id ?? 1,
            'date_of_birth' => $this->faker->dateTimeBetween('-65 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'tax_id' => $type === 'business' ? $this->faker->numerify('###-###-###-###') : null,
            'credit_limit' => $type === 'business' ?
                $this->faker->randomFloat(2, 50000, 500000) :
                $this->faker->randomFloat(2, 10000, 100000),
            'total_purchases' => 0,
            'total_orders' => 0,
            'last_purchase_at' => null,
            'notes' => $this->faker->optional(0.3)->sentence(),
            'is_active' => true,
        ];
    }

    public function individual(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'individual',
            'tax_id' => null,
            'credit_limit' => $this->faker->randomFloat(2, 10000, 100000),
        ]);
    }

    public function business(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'business',
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Inc.', 'Corp.', 'Ltd.', 'Co.']),
            'tax_id' => $this->faker->numerify('###-###-###-###'),
            'credit_limit' => $this->faker->randomFloat(2, 50000, 500000),
        ]);
    }

    public function withPurchaseHistory(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_purchases' => $this->faker->randomFloat(2, 5000, 100000),
            'total_orders' => $this->faker->numberBetween(5, 50),
            'last_purchase_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
