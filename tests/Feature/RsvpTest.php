<?php

namespace Tests\Feature;

use App\Enums\AttendeeStatus;
use App\Mail\RsvpConfirmation;
use App\Mail\RsvpDeclinedAcknowledgement;
use App\Models\Attendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RsvpTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '0770000000',
            'organization' => 'Ministry of Education',
            'department' => 'Bureau of Student Personnel Services',
            'position' => 'Director',
            'status' => 'confirmed',
        ], $overrides);
    }

    public function test_confirming_attendance_creates_attendee_and_queues_confirmation_email(): void
    {
        Mail::fake();

        $response = $this->post(route('rsvp.store'), $this->payload());

        $response->assertRedirect(route('rsvp.thank-you'));

        $attendee = Attendee::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame(AttendeeStatus::Confirmed, $attendee->status);
        $this->assertNotNull($attendee->confirmed_at);
        $this->assertNotNull($attendee->invite_token);
        $this->assertSame('Jane Doe', $attendee->full_name);

        Mail::assertQueued(RsvpConfirmation::class, fn ($mail) => $mail->attendee->is($attendee));
    }

    public function test_declining_attendance_queues_acknowledgement_without_ticket(): void
    {
        Mail::fake();

        $this->post(route('rsvp.store'), $this->payload([
            'first_name' => 'John',
            'last_name' => 'Roe',
            'email' => 'john@example.com',
            'status' => 'declined',
        ]));

        $attendee = Attendee::where('email', 'john@example.com')->firstOrFail();
        $this->assertSame(AttendeeStatus::Declined, $attendee->status);

        Mail::assertQueued(RsvpDeclinedAcknowledgement::class);
        Mail::assertNotQueued(RsvpConfirmation::class);
    }

    public function test_resubmitting_with_same_email_updates_existing_attendee_instead_of_duplicating(): void
    {
        Mail::fake();

        $this->post(route('rsvp.store'), $this->payload(['status' => 'declined']));

        $this->post(route('rsvp.store'), $this->payload([
            'last_name' => 'Doe-Updated',
            'email' => 'JANE@example.com',
            'status' => 'confirmed',
        ]));

        $this->assertSame(1, Attendee::where('email', 'jane@example.com')->count());

        $attendee = Attendee::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame('Jane Doe-Updated', $attendee->full_name);
        $this->assertSame(AttendeeStatus::Confirmed, $attendee->status);
    }

    public function test_organization_and_position_are_required(): void
    {
        $response = $this->post(route('rsvp.store'), $this->payload([
            'organization' => '',
            'position' => '',
        ]));

        $response->assertSessionHasErrors(['organization', 'position']);
    }

    public function test_rsvp_submissions_are_throttled(): void
    {
        Mail::fake();

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('rsvp.store'), $this->payload([
                'email' => "guest{$i}@example.com",
                'status' => 'declined',
            ]));
        }

        $response = $this->post(route('rsvp.store'), $this->payload([
            'email' => 'guest11@example.com',
            'status' => 'declined',
        ]));

        $response->assertStatus(429);
    }
}
