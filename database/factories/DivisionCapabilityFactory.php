<?php

namespace Database\Factories;

use App\Models\BusinessDivision;
use App\Models\DivisionCapability;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DivisionCapability> */
class DivisionCapabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_division_id' => BusinessDivision::factory(),
            'title' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(12),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
