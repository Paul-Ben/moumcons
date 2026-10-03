<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<\App\Models\BusinessDivision> */
class BusinessDivisionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(12),
            'full_description' => fake()->paragraphs(3, true),
            'category' => fake()->randomElement(['Technology', 'Agriculture', 'Services']),
            'status' => \App\Enums\DivisionStatus::Active,
            'featured' => false,
            'icon' => 'building',
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => '+234 80' . fake()->numerify('########'),
            'location' => fake()->city() . ', Nigeria',
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
