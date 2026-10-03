<?php

namespace Database\Factories;

use App\Models\BusinessDivision;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\ServiceRequest> */
class ServiceRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'organization' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+234 80' . fake()->numerify('########'),
            'business_division_id' => BusinessDivision::factory(),
            'location' => 'Makurdi, Benue State',
            'requirements' => fake()->paragraph(),
            'budget_range' => '₦500k - ₦2m',
            'consent' => true,
        ];
    }
}
