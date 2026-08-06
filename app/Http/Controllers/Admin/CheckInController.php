<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function show(Attendee $attendee): View
    {
        $alreadyCheckedIn = $attendee->checked_in_at !== null;

        if (! $alreadyCheckedIn) {
            $attendee->forceFill(['checked_in_at' => now()])->save();
        }

        return view('admin.checkin.show', [
            'attendee' => $attendee,
            'alreadyCheckedIn' => $alreadyCheckedIn,
        ]);
    }
}
