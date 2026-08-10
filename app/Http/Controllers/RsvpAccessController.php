<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RsvpAccessController extends Controller
{
    public function show(): View
    {
        return view('rsvp.access');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['passcode' => 'required|string']);

        // Case-insensitive and whitespace-trimmed — mobile keyboards auto-capitalize the
        // first letter, and codes pasted from a text message often carry a trailing space
        // or newline, which would otherwise fail an exact match for no visible reason.
        $expected = strtoupper(trim((string) config('event.rsvp_passcode')));
        $given = strtoupper(trim((string) $request->input('passcode')));

        if (! hash_equals($expected, $given)) {
            return back()->withErrors(['passcode' => 'That access code is incorrect. Please try again.']);
        }

        $request->session()->put('rsvp_access_granted', true);

        return redirect()->intended(route('rsvp.create'));
    }
}
