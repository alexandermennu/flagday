<?php

namespace Tests\Feature;

use App\Models\Attendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_page_is_publicly_accessible_without_authentication(): void
    {
        $attendee = Attendee::factory()->confirmed()->create();

        // A 200 here (rather than a 302 redirect) proves no auth middleware is gating this route.
        $this->get(route('pass.verify', $attendee->invite_token))->assertOk();
    }

    public function test_verify_page_shows_attendee_details(): void
    {
        $attendee = Attendee::factory()->confirmed()->create([
            'first_name' => 'Grace',
            'last_name' => 'Kollie',
            'organization' => 'Ministry of Finance',
            'position' => 'Deputy Director',
        ]);

        $response = $this->get(route('pass.verify', $attendee->invite_token));

        $response->assertOk();
        $response->assertSee('Grace Kollie');
        $response->assertSee('Ministry of Finance');
        $response->assertSee('Deputy Director');
        $response->assertSee($attendee->confirmation_id);
        $response->assertSee('Confirmed');
        $response->assertSee(config('event.name'));
        $response->assertSee('To be announced');
    }

    public function test_verify_page_shows_configured_event_time(): void
    {
        config(['event.date' => '2026-12-05', 'event.start_time' => '18:30']);
        $attendee = Attendee::factory()->confirmed()->create();

        $response = $this->get(route('pass.verify', $attendee->invite_token));

        $response->assertSee('Saturday, December 5, 2026');
        $response->assertSee('6:30 PM');
    }

    public function test_verify_page_does_not_mark_the_attendee_as_checked_in(): void
    {
        $attendee = Attendee::factory()->confirmed()->create();

        $this->get(route('pass.verify', $attendee->invite_token));

        $this->assertNull($attendee->fresh()->checked_in_at);
    }

    public function test_verify_page_returns_404_for_an_unknown_token(): void
    {
        $this->get('/confirm/not-a-real-token')->assertNotFound();
    }

    public function test_legacy_pass_url_redirects_to_the_confirm_url(): void
    {
        $attendee = Attendee::factory()->confirmed()->create();

        $this->get('/pass/'.$attendee->invite_token)
            ->assertRedirect(route('pass.verify', $attendee->invite_token));
    }
}
