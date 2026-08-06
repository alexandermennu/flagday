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
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'organization' => fake()->company(),
            'department' => fake()->optional()->word(),
            'position' => fake()->jobTitle(),
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
