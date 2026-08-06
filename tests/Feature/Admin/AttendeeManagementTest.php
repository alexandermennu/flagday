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

    public function test_admin_can_create_an_attendee(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        $response = $this->post(route('admin.attendees.store'), [
            'full_name' => 'VIP Guest',
            'email' => 'vip@example.com',
            'status' => 'pending',
        ]);

        $response->assertRedirect(route('admin.attendees.index'));
        $this->assertDatabaseHas('attendees', ['email' => 'vip@example.com', 'status' => 'pending']);
        Mail::assertNotQueued(RsvpConfirmation::class);
    }

    public function test_setting_status_to_confirmed_sends_ticket_email(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        $attendee = Attendee::factory()->create(['status' => AttendeeStatus::Pending]);

        $this->put(route('admin.attendees.update', $attendee), [
            'full_name' => $attendee->full_name,
            'email' => $attendee->email,
            'status' => 'confirmed',
        ]);

        Mail::assertQueued(RsvpConfirmation::class, fn ($mail) => $mail->attendee->is($attendee));
    }

    public function test_editing_an_already_confirmed_attendee_does_not_resend_ticket(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        $attendee = Attendee::factory()->confirmed()->create();

        $this->put(route('admin.attendees.update', $attendee), [
            'full_name' => 'Updated Name',
            'email' => $attendee->email,
            'status' => 'confirmed',
        ]);

        Mail::assertNotQueued(RsvpConfirmation::class);
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

    public function test_csv_export_neutralizes_formula_injection_payload(): void
    {
        $this->actingAsAdmin();

        Attendee::factory()->create([
            'full_name' => '=cmd|\'/c calc\'!A1',
            'email' => 'attacker@example.com',
        ]);

        $response = $this->get(route('admin.attendees.export'));

        $response->assertOk();
        $this->assertStringContainsString("'=cmd", $response->streamedContent());
    }
}
