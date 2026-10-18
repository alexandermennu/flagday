<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        return array_merge(Program::templateDefaults(), [
            'edition_label' => '9th Annual Special Honors Awards',
            'honors_line1' => 'Ninth',
            'event_date' => '2026-11-21',
            'event_time' => '19:00',
            'venue_name' => 'Test Event Hall',
            'venue_address' => '1 Test Road, Monrovia',
            'hosted_by' => 'Test Host',
        ]);
    }
}
