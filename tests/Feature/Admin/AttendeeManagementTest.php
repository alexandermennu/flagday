<?php

namespace Tests\Feature\Admin;

use App\Enums\AttendeeStatus;
use App\Mail\AttendeeReminder;
use App\Mail\RsvpConfirmation;
use App\Models\Attendee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AttendeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'VIP',
            'last_name' => 'Guest',
            'email' => 'vip@example.com',
            'organization' => 'Ministry of Education',
            'position' => 'Director',
            'status' => 'pending',
        ], $overrides);
    }

    public function test_admin_can_create_an_attendee(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        $response = $this->post(route('admin.attendees.store'), $this->payload());

        $response->assertRedirect(route('admin.attendees.index'));
        $this->assertDatabaseHas('attendees', ['email' => 'vip@example.com', 'status' => 'pending']);
        Mail::assertNotSent(RsvpConfirmation::class);
    }

    public function test_setting_status_to_confirmed_sends_ticket_email(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        $attendee = Attendee::factory()->create(['status' => AttendeeStatus::Pending]);

        $this->put(route('admin.attendees.update', $attendee), $this->payload([
            'first_name' => $attendee->first_name,
            'last_name' => $attendee->last_name,
            'email' => $attendee->email,
            'organization' => $attendee->organization,
            'position' => $attendee->position,
            'status' => 'confirmed',
        ]));

        Mail::assertSent(RsvpConfirmation::class, fn ($mail) => $mail->attendee->is($attendee));
    }

    public function test_editing_an_already_confirmed_attendee_does_not_resend_ticket(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        $attendee = Attendee::factory()->confirmed()->create();

        $this->put(route('admin.attendees.update', $attendee), $this->payload([
            'first_name' => $attendee->first_name,
            'last_name' => 'Updated',
            'email' => $attendee->email,
            'organization' => $attendee->organization,
            'position' => $attendee->position,
            'status' => 'confirmed',
        ]));

        Mail::assertNotSent(RsvpConfirmation::class);
    }

    public function test_admin_can_create_a_declined_attendee_without_a_position(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.attendees.store'), $this->payload([
            'email' => 'declined@example.com',
            'position' => '',
            'status' => 'declined',
            'decline_reason' => 'Out of the country.',
        ]));

        $response->assertRedirect(route('admin.attendees.index'));
        $this->assertDatabaseHas('attendees', [
            'email' => 'declined@example.com',
            'status' => 'declined',
            'position' => '',
            'decline_reason' => 'Out of the country.',
        ]);
    }

    public function test_admin_can_delete_an_attendee(): void
    {
        $this->actingAsAdmin();
        $attendee = Attendee::factory()->create();

        $this->delete(route('admin.attendees.destroy', $attendee));

        $this->assertDatabaseMissing('attendees', ['id' => $attendee->id]);
    }

    public function test_bulk_reminder_only_emails_confirmed_attendees(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        $confirmed = Attendee::factory()->confirmed()->create();
        $pending = Attendee::factory()->create(['status' => AttendeeStatus::Pending]);

        $response = $this->post(route('admin.attendees.remind'), [
            'attendee_ids' => [$confirmed->id, $pending->id],
        ]);

        $response->assertRedirect(route('admin.attendees.index'));

        Mail::assertQueued(AttendeeReminder::class, fn ($mail) => $mail->attendee->is($confirmed));
        Mail::assertQueued(AttendeeReminder::class, 1);

        $this->assertNotNull($confirmed->fresh()->reminder_sent_at);
        $this->assertNull($pending->fresh()->reminder_sent_at);
    }

    public function test_responded_filter_excludes_pending_invitees(): void
    {
        $this->actingAsAdmin();

        $confirmed = Attendee::factory()->confirmed()->create();
        $declined = Attendee::factory()->declined()->create();
        $pending = Attendee::factory()->create(['status' => AttendeeStatus::Pending]);

        $response = $this->get(route('admin.attendees.index', ['status' => 'responded']));

        $response->assertOk();
        $response->assertSee($confirmed->email);
        $response->assertSee($declined->email);
        $response->assertDontSee($pending->email);
    }

    public function test_csv_export_neutralizes_formula_injection_payload(): void
    {
        $this->actingAsAdmin();

        Attendee::factory()->create([
            'first_name' => '=cmd|\'/c calc\'!A1',
            'last_name' => 'Attacker',
            'email' => 'attacker@example.com',
        ]);

        $response = $this->get(route('admin.attendees.export'));

        $response->assertOk();
        $this->assertStringContainsString("'=cmd", $response->streamedContent());
    }

    public function test_reports_page_loads_with_stats(): void
    {
        $this->actingAsAdmin();

        Attendee::factory()->confirmed()->create();
        Attendee::factory()->declined()->create();

        $response = $this->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats');
        $response->assertViewHas('byOrganization');
    }
}
