<?php

namespace App\Http\Controllers;

use App\Enums\AttendeeStatus;
use App\Http\Requests\StoreRsvpRequest;
use App\Mail\RsvpDeclinedAcknowledgement;
use App\Models\Attendee;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class RsvpController extends Controller
{
    public function create(): View
    {
        return view('rsvp.create');
    }

    public function store(StoreRsvpRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Resubmission by the same email updates the existing response rather than
        // erroring. The try/catch below only guards the rare race where two requests
        // for a brand-new email both pass the lookup before either commits.
        $attendee = Attendee::where('email', $data['email'])->first();
        $wasConfirmed = $attendee?->status === AttendeeStatus::Confirmed;

        if (! $attendee) {
            try {
                $attendee = Attendee::create($data);
            } catch (UniqueConstraintViolationException) {
                $attendee = Attendee::where('email', $data['email'])->firstOrFail();
                $wasConfirmed = $attendee->status === AttendeeStatus::Confirmed;
            }
        }

        $attendee->fill($data);
        $attendee->confirmed_at = now();

        if ($attendee->status === AttendeeStatus::Confirmed && ! $wasConfirmed) {
            $attendee->reminder_sent_at = null;
        }

        $attendee->save();

        if ($attendee->status === AttendeeStatus::Confirmed) {
            $attendee->sendConfirmationEmail();
        } else {
            Mail::to($attendee->email)->queue(new RsvpDeclinedAcknowledgement($attendee));
        }

        return redirect()->route('rsvp.thank-you')->with('rsvp_status', $attendee->status->value);
    }

    public function thankYou(): View
    {
        return view('rsvp.thank-you', [
            'status' => session('rsvp_status', AttendeeStatus::Confirmed->value),
        ]);
    }
}
