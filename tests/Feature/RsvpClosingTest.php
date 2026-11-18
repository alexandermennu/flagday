<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\RsvpSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RsvpClosingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withSession(['rsvp_access_granted' => true]);
    }

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

    private function setRsvpOpen(bool $open): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.rsvp-status.update'), ['is_open' => $open ? '1' : '0'])
            ->assertSessionHasNoErrors();
        auth()->logout();
    }

    public function test_rsvp_is_open_by_default(): void
    {
        $this->assertTrue(RsvpSetting::isOpen());
        $this->get(route('rsvp.create'))->assertOk()->assertSee('Honors Ball RSVP')->assertDontSee('RSVP is closed');
    }

    public function test_a_closed_rsvp_shows_a_notice_instead_of_the_form(): void
    {
        $this->setRsvpOpen(false);

        $this->get(route('rsvp.create'))
            ->assertOk()
            ->assertSee('RSVP is closed')
            ->assertDontSee('name="first_name"', escape: false);
    }

    public function test_a_closed_rsvp_refuses_submissions_without_saving_or_emailing(): void
    {
        Mail::fake();
        $this->setRsvpOpen(false);

        // e.g. a form opened before the RSVP closed, submitted afterwards.
        $this->post(route('rsvp.store'), $this->payload())->assertRedirect(route('rsvp.create'));

        $this->assertDatabaseCount('attendees', 0);
        Mail::assertNothingSent();
    }

    public function test_a_closed_rsvp_does_not_let_existing_attendees_change_their_response(): void
    {
        Mail::fake();
        $this->post(route('rsvp.store'), $this->payload())->assertRedirect(route('rsvp.thank-you'));
        $this->setRsvpOpen(false);

        $this->post(route('rsvp.store'), $this->payload(['status' => 'declined']));

        $this->assertSame('confirmed', Attendee::sole()->status->value);
    }

    public function test_the_passcode_page_is_closed_too(): void
    {
        config(['event.rsvp_passcode_required' => true, 'event.rsvp_passcode' => 'secret']);
        $this->setRsvpOpen(false);

        $this->get(route('rsvp.access'))->assertOk()->assertSee('RSVP is closed');
        $this->post(route('rsvp.access.store'), ['passcode' => 'secret'])->assertRedirect(route('rsvp.create'));
    }

    public function test_public_pages_show_the_rsvp_as_closed_and_stop_linking_to_the_form(): void
    {
        $formLink = 'href="'.route('rsvp.create').'"';
        foreach ([route('landing'), route('class.show'), route('events.show', 'honors-ball')] as $url) {
            $this->get($url)->assertOk()->assertSee($formLink, escape: false);
        }

        $this->setRsvpOpen(false);

        foreach ([route('landing'), route('class.show'), route('events.show', 'honors-ball')] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee($formLink, escape: false)
                ->assertSee('RSVP Closed');
        }
        // The landing page has no footer; the other pages do.
        $this->get(route('class.show'))->assertSee('RSVP — Honors Ball (closed)');

        $this->get(route('events.show', 'honors-ball'))
            ->assertDontSee('Go to the RSVP form')
            ->assertSee('The RSVP for the Honors Ball is closed.');
        $this->get(route('class.show'))->assertSee('RSVP closed')->assertDontSee('RSVP Now');
    }

    public function test_reopening_accepts_responses_again(): void
    {
        Mail::fake();
        $this->setRsvpOpen(false);
        $this->setRsvpOpen(true);

        $this->get(route('rsvp.create'))->assertOk()->assertDontSee('RSVP is closed');
        $this->get(route('events.show', 'honors-ball'))->assertSee('RSVP for the Honors Ball')->assertDontSee('RSVP Closed');
        $this->post(route('rsvp.store'), $this->payload())->assertRedirect(route('rsvp.thank-you'));
        $this->assertDatabaseCount('attendees', 1);
    }

    public function test_the_dashboard_shows_the_status_and_toggle(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Honors Ball RSVP')->assertSee('Close RSVP');

        $this->put(route('admin.rsvp-status.update'), ['is_open' => '0'])->assertSessionHas('success');
        $this->get(route('admin.dashboard'))->assertSee('Closed')->assertSee('Open RSVP');
    }

    public function test_only_admins_can_open_or_close_the_rsvp(): void
    {
        $this->put(route('admin.rsvp-status.update'), ['is_open' => '0'])->assertRedirect(route('admin.login'));

        $this->assertTrue(RsvpSetting::isOpen());
    }
}
