<?php

namespace Database\Factories;

use App\Models\Attendee;
use App\Models\AttendeeGuest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendeeGuest>
 */
class AttendeeGuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendee_id' => Attendee::factory(),
            'full_name' => fake()->name(),
            'organization' => fake()->company(),
            'position' => fake()->jobTitle(),
        ];
    }
}
