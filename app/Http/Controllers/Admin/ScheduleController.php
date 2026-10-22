<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleEvent;
use App\Models\ScheduleProtocol;
use App\Models\ScheduleSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin overview of the Protocols & Calendar page: page settings, the protocols list
 * (edited inline) and the calendar events list (each edited on its own page — see
 * ScheduleEventController).
 */
class ScheduleController extends Controller
{
    public function index(): View
    {
        return view('admin.schedule.index', [
            'settings' => ScheduleSetting::current(),
            'protocols' => ScheduleProtocol::ordered()->get(),
            'events' => ScheduleEvent::ordered()->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme' => ['nullable', 'string', 'max:160'],
            'source_year' => ['nullable', 'digits:4'],
            'is_confirmed' => ['boolean'],
        ]);

        ScheduleSetting::current()->update($data + ['is_confirmed' => false]);

        return back()->with('success', 'Page settings saved.');
    }

    public function storeProtocol(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'time_label' => ['required', 'string', 'max:60'],
            'description' => ['required', 'string', 'max:1000'],
        ], [], ['time_label' => 'time']);

        ScheduleProtocol::create($data + [
            'position' => (int) ScheduleProtocol::max('position') + 1,
            'is_published' => true,
        ]);

        return back()->with('success', 'Protocol added.');
    }

    /** Saves every protocol's time, description, publish state and order in one go. */
    public function updateProtocols(Request $request): RedirectResponse
    {
        $ids = ScheduleProtocol::pluck('id')->all();

        $data = $request->validate([
            'order' => ['array'],
            'order.*' => ['integer', 'in:'.implode(',', $ids ?: [0])],
            'protocols' => ['array'],
            'protocols.*.time_label' => ['required', 'string', 'max:60'],
            'protocols.*.description' => ['required', 'string', 'max:1000'],
            'protocols.*.is_published' => ['boolean'],
        ], [], ['protocols.*.time_label' => 'time', 'protocols.*.description' => 'description']);

        $positions = array_flip(array_values($data['order'] ?? []));

        DB::transaction(function () use ($data, $positions) {
            foreach (ScheduleProtocol::all() as $protocol) {
                if ($row = $data['protocols'][$protocol->id] ?? null) {
                    $protocol->fill([
                        'time_label' => $row['time_label'],
                        'description' => $row['description'],
                        'is_published' => (bool) ($row['is_published'] ?? false),
                    ]);
                }
                if (isset($positions[$protocol->id])) {
                    $protocol->position = $positions[$protocol->id] + 1;
                }
                $protocol->save();
            }
        });

        return back()->with('success', 'Protocols saved.');
    }

    public function destroyProtocol(ScheduleProtocol $protocol): RedirectResponse
    {
        $protocol->delete();

        return back()->with('success', 'Protocol deleted.');
    }

    /** Saves the calendar's manual order and publish states. */
    public function updateEvents(Request $request): RedirectResponse
    {
        $ids = ScheduleEvent::pluck('id')->all();

        $data = $request->validate([
            'order' => ['array'],
            'order.*' => ['integer', 'in:'.implode(',', $ids ?: [0])],
            'published' => ['array'],
            'published.*' => ['boolean'],
        ]);

        $positions = array_flip(array_values($data['order'] ?? []));

        DB::transaction(function () use ($data, $positions) {
            foreach (ScheduleEvent::all() as $event) {
                if (array_key_exists($event->id, $data['published'] ?? [])) {
                    $event->is_published = (bool) $data['published'][$event->id];
                }
                if (isset($positions[$event->id])) {
                    $event->position = $positions[$event->id] + 1;
                }
                $event->save();
            }
        });

        return back()->with('success', 'Calendar order and publishing saved.');
    }

    /** Orders the calendar by date, then start time; undated events go last. */
    public function sortEvents(): RedirectResponse
    {
        $sorted = ScheduleEvent::ordered()->get()->sortBy([
            fn ($a, $b) => ($a->event_date === null) <=> ($b->event_date === null),
            fn ($a, $b) => $a->event_date?->timestamp <=> $b->event_date?->timestamp,
            fn ($a, $b) => ($a->start_time ?? '99:99') <=> ($b->start_time ?? '99:99'),
        ])->values();

        DB::transaction(fn () => $sorted->each(fn ($event, $i) => $event->update(['position' => $i + 1])));

        return back()->with('success', 'Calendar sorted by date and time.');
    }
}
