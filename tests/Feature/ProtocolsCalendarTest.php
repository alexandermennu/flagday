<?php

namespace Tests\Feature;

use App\Models\ScheduleEvent;
use App\Models\ScheduleProtocol;
use App\Models\ScheduleSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtocolsCalendarTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): self
    {
        return $this->actingAs(User::factory()->create());
    }

    // ------------------------------------------------------------------ homepage buttons

    public function test_homepage_hero_buttons_lead_to_program_sheets_and_protocols(): void
    {
        $html = $this->get(route('landing'))->assertOk()->getContent();
        $hero = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));

        $this->assertStringContainsString('href="'.route('class.show').'#events"', $hero);
        $this->assertStringContainsString('href="'.route('protocols.show').'"', $hero);
        $this->assertStringContainsString('View Program Sheets', $hero);
        $this->assertStringContainsString('Protocols &amp; Calendar', $hero);
    }

    public function test_rsvp_remains_available_elsewhere(): void
    {
        $this->get(route('rsvp.create'))->assertOk();
        $this->get(route('events.show', 'honors-ball'))->assertSee(route('rsvp.create'));
    }

    // ------------------------------------------------------------------ source content (seeded)

    public function test_every_protocol_from_the_source_is_shown_verbatim_in_order(): void
    {
        $response = $this->get(route('protocols.show'))->assertOk();

        $response->assertSeeInOrder([
            'From 8:00 a.m.', 'The students arrive at the Monrovia Christian Fellowship premise and assemble at the Reception Hall as per their listing to be robed for the academic procession.',
            'From 8:30 a.m.', 'The University Faculty arrive at Monrovia Christian Fellowship premise and assemble in the church guest room to robe.',
            'From 9:00 a.m.', 'Other Guests arrive at the Monrovia Christian Fellowship premise. Those who do not form part of the procession will be seated by the ushers.',
            'At 9:30 a.m.', 'the Board of Governors and special Guests arrive at Monrovia Christian Fellowship premise.',
            'At 10:15 a.m.', 'the Guest Speaker will arrive at the Monrovia Christian Fellowship premise.',
            'At 10:30 a.m.', 'The Academic Procession will commence.',
        ]);
        $this->assertSame(6, substr_count($response->getContent(), 'data-protocol'));
    }

    public function test_every_calendar_event_and_detail_from_the_source_is_shown(): void
    {
        $response = $this->get(route('protocols.show'))->assertOk();

        $response->assertSeeInOrder([
            'Rooted in Excellence, Rising to Global Challenges',
            'Friday, November 14, 2025', 'Commencement Rehearsal', '(Attendance Mandatory)', '3:30 p.m.', 'New Campus, Barrolle Practice Ground Road',
            'Saturday, November 22, 2025', 'Awards Night', '7:30 p.m. Prompt', 'Shatim Event Center, Duport Road',
            'Sunday, November 23, 2025', 'Baccalaureate Service', '2:00 p.m.', 'Monrovia Christian Fellowship', 'Speaker', 'Pastor Francis T. Neufville',
            'Tuesday, November 25, 2025', 'Commencement Convocation 2025', '10:00 a.m.', 'Monrovia Christian Fellowship', 'Guest Speaker', 'Hon. Christine N. Umutoni',
            'Color', 'Red &amp; White',
            'Seniors report:',
            'All guests must be in the Main Hall by 9:00 a.m. as doors will close at this time. Guests cannot hold seats and must be in their seats before the procession begins at 10:00 a.m. prompt.',
        ], escape: false);
        $this->assertSame(4, substr_count($response->getContent(), 'data-calendar-entry'));
    }

    public function test_previous_year_dates_are_flagged_until_confirmed(): void
    {
        $this->get(route('protocols.show'))
            ->assertSee('Awaiting confirmation for the Class of 2026.')
            ->assertSee('from the 2025 graduation program')
            ->assertSee('to be confirmed');

        ScheduleSetting::current()->update(['is_confirmed' => true]);

        $this->get(route('protocols.show'))
            ->assertDontSee('Awaiting confirmation')
            ->assertDontSee('to be confirmed');
    }

    // ------------------------------------------------------------------ admin: access

    public function test_only_administrators_can_manage_the_schedule(): void
    {
        $protocol = ScheduleProtocol::first();
        $event = ScheduleEvent::first();

        $this->get(route('admin.schedule.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.schedule.protocols.store'), ['time_label' => 'X', 'description' => 'Y'])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.schedule.protocols.destroy', $protocol))->assertRedirect(route('admin.login'));
        $this->put(route('admin.schedule.events.update', $event), ['title' => 'Hacked'])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.schedule.events.destroy', $event))->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('schedule_protocols', 6);
        $this->assertSame('Commencement Rehearsal', $event->fresh()->title);

        $this->admin()->get(route('admin.schedule.index'))->assertOk()->assertSee('Commencement Rehearsal')->assertSee('From 8:00 a.m.');
    }

    // ------------------------------------------------------------------ admin: protocols

    public function test_protocols_can_be_created_edited_reordered_unpublished_and_deleted(): void
    {
        $this->admin()->post(route('admin.schedule.protocols.store'), [
            'time_label' => 'At 10:45 a.m.',
            'description' => 'Graduates are seated.',
        ])->assertSessionHasNoErrors();

        $new = ScheduleProtocol::where('time_label', 'At 10:45 a.m.')->sole();
        $this->assertSame(7, $new->position);
        $this->get(route('protocols.show'))->assertSee('Graduates are seated.');

        // Edit, move to the top, and unpublish the first source protocol.
        $all = ScheduleProtocol::ordered()->get();
        $first = $all->first();
        $rows = $all->mapWithKeys(fn ($p) => [$p->id => ['time_label' => $p->time_label, 'description' => $p->description, 'is_published' => '1']])->all();
        $rows[$new->id]['description'] = 'Graduates are seated in the Main Hall.';
        $rows[$first->id]['is_published'] = '0';
        $order = array_merge([$new->id], $all->pluck('id')->reject(fn ($id) => $id === $new->id)->all());

        $this->put(route('admin.schedule.protocols.update'), ['protocols' => $rows, 'order' => $order])->assertSessionHasNoErrors();

        $this->assertSame($new->id, ScheduleProtocol::ordered()->first()->id);
        $page = $this->get(route('protocols.show'));
        $page->assertSeeInOrder(['Graduates are seated in the Main Hall.', 'From 8:30 a.m.']);
        $page->assertDontSee('From 8:00 a.m.');

        $this->delete(route('admin.schedule.protocols.destroy', $new))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('schedule_protocols', ['id' => $new->id]);
        $this->get(route('protocols.show'))->assertDontSee('Graduates are seated');
    }

    public function test_protocol_fields_are_validated(): void
    {
        $this->admin()->post(route('admin.schedule.protocols.store'), ['time_label' => '', 'description' => ''])
            ->assertSessionHasErrors(['time_label', 'description']);
        $this->assertDatabaseCount('schedule_protocols', 6);
    }

    // ------------------------------------------------------------------ admin: calendar

    public function test_calendar_events_can_be_created_and_edited(): void
    {
        $this->admin()->post(route('admin.schedule.events.store'), [
            'title' => 'Senior Dinner',
            'event_date' => '2026-11-20',
            'start_time' => '18:00',
            'end_time' => '21:00',
            'venue' => 'Main Campus Hall',
            'speaker_label' => 'Guest Speaker',
            'speaker' => 'Dr. Jane Doe',
            'guest_of_honor' => 'The University President',
            'attendance' => 'Seniors only',
            'dress_code' => 'Formal',
            'description' => 'An evening for the graduating class.',
            'notes_text' => "Arrive by 5:45 p.m.\n\nBring your invitation.",
            'is_published' => '1',
        ])->assertRedirect(route('admin.schedule.index'));

        $event = ScheduleEvent::where('title', 'Senior Dinner')->sole();
        $this->assertSame(['Arrive by 5:45 p.m.', 'Bring your invitation.'], $event->notes);

        $this->get(route('protocols.show'))->assertSeeInOrder([
            // Description sits directly under the title; then the aligned details; then the information lines.
            'Friday, November 20, 2026', 'Senior Dinner', '(Seniors only)', 'An evening for the graduating class.',
            '6:00 p.m. – 9:00 p.m.', 'Main Campus Hall', 'Guest Speaker', 'Dr. Jane Doe',
            'Guest of honor', 'The University President', 'Dress code', 'Formal', 'Arrive by 5:45 p.m.', 'Bring your invitation.',
        ]);

        $this->put(route('admin.schedule.events.update', $event), [
            'title' => 'Senior Dinner & Dance',
            'event_date' => '2026-11-21',
            'start_time' => '19:00',
            'venue' => 'Grand Ballroom',
            'speaker' => 'Dr. John Roe',
            'notes_text' => 'Doors open at 6:30 p.m.',
            'is_published' => '1',
        ])->assertSessionHasNoErrors();

        $this->get(route('protocols.show'))
            ->assertSee('Senior Dinner &amp; Dance', escape: false)
            ->assertSee('Saturday, November 21, 2026')
            ->assertSee('Grand Ballroom')
            ->assertSee('Dr. John Roe')
            ->assertSee('Doors open at 6:30 p.m.')
            ->assertDontSee('Main Campus Hall')
            ->assertDontSee('Bring your invitation.');
    }

    public function test_calendar_validation_rejects_bad_input(): void
    {
        $event = ScheduleEvent::first();

        $this->admin()->put(route('admin.schedule.events.update', $event), [
            'title' => '', 'event_date' => 'not a date', 'start_time' => '18:00', 'end_time' => '17:00',
        ])->assertSessionHasErrors(['title', 'event_date', 'end_time']);

        $this->assertSame('Commencement Rehearsal', $event->fresh()->title);
    }

    public function test_calendar_events_can_be_unpublished_reordered_sorted_and_deleted(): void
    {
        [$rehearsal, $awards, $bacc, $convocation] = ScheduleEvent::ordered()->get()->all();
        $this->admin();

        // Manual order: Convocation first; unpublish Awards Night.
        $this->put(route('admin.schedule.events.order'), [
            'order' => [$convocation->id, $rehearsal->id, $awards->id, $bacc->id],
            'published' => [$rehearsal->id => '1', $awards->id => '0', $bacc->id => '1', $convocation->id => '1'],
        ])->assertSessionHasNoErrors();

        $page = $this->get(route('protocols.show'));
        $page->assertSeeInOrder(['Commencement Convocation 2025', 'Commencement Rehearsal', 'Baccalaureate Service']);
        $page->assertDontSee('Awards Night');

        // Sort chronologically again.
        $this->post(route('admin.schedule.events.sort'))->assertSessionHasNoErrors();
        $this->assertSame([$rehearsal->id, $awards->id, $bacc->id, $convocation->id], ScheduleEvent::ordered()->pluck('id')->all());

        $this->delete(route('admin.schedule.events.destroy', $bacc))->assertRedirect(route('admin.schedule.index'));
        $this->assertDatabaseMissing('schedule_events', ['id' => $bacc->id]);
        $this->get(route('protocols.show'))->assertDontSee('Baccalaureate Service');
    }

    public function test_page_settings_are_editable(): void
    {
        $this->admin()->put(route('admin.schedule.settings.update'), [
            'theme' => 'A New 2026 Theme',
            'source_year' => '2025',
            'is_confirmed' => '0',
        ])->assertSessionHasNoErrors();

        $this->get(route('protocols.show'))->assertSee('A New 2026 Theme')->assertSee('Awaiting confirmation');

        $this->put(route('admin.schedule.settings.update'), ['theme' => '', 'is_confirmed' => '1']);
        $this->get(route('protocols.show'))->assertDontSee('Theme (')->assertDontSee('Awaiting confirmation');
    }
}
