<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendeeStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendee;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total' => Attendee::count(),
            'confirmed' => Attendee::where('status', AttendeeStatus::Confirmed)->count(),
            'declined' => Attendee::where('status', AttendeeStatus::Declined)->count(),
            'pending' => Attendee::where('status', AttendeeStatus::Pending)->count(),
            'checked_in' => Attendee::whereNotNull('checked_in_at')->count(),
        ];

        $recent = Attendee::latest('updated_at')->limit(8)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }
}
