<?php

namespace Database\Factories;

use App\Enums\EnquiryStatus;
use App\Enums\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Enquiry> */
class EnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'organization' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+234 80' . fake()->numerify('########'),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'status' => EnquiryStatus::New,
            'priority' => Priority::Normal,
        ];
    }
}
