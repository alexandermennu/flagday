<?php

namespace Tests\Feature\Admin;

use App\Models\Attendee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_through_login_and_lands_back_on_checkin_page(): void
    {
        $user = User::factory()->create();
        $attendee = Attendee::factory()->confirmed()->create();

        $checkinUrl = route('admin.checkin.show', $attendee->invite_token);

        $this->get($checkinUrl)->assertRedirect(route('admin.login'));

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($checkinUrl);
    }

    public function test_visiting_checkin_url_stamps_checked_in_at(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendee = Attendee::factory()->confirmed()->create();
        $this->assertNull($attendee->checked_in_at);

        $response = $this->get(route('admin.checkin.show', $attendee->invite_token));

        $response->assertOk();
        $this->assertNotNull($attendee->fresh()->checked_in_at);
    }

    public function test_visiting_checkin_url_twice_does_not_overwrite_original_timestamp(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendee = Attendee::factory()->confirmed()->create();

        $this->get(route('admin.checkin.show', $attendee->invite_token));
        $firstCheckIn = $attendee->fresh()->checked_in_at;

        $this->travel(5)->minutes();
        $this->get(route('admin.checkin.show', $attendee->invite_token));

        $this->assertTrue($firstCheckIn->equalTo($attendee->fresh()->checked_in_at));
    }
}
