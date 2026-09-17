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
        $tetapCount = Employee::where('employment_status', 'tetap')->count();
        $vendorCount = Employee::where('employment_status', 'vendor')->count();
        $magangCount = Employee::where('employment_status', 'magang')->count();

        $departments = Employee::select('department')->distinct()->pluck('department');

        // Attendance stats for selected date
        $attendanceBaseQuery = Attendance::where('date', $selectedDate);

        if ($selectedDepartment !== 'all' && ! empty($selectedDepartment)) {
            $attendanceBaseQuery->whereHas('employee', function ($q) use ($selectedDepartment) {
                $q->where('department', $selectedDepartment);
            });
        }

        $statusCounts = (clone $attendanceBaseQuery)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $presentCount = $statusCounts['present'] ?? 0;
        $lateCount = $statusCounts['late'] ?? 0;
        $wfhCount = $statusCounts['wfh'] ?? 0;
        $leaveCount = ($statusCounts['sick'] ?? 0) + ($statusCounts['permission'] ?? 0);
        $recordedCount = array_sum($statusCounts);
        $notCheckedInCount = max(0, $totalEmployees - $recordedCount);

        // Recent attendances (limit 15 for fast page load)
        $recentAttendances = (clone $attendanceBaseQuery)
            ->with(['employee.user'])
            ->orderByDesc('id')
            ->take(15)
            ->get();

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

        // Transjakarta Regional Distribution Data
        $regionalEmployees = Employee::selectRaw('region, employment_status, position, count(*) as count')
            ->groupBy('region', 'employment_status', 'position')
            ->get();

        $regionalAttendance = Attendance::where('date', $selectedDate)
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->selectRaw('employees.region, attendances.status, count(*) as count')
            ->groupBy('employees.region', 'attendances.status')
            ->get();

        $regionKeys = ['jakarta_timur', 'jakarta_barat', 'jakarta_pusat', 'jakarta_utara', 'jakarta_selatan'];
        $regionsData = [];

        foreach ($regionKeys as $rKey) {
            $rEmp = $regionalEmployees->where('region', $rKey);
            $rAtt = $regionalAttendance->where('region', $rKey);

            $tetap = (int) $rEmp->where('employment_status', 'tetap')->sum('count');
            $vendor = (int) $rEmp->where('employment_status', 'vendor')->sum('count');
            $magang = (int) $rEmp->where('employment_status', 'magang')->sum('count');
            $regTotal = $tetap + $vendor + $magang;

            $posPramudi = (int) $rEmp->where('position', 'Pramudi')->sum('count');
            $posPramusapa = (int) $rEmp->where('position', 'Pramusapa')->sum('count');
            $posPramujaga = (int) $rEmp->where('position', 'Pramujaga')->sum('count');
            $posKantor = (int) $rEmp->whereIn('position', ['Karyawan Kantor', 'HR Manager & General Affairs'])->sum('count');

            $attPresent = (int) $rAtt->where('status', 'present')->sum('count');
            $attLate = (int) $rAtt->where('status', 'late')->sum('count');
            $attSick = (int) $rAtt->where('status', 'sick')->sum('count');
            $attPermission = (int) $rAtt->where('status', 'permission')->sum('count');
            $attRecorded = $attPresent + $attLate + $attSick + $attPermission;
            $attNotCheckedIn = max(0, $regTotal - $attRecorded);

            $regionsData[$rKey] = [
                'total' => $regTotal,
                'tetap' => $tetap,
                'vendor' => $vendor,
                'magang' => $magang,
                'positions' => [
                    'Pramudi' => $posPramudi,
                    'Pramusapa' => $posPramusapa,
                    'Pramujaga' => $posPramujaga,
                    'Karyawan Kantor' => $posKantor,
                ],
                'attendance' => [
                    'present' => $attPresent,
                    'late' => $attLate,
                    'sick' => $attSick,
                    'permission' => $attPermission,
                    'not_checked_in' => $attNotCheckedIn,
                ],
            ];
        }

        $transjakartaStats = [
            'totalEmployees' => $totalEmployees,
            'tetapCount' => $tetapCount,
            'vendorCount' => $vendorCount,
            'magangCount' => $magangCount,
            'regions' => $regionsData,
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalEmployees' => $totalEmployees,
                'present' => $presentCount,
                'late' => $lateCount,
                'wfh' => $wfhCount,
                'leave' => $leaveCount,
                'notCheckedIn' => $notCheckedInCount,
            ],
            'transjakartaStats' => $transjakartaStats,
            'filters' => [
                'date' => $selectedDate,
                'department' => $selectedDepartment,
            ],
            'departments' => $departments,
            'attendances' => $recentAttendances,
            'totalAttendancesToday' => $recordedCount,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'pendingComplaints' => $pendingComplaints,
        ]);
    }
}
