<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PublicEventDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Config values that must NOT leak onto a page once a program is published.
        config([
            'graduation.events.honors-ball.venue' => 'Config Ballroom',
            'graduation.events.honors-ball.date' => '2026-01-01',
            'graduation.events.honors-ball.dress_code' => 'Black Tie',
        ]);
    }

    /** Creates a program of the given type and publishes it through the admin. */
    private function publish(string $type, array $details): Program
    {
        $this->actingAs(User::factory()->create());

        $program = Program::factory()->create(array_merge(['type' => $type], $details));

        if ($type === 'convocation') {
            $this->put(route('admin.programs.update', $program), array_merge(Program::factory()->raw(), $details, [
                'type' => 'convocation',
                'program_pdf' => UploadedFile::fake()->createWithContent('program.pdf', "%PDF-1.4\n%%EOF\n"),
            ]))->assertSessionHasNoErrors();
        }

        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();
        auth()->logout();

        return $program->fresh();
    }

    /**
     * The page's own details block — excluding the "More Graduation Events" cards further
     * down (on the other event pages), which correctly show the other events' details.
     */
    private function eventDetails(string $slug): string
    {
        $html = $this->get(route('events.show', $slug))->assertOk()->getContent();
        $start = strpos($html, 'Event Details</h2>');
        // The Honors Ball page has a gallery instead of "More Graduation Events".
        $end = strpos($html, 'More Graduation Events') ?: strpos($html, '</main>');
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        return substr($html, $start, $end - $start);
    }

    public function test_honors_ball_page_shows_the_published_programs_details(): void
    {
        $this->publish('honors-ball', [
            'event_date' => '2026-11-21',
            'event_time' => '19:00',
            'venue_name' => 'Starlight Ballroom',
            'venue_address' => '12 Tubman Boulevard, Monrovia',
        ]);

        $this->get(route('events.show', 'honors-ball'))
            ->assertOk()
            ->assertSee('Saturday, November 21, 2026')
            ->assertSee('7:00 PM')
            ->assertSee('Starlight Ballroom')
            ->assertSee('12 Tubman Boulevard, Monrovia')
            ->assertDontSee('Config Ballroom')
            // Dress code isn't on the program form, so it still comes from config.
            ->assertSee('Black Tie')
            // PDF buttons and RSVP are unchanged.
            ->assertSee(route('events.program', 'honors-ball'))
            ->assertSee(route('events.program', ['honors-ball', 'download' => 1]))
            ->assertSee('RSVP for the Honors Ball');
    }

    public function test_each_event_page_shows_only_its_own_programs_details(): void
    {
        $this->publish('honors-ball', ['event_date' => '2026-11-21', 'event_time' => '19:00', 'venue_name' => 'Starlight Ballroom', 'venue_address' => 'Ballroom Road']);
        $this->publish('baccalaureate', ['event_date' => '2026-12-06', 'event_time' => '10:00', 'venue_name' => 'Unity Chapel', 'venue_address' => 'Chapel Street']);
        $this->publish('convocation', ['event_date' => '2026-12-12', 'event_time' => '09:30', 'venue_name' => 'Convocation Arena', 'venue_address' => 'Arena Avenue']);

        $baccalaureate = $this->eventDetails('baccalaureate');
        foreach (['Sunday, December 6, 2026', '10:00 AM', 'Unity Chapel', 'Chapel Street'] as $expected) {
            $this->assertStringContainsString($expected, $baccalaureate);
        }
        foreach (['Starlight Ballroom', 'Ballroom Road', 'Convocation Arena', 'Arena Avenue', 'November 21', 'December 12'] as $other) {
            $this->assertStringNotContainsString($other, $baccalaureate);
        }
        $this->get(route('events.show', 'baccalaureate'))->assertDontSee('RSVP for the Baccalaureate');

        $convocation = $this->eventDetails('convocation');
        foreach (['Saturday, December 12, 2026', '9:30 AM', 'Convocation Arena', 'Arena Avenue'] as $expected) {
            $this->assertStringContainsString($expected, $convocation);
        }
        foreach (['Starlight Ballroom', 'Unity Chapel', 'Chapel Street', 'November 21', 'December 6'] as $other) {
            $this->assertStringNotContainsString($other, $convocation);
        }

        $this->get(route('events.show', 'convocation'))
            // The uploaded PDF stays viewable and downloadable.
            ->assertSee(route('events.program', 'convocation'))
            ->assertSee(route('events.program', ['convocation', 'download' => 1]));

        // The Class of 2026 page's event cards read the same details.
        $this->get(route('class.show'))
            ->assertSee('Starlight Ballroom')
            ->assertSee('Unity Chapel')
            ->assertSee('Convocation Arena');
    }

    public function test_empty_program_fields_show_to_be_announced_rather_than_config_values(): void
    {
        $this->publish('honors-ball', [
            'event_date' => null,
            'event_time' => null,
            'venue_name' => null,
            'venue_address' => null,
        ]);

        $details = $this->eventDetails('honors-ball');

        $this->assertStringNotContainsString('Config Ballroom', $details);
        $this->assertStringNotContainsString('January 1, 2026', $details);
        $this->assertSame(3, preg_match_all('/>\s*To be announced\s*</', $details), 'Date, time and venue all fall back to the placeholder.');
        $this->assertStringContainsString('Black Tie', $details);
    }

    public function test_republishing_updates_the_public_page(): void
    {
        $program = $this->publish('baccalaureate', ['event_date' => '2026-12-06', 'venue_name' => 'Unity Chapel']);

        $this->actingAs(User::factory()->create());
        $this->put(route('admin.programs.update', $program), array_merge(Program::factory()->raw(), [
            'type' => 'baccalaureate',
            'event_date' => '2026-12-13',
            'event_time' => '11:00',
            'venue_name' => 'Grand Cathedral',
            'venue_address' => 'Cathedral Square',
        ]))->assertSessionHasNoErrors();
        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();

        $this->get(route('events.show', 'baccalaureate'))
            ->assertSee('Sunday, December 13, 2026')
            ->assertSee('11:00 AM')
            ->assertSee('Grand Cathedral')
            ->assertDontSee('Unity Chapel');
    }

    public function test_unpublished_programs_do_not_affect_the_public_page(): void
    {
        Program::factory()->create(['type' => 'convocation', 'venue_name' => 'Draft Only Hall']);

        $this->get(route('events.show', 'convocation'))
            ->assertOk()
            ->assertDontSee('Draft Only Hall')
            ->assertSee('Program coming soon');
    }

    public function test_the_most_recently_published_program_of_a_type_is_used(): void
    {
        $this->publish('convocation', ['venue_name' => 'Older Venue']);
        $this->travel(1)->minutes();
        $this->publish('convocation', ['venue_name' => 'Newer Venue']);

        $this->get(route('events.show', 'convocation'))
            ->assertSee('Newer Venue')
            ->assertDontSee('Older Venue');
    }
}
