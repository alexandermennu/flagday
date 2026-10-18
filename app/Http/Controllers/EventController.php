<?php

namespace App\Http\Controllers;

use App\Support\Graduation;
use Illuminate\View\View;

/**
 * Public detail page for each graduation event (Honors Ball, Baccalaureate,
 * Convocation). Content comes from config/graduation.php.
 */
class EventController extends Controller
{
    public function show(string $slug): View
    {
        $event = Graduation::find($slug);

        abort_if($event === null, 404);

        return view('events.show', [
            'event' => $event,
            'otherEvents' => Graduation::events()->except($slug),
        ]);
    }
}
