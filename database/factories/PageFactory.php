<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Page> */
class PageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'summary' => fake()->sentence(12),
            'content' => '<div>'.fake()->paragraph().'</div>',
            'status' => PageStatus::Published,
        ];
    }
}
