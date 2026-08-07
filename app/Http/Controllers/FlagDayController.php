<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * The 179th Flag Day information hub — a standalone page separate from the RSVP
 * landing page. Programme, schools, FAQ, and partner content below is placeholder
 * data pending real content from the Ministry.
 */
class FlagDayController extends Controller
{
    public function show(): View
    {
        $programme = [
            ['time' => '8:00 AM', 'title' => 'Registration'],
            ['time' => '9:00 AM', 'title' => 'Arrival of Guests'],
            ['time' => '10:00 AM', 'title' => 'Arrival of the President'],
            ['time' => '10:05 AM', 'title' => 'Opening Prayer'],
            ['time' => '10:15 AM', 'title' => 'National Anthem'],
            ['time' => '10:20 AM', 'title' => 'Flag Raising Ceremony'],
            ['time' => '10:30 AM', 'title' => "President's Address"],
            ['time' => '10:45 AM', 'title' => 'National Orator Address'],
            ['time' => '11:15 AM', 'title' => 'Cultural Performance'],
            ['time' => '11:45 AM', 'title' => 'Awards & Recognitions'],
            ['time' => '12:15 PM', 'title' => 'Closing Remarks'],
        ];

        $schools = [
            ['name' => 'Starz University', 'county' => 'Montserrado', 'category' => 'Private'],
            ['name' => 'Tubman High School', 'county' => 'Montserrado', 'category' => 'Public'],
            ['name' => 'Ganta United Methodist High', 'county' => 'Nimba', 'category' => 'Faith-Based'],
            ['name' => 'Harbel Public School', 'county' => 'Margibi', 'category' => 'Public'],
            ['name' => 'Zorzor Multi-Purpose High', 'county' => 'Lofa', 'category' => 'Public'],
            ['name' => 'Pleebo Sacred Heart High', 'county' => 'Maryland', 'category' => 'Faith-Based'],
            ['name' => 'Buchanan High School', 'county' => 'Grand Bassa', 'category' => 'Public'],
            ['name' => 'Barclay Training Center', 'county' => 'Grand Kru', 'category' => 'Public'],
        ];

        $faqs = [
            [
                'question' => 'Is attendance by invitation only?',
                'answer' => 'Yes. This year\'s ceremony is open to invited officials, dignitaries, participating schools, and their guests. Bring your confirmation ID or digital pass to the entrance.',
            ],
            [
                'question' => 'What time should guests arrive?',
                'answer' => 'Registration opens at 8:00 AM. Guests are asked to be seated no later than '.date('g:i A', strtotime(config('event.start_time'))).' as the programme begins promptly.',
            ],
            [
                'question' => 'Is parking available?',
                'answer' => 'Yes, ample parking is available on-site at '.config('event.venue').'. Security personnel will be on hand to direct vehicles.',
            ],
            [
                'question' => 'What should I wear?',
                'answer' => 'The dress code for this year\'s celebration is '.config('event.dress_code').'.',
            ],
        ];

        $partners = [
            'Ministry of Education',
            'National Flag Day Committee',
            'UNICEF',
            'UNDP',
            'USAID',
            'Alliance Française',
        ];

        $counties = collect($schools)->pluck('county')->unique()->sort()->values()->all();
        $categories = collect($schools)->pluck('category')->unique()->sort()->values()->all();

        return view('flag-day', compact('programme', 'schools', 'faqs', 'partners', 'counties', 'categories'));
    }
}
