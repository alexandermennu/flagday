<?php

namespace Database\Factories;

use App\Enums\AttendeeStatus;
use App\Models\Attendee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Attendee>
 */
class AttendeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'organization' => fake()->optional()->company(),
            'status' => AttendeeStatus::Pending,
            'invite_token' => (string) Str::uuid(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendeeStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendeeStatus::Declined,
            'confirmed_at' => now(),
        ]);
    }
}
