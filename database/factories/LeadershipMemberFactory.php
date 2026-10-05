<?php

namespace Database\Factories;

use App\Models\LeadershipMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeadershipMember> */
class LeadershipMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'position' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'sort_order' => 0,
            'is_published' => true,
        ];
    }
}
