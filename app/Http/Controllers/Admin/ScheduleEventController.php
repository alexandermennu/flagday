<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleEventRequest;
use App\Models\ScheduleEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Create, edit and delete individual Calendar of Events entries. */
class ScheduleEventController extends Controller
{
    public function create(): View
    {
        return view('admin.schedule.event', ['event' => new ScheduleEvent(['is_published' => true, 'notes' => []])]);
    }

    public function store(ScheduleEventRequest $request): RedirectResponse
    {
        ScheduleEvent::create($request->eventData() + ['position' => (int) ScheduleEvent::max('position') + 1]);

        return redirect()->route('admin.schedule.index')->with('success', 'Calendar event added.');
    }

    public function edit(ScheduleEvent $event): View
    {
        return view('admin.schedule.event', ['event' => $event]);
    }

    public function update(ScheduleEventRequest $request, ScheduleEvent $event): RedirectResponse
    {
        $event->update($request->eventData());

        return redirect()->route('admin.schedule.index')->with('success', "Saved “{$event->title}”.");
    }

    public function destroy(ScheduleEvent $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.schedule.index')->with('success', "Deleted “{$event->title}”.");
    }
}
