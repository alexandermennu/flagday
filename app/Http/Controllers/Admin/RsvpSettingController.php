<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RsvpSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Opens or closes the public Honors Ball RSVP (the toggle on the admin dashboard). */
class RsvpSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(['is_open' => ['required', 'boolean']]);

        RsvpSetting::current()->update(['is_open' => (bool) $validated['is_open']]);

        return back()->with('success', $validated['is_open']
            ? 'RSVP is open. The public form is accepting responses again.'
            : 'RSVP is closed. The public form no longer accepts responses.');
    }
}
