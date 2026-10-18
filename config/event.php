<?php

/*
| The event the RSVP form, PDF ticket, calendar file, and emails are for — the
| Starz University Class of 2026 Honors Ball. Date, time, and venue default to
| empty and render as "To be announced" until set (see App\Support\Graduation).
*/

return [

    'name' => env('EVENT_NAME', 'Starz University Honors Ball 2026'),

    'date' => env('EVENT_DATE'),

    'start_time' => env('EVENT_START_TIME'),

    'end_time' => env('EVENT_END_TIME'),

    'timezone' => env('EVENT_TIMEZONE', 'Africa/Monrovia'),

    'venue' => env('EVENT_VENUE'),

    'venue_address' => env('EVENT_VENUE_ADDRESS'),

    'dress_code' => env('EVENT_DRESS_CODE'),

    'theme' => env('EVENT_THEME', 'Class of 2026'),

    'contact_phone' => env('EVENT_CONTACT_PHONE'),

    'contact_phone_secondary' => env('EVENT_CONTACT_PHONE_SECONDARY'),

    'contact_whatsapp' => env('EVENT_CONTACT_WHATSAPP'),

    'contact_email' => env('EVENT_CONTACT_EMAIL'),

    'contact_website' => env('EVENT_CONTACT_WEBSITE'),

    // The passcode gate in front of the RSVP form is off for now — RSVP is open to
    // anyone with the link. Set EVENT_RSVP_PASSCODE_REQUIRED=true to turn it back on.
    'rsvp_passcode_required' => (bool) env('EVENT_RSVP_PASSCODE_REQUIRED', false),

    'rsvp_passcode' => env('EVENT_RSVP_PASSCODE', ''),

];
