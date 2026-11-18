<?php

namespace App\Http\Middleware;

use App\Models\RsvpSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops the public RSVP flow while the RSVP is closed (admin → Dashboard): the form and
 * passcode pages show a "closed" notice, and a submission — even from a form opened
 * before the RSVP closed — is refused without saving anything or sending email.
 */
class EnsureRsvpOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (RsvpSetting::isOpen()) {
            return $next($request);
        }

        if ($request->isMethod('get')) {
            return response()->view('rsvp.closed');
        }

        return redirect()->route('rsvp.create');
    }
}
