<?php

namespace Database\Factories;

use App\Models\BusinessDivision;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<\App\Models\Service> */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(4, true);

        return [
            'business_division_id' => BusinessDivision::factory(),
            'service_category_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(10),
            'description' => fake()->paragraphs(2, true),
            'service_type' => fake()->randomElement(['Consulting', 'Implementation', 'Training']),
            'pricing_type' => \App\Enums\PricingType::QuoteRequired,
            'featured' => false,
            'status' => \App\Enums\ServiceStatus::Active,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
