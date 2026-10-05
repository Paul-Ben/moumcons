<?php

namespace Database\Factories;

use App\Enums\NewsStatus;
use App\Models\NewsArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NewsArticle> */
class NewsArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(6),
            'excerpt' => fake()->sentence(18),
            'content' => '<div>'.fake()->paragraphs(3, true).'</div>',
            'status' => NewsStatus::Published,
            'published_at' => now()->subDay(),
            'featured' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => NewsStatus::Draft, 'published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(['status' => NewsStatus::Scheduled, 'published_at' => now()->addDays(3)]);
    }
}
