<?php

use App\Http\Controllers\Admin\AttendeeController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CheckInController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\FlagDayController;
use App\Http\Controllers\PassVerificationController;
use App\Http\Controllers\RsvpAccessController;
use App\Http\Controllers\RsvpController;
use App\Http\Middleware\RequireRsvpPasscode;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/flag-day', [FlagDayController::class, 'show'])->name('flag-day.show');

// The RSVP form sits behind a shared passcode (see RequireRsvpPasscode) — anyone can
// click "RSVP" in the nav, but must enter the code before the form itself loads.
Route::get('/rsvp/access', [RsvpAccessController::class, 'show'])->name('rsvp.access');
Route::post('/rsvp/access', [RsvpAccessController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('rsvp.access.store');

Route::middleware(RequireRsvpPasscode::class)->group(function () {
    Route::get('/rsvp', [RsvpController::class, 'create'])->name('rsvp.create');
    Route::post('/rsvp', [RsvpController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('rsvp.store');
});
Route::get('/rsvp/thank-you', [RsvpController::class, 'thankYou'])->name('rsvp.thank-you');

// Public, unauthenticated — the QR code on the PDF Event Pass points here.
Route::get('/confirm/{attendee:invite_token}', [PassVerificationController::class, 'show'])->name('pass.verify');

// Redirects tickets issued before the confirmation URL moved from /pass to /confirm.
Route::get('/pass/{token}', fn (string $token) => redirect()->route('pass.verify', $token, 301));

// Public — linked from the confirmation/reminder emails instead of an .ics attachment,
// since attached .ics files trigger Gmail's large inline event card.
Route::get('/calendar/{attendee:invite_token}', [CalendarController::class, 'show'])->name('calendar.show');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('attendees', AttendeeController::class)->except('show');
        Route::post('attendees/remind', [AttendeeController::class, 'remind'])->name('attendees.remind');
        Route::patch('attendees/{attendee}/check-in', [AttendeeController::class, 'checkIn'])->name('attendees.check-in');
        Route::patch('attendees/{attendee}/uncheck-in', [AttendeeController::class, 'uncheckIn'])->name('attendees.uncheck-in');
        Route::get('attendees-export', [AttendeeController::class, 'export'])->name('attendees.export');

        Route::get('checkin/{attendee:invite_token}', [CheckInController::class, 'show'])->name('checkin.show');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });
});
