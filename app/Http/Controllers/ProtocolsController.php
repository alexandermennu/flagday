<?php

namespace App\Http\Controllers;

use App\Models\ScheduleEvent;
use App\Models\ScheduleProtocol;
use App\Models\ScheduleSetting;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

/** Public Protocols & Calendar page — published entries from admin → Protocols & Calendar. */
class ProtocolsController extends Controller
{
    public function show(): View
    {
        try {
            $settings = ScheduleSetting::current();
            $protocols = ScheduleProtocol::ordered()->where('is_published', true)->get();
            $events = ScheduleEvent::ordered()->where('is_published', true)->get();
        } catch (QueryException) {
            // Tables not migrated yet: show the page shell rather than an error.
            [$settings, $protocols, $events] = [new ScheduleSetting(['is_confirmed' => false]), collect(), collect()];
        }

        return view('protocols', compact('settings', 'protocols', 'events'));
    }
}
