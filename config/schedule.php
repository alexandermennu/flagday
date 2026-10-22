<?php

/*
|--------------------------------------------------------------------------
| Protocols & Calendar page (/protocols-calendar)
|--------------------------------------------------------------------------
|
| Transcribed from the university's graduation program ("Protocols" and
| "Calendar of Events"). That source is the Class of 2025 program, so its
| dates are NOT the Class of 2026 schedule.
|
| While `confirmed` is false, the page shows an "awaiting confirmation" notice
| and labels every date as a 2025 date to be confirmed. When the approved 2026
| schedule is available: update the entries below (wording, dates, times,
| venues, speakers), then set SCHEDULE_CONFIRMED=true in the environment (or
| `confirmed` here) to remove the notices.
|
| Calendar entries: leave any field null if it does not apply — it is simply
| not shown. `speaker_label` is the label printed before the speaker's name.
|
*/

return [

    'confirmed' => (bool) env('SCHEDULE_CONFIRMED', false),

    // The source year of the details below, shown in the confirmation notices.
    'source_year' => '2025',

    'theme' => 'Rooted in Excellence, Rising to Global Challenges',

    // In order of arrival, as listed in the source.
    'protocols' => [
        ['time' => 'From 8:00 a.m.', 'text' => 'The students arrive at the Monrovia Christian Fellowship premise and assemble at the Reception Hall as per their listing to be robed for the academic procession.'],
        ['time' => 'From 8:30 a.m.', 'text' => 'The University Faculty arrive at Monrovia Christian Fellowship premise and assemble in the church guest room to robe.'],
        ['time' => 'From 9:00 a.m.', 'text' => 'Other Guests arrive at the Monrovia Christian Fellowship premise. Those who do not form part of the procession will be seated by the ushers.'],
        ['time' => 'At 9:30 a.m.', 'text' => 'The Board of Governors and special Guests arrive at Monrovia Christian Fellowship premise.'],
        ['time' => 'At 10:15 a.m.', 'text' => 'The Guest Speaker will arrive at the Monrovia Christian Fellowship premise.'],
        ['time' => 'At 10:30 a.m.', 'text' => 'The Academic Procession will commence.'],
    ],

    'calendar' => [
        [
            'date' => 'Friday, November 14, 2025',
            'event' => 'Commencement Rehearsal',
            'tag' => 'Attendance Mandatory',
            'time' => '3:30 p.m.',
            'venue' => 'New Campus, Barrolle Practice Ground Road',
            'speaker' => null,
            'speaker_label' => null,
            'notes' => [],
            'color' => null,
        ],
        [
            'date' => 'Saturday, November 22, 2025',
            'event' => 'Awards Night',
            'tag' => null,
            'time' => '7:30 p.m. Prompt',
            'venue' => 'Shatim Event Center, Duport Road',
            'speaker' => null,
            'speaker_label' => null,
            'notes' => [],
            'color' => null,
        ],
        [
            'date' => 'Sunday, November 23, 2025',
            'event' => 'Baccalaureate Service',
            'tag' => null,
            'time' => '2:00 PM',
            'venue' => 'Monrovia Christian Fellowship',
            'speaker' => 'Pastor Francis T. Neufville',
            'speaker_label' => 'Speaker',
            'notes' => [],
            'color' => null,
        ],
        [
            'date' => 'Tuesday, November 25, 2025',
            'event' => 'Commencement Convocation 2025',
            'tag' => null,
            'time' => '10:00 a.m.',
            'venue' => 'Monrovia Christian Fellowship',
            'speaker' => 'Hon. Christine N. Umutoni',
            'speaker_label' => 'Guest Speaker',
            'notes' => [
                'All guests must be in the Main Hall by 9:00 a.m. as doors will close at this time. Guests cannot hold seats and must be in their seats before the procession begins at 10:00 a.m. prompt.',
            ],
            'color' => 'Red & White',
        ],
    ],

];
