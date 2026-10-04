<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\BusinessDivision;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_division_id' => BusinessDivision::factory(),
            'title' => fake()->unique()->sentence(4),
            'client' => fake()->company(),
            'location' => 'Makurdi, Benue State',
            'summary' => fake()->sentence(14),
            'description' => '<div>'.fake()->paragraph().'</div>',
            'start_date' => now()->subYear(),
            'completion_date' => null,
            'status' => ProjectStatus::Ongoing,
            'featured' => false,
            'published_at' => now()->subDay(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['published_at' => null]);
    }

    public function featured(): static
    {
        return $this->state(['featured' => true]);
    }
}
