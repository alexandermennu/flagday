<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\View\View;

/**
 * Public, unauthenticated confirmation-of-attendance page — the QR code on the PDF
 * Event Pass points here. Deliberately read-only: it does not mark check-in (that
 * remains an admin-only action, see Admin\AttendeeController::checkIn()), it only
 * confirms the pass is genuine and shows the guest's details.
 */
class PassVerificationController extends Controller
{
    public function show(Attendee $attendee): View
    {
        return view('pass.verify', ['attendee' => $attendee]);
    }
}
