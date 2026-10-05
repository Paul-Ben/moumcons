<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Models\JobOpening;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobOpening> */
class JobOpeningFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->jobTitle(),
            'location' => 'Makurdi, Benue State',
            'employment_type' => EmploymentType::FullTime,
            'summary' => fake()->sentence(12),
            'description' => '<div>'.fake()->paragraph().'</div>',
            'application_deadline' => now()->addMonth(),
            'status' => JobStatus::Open,
            'published_at' => now()->subDay(),
        ];
    }
}
