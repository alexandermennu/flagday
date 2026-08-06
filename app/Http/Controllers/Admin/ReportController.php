<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendeeStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\AttendeeGuest;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $total = Attendee::count();
        $confirmed = Attendee::where('status', AttendeeStatus::Confirmed)->count();
        $declined = Attendee::where('status', AttendeeStatus::Declined)->count();
        $pending = Attendee::where('status', AttendeeStatus::Pending)->count();
        $checkedIn = Attendee::whereNotNull('checked_in_at')->count();
        $responded = $confirmed + $declined;
        $totalGuests = AttendeeGuest::count();

        $byOrganization = Attendee::selectRaw(
            'organization, count(*) as total, sum(case when status = ? then 1 else 0 end) as confirmed',
            [AttendeeStatus::Confirmed->value]
        )
            ->groupBy('organization')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('admin.reports.index', [
            'stats' => [
                'total' => $total,
                'confirmed' => $confirmed,
                'declined' => $declined,
                'pending' => $pending,
                'checked_in' => $checkedIn,
                'response_rate' => $total > 0 ? round($responded / $total * 100) : 0,
                'total_guests' => $totalGuests,
                'expected_attendance' => $confirmed + $totalGuests,
            ],
            'byOrganization' => $byOrganization,
        ]);
    }
}
