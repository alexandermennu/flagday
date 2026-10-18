<?php

namespace Tests\Feature;

use Tests\TestCase;

class RsvpAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The gate is off by default; these tests cover its behavior when switched on.
        config(['event.rsvp_passcode_required' => true, 'event.rsvp_passcode' => 'SU2026']);
    }

    public function test_rsvp_form_is_open_without_a_passcode_when_the_gate_is_disabled(): void
    {
        config(['event.rsvp_passcode_required' => false]);

        $this->get(route('rsvp.create'))->assertOk()->assertSee('Honors Ball RSVP');
    }

    public function test_rsvp_form_redirects_to_the_passcode_screen_when_not_yet_unlocked(): void
    {
        $this->get(route('rsvp.create'))->assertRedirect(route('rsvp.access'));
    }

    public function test_incorrect_passcode_is_rejected(): void
    {
        $response = $this->post(route('rsvp.access.store'), ['passcode' => 'wrong-code']);

        $response->assertSessionHasErrors('passcode');
        $this->get(route('rsvp.create'))->assertRedirect(route('rsvp.access'));
    }

    public function test_correct_passcode_unlocks_the_rsvp_form_and_redirects_back_to_it(): void
    {
        $this->get(route('rsvp.create'));

        $response = $this->post(route('rsvp.access.store'), [
            'passcode' => config('event.rsvp_passcode'),
        ]);

        $response->assertRedirect(route('rsvp.create'));
        $this->get(route('rsvp.create'))->assertOk();
    }

    public function test_passcode_check_ignores_case_and_surrounding_whitespace(): void
    {
        // Covers mobile auto-capitalization and codes pasted with a trailing space/newline.
        $response = $this->post(route('rsvp.access.store'), [
            'passcode' => ' '.strtolower(config('event.rsvp_passcode'))." \n",
        ]);

        $response->assertRedirect(route('rsvp.create'));
        $this->get(route('rsvp.create'))->assertOk();
    }
}
