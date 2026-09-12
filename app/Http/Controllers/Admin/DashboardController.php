<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $selectedDepartment = $request->input('department', 'all');

        $totalEmployees = Employee::count();
        $departments = Employee::select('department')->distinct()->pluck('department');

        $attendanceQuery = Attendance::with(['employee.user'])
            ->where('date', $selectedDate);

        if ($selectedDepartment !== 'all' && !empty($selectedDepartment)) {
            $attendanceQuery->whereHas('employee', function ($q) use ($selectedDepartment) {
                $q->where('department', $selectedDepartment);
            });
        }

        $attendances = $attendanceQuery->get();

        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $wfhCount = $attendances->where('status', 'wfh')->count();
        $leaveCount = $attendances->whereIn('status', ['sick', 'permission'])->count();
        $recordedCount = $attendances->count();
        $notCheckedInCount = max(0, $totalEmployees - $recordedCount);

        // Pending items for review
        $pendingLeaveRequests = LeaveRequest::with(['employee.user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $pendingComplaints = Complaint::with(['employee.user', 'attendance'])
            ->whereIn('status', ['pending', 'in_review'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalEmployees' => $totalEmployees,
                'present' => $presentCount,
                'late' => $lateCount,
                'wfh' => $wfhCount,
                'leave' => $leaveCount,
                'notCheckedIn' => $notCheckedInCount,
            ],
            'filters' => [
                'date' => $selectedDate,
                'department' => $selectedDepartment,
            ],
            'departments' => $departments,
            'attendances' => $attendances,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'pendingComplaints' => $pendingComplaints,
        ]);
    }
}
