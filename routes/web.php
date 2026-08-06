<?php

use App\Http\Controllers\Admin\AttendeeController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CheckInController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\RsvpController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/rsvp', [RsvpController::class, 'create'])->name('rsvp.create');
Route::post('/rsvp', [RsvpController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('rsvp.store');
Route::get('/rsvp/thank-you', [RsvpController::class, 'thankYou'])->name('rsvp.thank-you');

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
        Route::patch('attendees/{attendee}/uncheck-in', [AttendeeController::class, 'uncheckIn'])->name('attendees.uncheck-in');
        Route::get('attendees-export', [AttendeeController::class, 'export'])->name('attendees.export');

        Route::get('checkin/{attendee:invite_token}', [CheckInController::class, 'show'])->name('checkin.show');
    });
});
