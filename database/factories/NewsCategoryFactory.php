<?php

namespace Database\Factories;

use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NewsCategory> */
class NewsCategoryFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true)];
    }
}
