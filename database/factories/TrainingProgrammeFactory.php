<?php

namespace Database\Factories;

use App\Enums\DeliveryMode;
use App\Enums\TrainingStatus;
use App\Models\TrainingProgramme;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TrainingProgramme> */
class TrainingProgrammeFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addWeeks(3)->startOfDay();

        return [
            'title' => fake()->unique()->sentence(4),
            'summary' => fake()->sentence(14),
            'description' => '<div>'.fake()->paragraph().'</div>',
            'course_category' => 'Digital Skills',
            'duration' => '3 days',
            'start_date' => $start,
            'end_date' => $start->copy()->addDays(2),
            'registration_deadline' => $start->copy()->subWeek(),
            'delivery_mode' => DeliveryMode::Physical,
            'venue' => 'MOAUM Training Centre, Makurdi',
            'fee' => 50000,
            'capacity' => 30,
            'status' => TrainingStatus::OpenForRegistration,
            'featured' => false,
            'published_at' => now()->subDay(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['published_at' => null]);
    }
}
