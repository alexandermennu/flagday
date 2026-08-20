<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * The 179th Flag Day information hub — a standalone page separate from the RSVP
 * landing page. Programme and FAQ content below is placeholder data pending
 * real content from the Ministry. Schools reflect the official parade
 * line-up; partners reflect the official partner list.
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
            ['position' => 1, 'name' => 'St. Theresa Convent Catholic School', 'rank' => "178th First Place", 'contact' => 'Randell Street, Monrovia'],
            ['position' => 2, 'name' => 'William V.S. Tubman High Sch.', 'rank' => "178th Second Place", 'contact' => '12th Street, Sinkor'],
            ['position' => 3, 'name' => 'Emily Foundation Academy', 'rank' => "178th Third Place", 'contact' => 'Brewerville, Montserrado Co.'],
            ['position' => 4, 'name' => 'Ann Sandell Independent High School', 'rank' => "178th Fourth Place", 'contact' => 'Police Academy Road'],
            ['position' => 5, 'name' => 'Monrovia College And Industrial Training School, Inc', 'rank' => "178th Five Place", 'contact' => 'Clay Street Monrovia'],
            ['position' => 6, 'name' => 'College of West Africa', 'rank' => 'No Ranking', 'contact' => 'Ashmun Street, Monrovia'],
            ['position' => 7, 'name' => 'Gray D. Allison High school', 'rank' => 'No Ranking', 'contact' => 'UN Drive, BTC/Monrovia'],
            ['position' => 8, 'name' => 'Boatswain High School', 'rank' => 'No Ranking', 'contact' => 'Jamaica Road, Bushrod Island'],
            ['position' => 9, 'name' => 'D. Tweh Memorial High School', 'rank' => 'No Ranking', 'contact' => 'New Kru Town'],
            ['position' => 10, 'name' => 'E. Jonathan Goodridge High School', 'rank' => 'No Ranking', 'contact' => 'Bradnesville Estate'],
            ['position' => 11, 'name' => 'Special Project High School', 'rank' => 'No Ranking', 'contact' => 'Stephen A. Tolbert Estate'],
            ['position' => 12, 'name' => 'Cathedral Catholic School', 'rank' => 'No Ranking', 'contact' => 'Ashmun & Broad Street, Monrovia'],
            ['position' => 13, 'name' => 'Star International School System', 'rank' => 'No Ranking', 'contact' => 'Rehab Paynesville'],
            ['position' => 14, 'name' => 'Muslim Congress High School', 'rank' => 'No Ranking', 'contact' => 'Mechlin Street, Monrovia'],
            ['position' => 15, 'name' => 'William Gabriel Kpolleh High Sch.', 'rank' => 'No Ranking', 'contact' => 'New Georgia Estate'],
            ['position' => 16, 'name' => 'World Wide Mission School', 'rank' => 'No Ranking', 'contact' => 'Newport Street, Monrovia'],
            ['position' => 17, 'name' => 'Annie Banks Williams', 'rank' => 'No Ranking', 'contact' => 'Brewerville, Montserrado Co.'],
            ['position' => 18, 'name' => 'Living Water Baptist School', 'rank' => 'No Ranking', 'contact' => '12th Street, Sinkor'],
            ['position' => 19, 'name' => 'General Automobile Academy', 'rank' => 'No Ranking', 'contact' => 'Paynesville'],
            ['position' => 20, 'name' => 'Elizabeth Crawford Memorial Sch.', 'rank' => 'No Ranking', 'contact' => 'Carey & Newport Street, Monrovia'],
            ['position' => 21, 'name' => 'Richard M. Nixon Institute', 'rank' => 'No Ranking', 'contact' => 'Capitol Hill, Monrovia'],
            ['position' => 22, 'name' => "Winners' Institute", 'rank' => 'No Ranking', 'contact' => 'Paynesville'],
            ['position' => 23, 'name' => 'A.M.E. Zion Academy', 'rank' => 'No Ranking', 'contact' => 'Benson Street'],
            ['position' => 24, 'name' => 'Prinsia Memorial Institute', 'rank' => 'No Ranking', 'contact' => 'Carpetville, Banjor'],
            ['position' => 25, 'name' => 'Effort Baptist School', 'rank' => 'No Ranking', 'contact' => 'Pynesville'],
            ['position' => 26, 'name' => 'June L. Moore Public School', 'rank' => 'No Ranking', 'contact' => 'Gbengbar Town, RIA Highway'],
            ['position' => 27, 'name' => 'Soltiamon Christian School Sys.', 'rank' => 'No Ranking', 'contact' => 'Tokpa Camp, Old Road'],
            ['position' => 28, 'name' => 'G. W. Gibson High School', 'rank' => 'No Ranking', 'contact' => 'Capitol Bye-Pass/ Monrovia'],
            ['position' => 29, 'name' => 'Liberia Dujar High School', 'rank' => 'No Ranking', 'contact' => 'Grassfield Community'],
            ['position' => 30, 'name' => 'SMS Muslim Congress', 'rank' => 'No Ranking', 'contact' => 'Garnersville Township'],
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
            'Wahala Publishing House',
            'National Tourism Authority',
            'Governance Commission',
        ];

        return view('flag-day', compact('programme', 'schools', 'faqs', 'partners'));
    }
}
