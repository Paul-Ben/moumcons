<?php

namespace Database\Factories;

use App\Models\BusinessDivision;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\QuoteRequest> */
class QuoteRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'organization' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+234 80' . fake()->numerify('########'),
            'business_division_id' => BusinessDivision::factory(),
            'service_id' => Service::factory(),
            'project_title' => fake()->sentence(6),
            'location' => 'Makurdi, Benue State',
            'requirements' => fake()->paragraph(),
            'estimated_quantity' => fake()->numberBetween(1, 100) . ' units',
            'budget_range' => '₦500k - ₦2m',
            'consent' => true,
        ];
    }
}
