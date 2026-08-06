<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendeeRequest;
use App\Http\Requests\UpdateAttendeeRequest;
use App\Mail\AttendeeReminder;
use App\Models\Attendee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Attendee::query();

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $attendees = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('admin.attendees.index', [
            'attendees' => $attendees,
            'filters' => [
                'q' => $request->query('q', ''),
                'status' => $request->query('status', ''),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.attendees.create');
    }

    public function store(StoreAttendeeRequest $request): RedirectResponse
    {
        $attendee = Attendee::create($request->validated());

        $this->handleConfirmationTransition($attendee, previousStatus: AttendeeStatus::Pending);

        return redirect()->route('admin.attendees.index')->with('success', "Added {$attendee->full_name}.");
    }

    public function edit(Attendee $attendee): View
    {
        return view('admin.attendees.edit', ['attendee' => $attendee]);
    }

    public function update(UpdateAttendeeRequest $request, Attendee $attendee): RedirectResponse
    {
        $previousStatus = $attendee->status;

        $attendee->fill($request->validated());
        $attendee->save();

        $this->handleConfirmationTransition($attendee, $previousStatus);

        return redirect()->route('admin.attendees.index')->with('success', "Updated {$attendee->full_name}.");
    }

    public function destroy(Attendee $attendee): RedirectResponse
    {
        $attendee->delete();

        return redirect()->route('admin.attendees.index')->with('success', 'Attendee removed.');
    }

    public function remind(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'attendee_ids' => ['required', 'array'],
            'attendee_ids.*' => ['integer', 'exists:attendees,id'],
        ]);

        $selected = Attendee::whereIn('id', $validated['attendee_ids'])->get();
        $eligible = $selected->where('status', AttendeeStatus::Confirmed);
        $skipped = $selected->count() - $eligible->count();

        foreach ($eligible as $attendee) {
            Mail::to($attendee->email)->queue(new AttendeeReminder($attendee));
            $attendee->forceFill(['reminder_sent_at' => now()])->save();
        }

        $message = "Sent reminder to {$eligible->count()} attendee(s).";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} not yet confirmed.";
        }

        return redirect()->route('admin.attendees.index')->with('success', $message);
    }

    public function export(): StreamedResponse
    {
        $filename = 'attendees-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Name', 'Email', 'Phone', 'Organization', 'Status', 'RSVP Date', 'Checked In At']);

            Attendee::orderBy('full_name')->chunk(200, function ($attendees) use ($handle) {
                foreach ($attendees as $attendee) {
                    fputcsv($handle, [
                        $this->csvSafe($attendee->full_name),
                        $this->csvSafe($attendee->email),
                        $this->csvSafe($attendee->phone ?? ''),
                        $this->csvSafe($attendee->organization ?? ''),
                        $attendee->status->label(),
                        optional($attendee->confirmed_at)->format('Y-m-d H:i'),
                        optional($attendee->checked_in_at)->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function uncheckIn(Attendee $attendee): RedirectResponse
    {
        $attendee->forceFill(['checked_in_at' => null])->save();

        return back()->with('success', "Cleared check-in for {$attendee->full_name}.");
    }

    /**
     * Sends the confirmation email (with digital invite) exactly once, at the moment an
     * attendee's status transitions into Confirmed — not on every unrelated edit.
     */
    private function handleConfirmationTransition(Attendee $attendee, AttendeeStatus $previousStatus): void
    {
        if ($attendee->status !== AttendeeStatus::Confirmed || $previousStatus === AttendeeStatus::Confirmed) {
            return;
        }

        $attendee->forceFill([
            'confirmed_at' => now(),
            'reminder_sent_at' => null,
        ])->save();

        $attendee->sendConfirmationEmail();
    }

    private function csvSafe(string $value): string
    {
        if (preg_match('/^[=+\-@]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
