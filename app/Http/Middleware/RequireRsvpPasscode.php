<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the public RSVP form behind a shared passcode (see RsvpAccessController) — the
 * form itself has no login, so this is a light barrier against randoms who click the
 * "RSVP" nav link rather than a real authentication layer.
 */
class RequireRsvpPasscode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('rsvp_access_granted') === true) {
            return $next($request);
        }

        if ($request->isMethod('get')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('rsvp.access');
    }
}
