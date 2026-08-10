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

        if (! hash_equals((string) config('event.rsvp_passcode'), $request->input('passcode'))) {
            return back()->withErrors(['passcode' => 'That access code is incorrect. Please try again.']);
        }

        $request->session()->put('rsvp_access_granted', true);

        return redirect()->intended(route('rsvp.create'));
    }
}
