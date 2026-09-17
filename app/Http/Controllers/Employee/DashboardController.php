<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $employee = $user->employee;

        $today = Carbon::today()->toDateString();

        // Active announcements with author
        $announcements = Announcement::where('is_active', true)
            ->with('author')
            ->orderBy('created_at', 'desc')
            ->get();

        // National & corporate events / holidays
        $upcomingHolidays = Holiday::orderBy('date', 'asc')->get();

        // Today's attendance status for quick widget
        $todayAttendance = null;
        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();
        }

        return Inertia::render('Employee/Dashboard', [
            'employee' => $employee ? $employee->load('user') : null,
            'announcements' => $announcements,
            'upcomingHolidays' => $upcomingHolidays,
            'todayAttendance' => $todayAttendance,
        ]);
    }
}
