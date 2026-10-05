<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobApplication> */
class JobApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'career_id' => JobOpening::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+234 80'.fake()->numerify('########'),
            'cover_letter' => fake()->paragraph(),
            'cv_path' => 'applications/test-cv.pdf',
            'cv_original_name' => 'cv.pdf',
            'consent' => true,
            'status' => ApplicationStatus::Received,
        ];
    }
}
