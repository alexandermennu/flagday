<?php

/*
|--------------------------------------------------------------------------
| Starz University Class of 2026 — Digital Graduation Program
|--------------------------------------------------------------------------
|
| Public-facing content for the landing page and event pages. Every date, time,
| venue, and dress code is read from the environment and left empty by default —
| an empty value renders as "To be announced" on the site, so nothing is shown
| until the real detail is confirmed. The Honors Ball shares the EVENT_* variables
| with config/event.php, since it is the event the RSVP form, tickets, and
| emails are for.
|
| `program_pdf` takes a URL or a path relative to /public (e.g. "programs/convocation.pdf");
| leave it empty and the page shows "Program coming soon".
|
| `flipbook` shows the event's published program in the page-turning viewer
| (/events/{slug}/program/view) instead of opening the raw PDF. It works for any
| published program; set it to true to enable it for an event.
|
*/

return [

    'institution' => env('GRAD_INSTITUTION', 'Starz University'),

    'class_year' => env('GRAD_CLASS_YEAR', '2026'),

    'tagline' => env('GRAD_TAGLINE', 'Celebrating the achievements of our graduating class.'),

    'contact_email' => env('GRAD_CONTACT_EMAIL'),

    'contact_phone' => env('GRAD_CONTACT_PHONE'),

    // Largest Convocation program PDF an administrator may upload, in KB (default 20 MB).
    // PHP's upload_max_filesize / post_max_size must be at least this large, and uploads
    // are stored in the database, so keep it well under the database's packet limit.
    'program_pdf_max_kb' => (int) env('PROGRAM_PDF_MAX_KB', 20480),

    // Largest gallery photo an administrator may upload, in KB (default 10 MB). Photos are
    // resized on upload, so this only limits what the server has to accept.
    'gallery_photo_max_kb' => (int) env('GALLERY_PHOTO_MAX_KB', 10240),

    'events' => [

        'honors-ball' => [
            'name' => 'Honors Ball',
            'kicker' => 'An Evening of Distinction',
            'summary' => 'A formal evening celebrating the academic excellence and achievements of the graduating class.',
            'description' => 'The Honors Ball brings graduates, faculty, family, and friends together for a formal evening of recognition and celebration as the Class of 2026 prepares to cross the stage.',
            'date' => env('EVENT_DATE'),
            'time' => env('EVENT_START_TIME'),
            'venue' => env('EVENT_VENUE'),
            'venue_address' => env('EVENT_VENUE_ADDRESS'),
            'dress_code' => env('EVENT_DRESS_CODE'),
            'program_pdf' => env('HONORS_BALL_PROGRAM_PDF'),
            'rsvp' => true,
            'flipbook' => true,
        ],

        'baccalaureate' => [
            'name' => 'Baccalaureate',
            'kicker' => 'A Service of Reflection',
            'summary' => 'A service of reflection and thanksgiving honoring the journey of the graduating class.',
            'description' => 'The Baccalaureate service offers graduates and their families a moment of reflection, gratitude, and encouragement before Convocation.',
            'date' => env('BACCALAUREATE_DATE'),
            'time' => env('BACCALAUREATE_TIME'),
            'venue' => env('BACCALAUREATE_VENUE'),
            'venue_address' => env('BACCALAUREATE_VENUE_ADDRESS'),
            'dress_code' => env('BACCALAUREATE_DRESS_CODE'),
            'program_pdf' => env('BACCALAUREATE_PROGRAM_PDF'),
            'rsvp' => false,
            'flipbook' => true,
        ],

        'convocation' => [
            'name' => 'Convocation',
            'kicker' => 'The Commencement Ceremony',
            'summary' => 'The official ceremony at which degrees are conferred upon the Class of 2026.',
            'description' => 'Convocation is the culminating ceremony of the graduation season, where members of the Class of 2026 are formally presented and their degrees conferred.',
            'date' => env('CONVOCATION_DATE'),
            'time' => env('CONVOCATION_TIME'),
            'venue' => env('CONVOCATION_VENUE'),
            'venue_address' => env('CONVOCATION_VENUE_ADDRESS'),
            'dress_code' => env('CONVOCATION_DRESS_CODE'),
            'program_pdf' => env('CONVOCATION_PROGRAM_PDF'),
            'rsvp' => false,
            'flipbook' => false,
        ],

    ],

];
