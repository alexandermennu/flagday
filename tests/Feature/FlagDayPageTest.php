<?php

namespace Tests\Feature;

use Tests\TestCase;

class FlagDayPageTest extends TestCase
{
    public function test_flag_day_page_is_publicly_accessible(): void
    {
        $this->get(route('flag-day.show'))->assertOk();
    }

    public function test_flag_day_page_shows_event_details_and_key_sections(): void
    {
        $response = $this->get(route('flag-day.show'));

        $response->assertOk();
        $response->assertSee('179th National');
        $response->assertSee(config('event.venue'));
        $response->assertSee('Programme of Events');
        $response->assertSee('Participating Schools');
        $response->assertSee('Frequently Asked Questions');
    }
}
