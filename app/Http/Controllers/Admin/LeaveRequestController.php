<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status', 'all');
        $type = $request->input('type', 'all');
        $department = $request->input('department', 'all');
        $month = $request->input('month');

        $query = LeaveRequest::with(['employee.user', 'reviewer']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($department && $department !== 'all') {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($month) {
            $query->whereMonth('start_date', $month);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->get();
        $leaveRequests->each(function ($item) {
            if ($item->employee) {
                $item->employee_balance = $item->employee->getAnnualLeaveBalance(Carbon::parse($item->start_date)->year);
            }
        });

        $departments = Employee::select('department')->distinct()->pluck('department');

        return Inertia::render('Admin/LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
            'departments' => $departments,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'department' => $department,
                'month' => $month,
            ],
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah pernah diproses.');
        }

        if ($leaveRequest->type === 'annual_leave' && $leaveRequest->employee) {
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $remaining = $leaveRequest->employee->getAnnualLeaveRemaining($year);
            if ($leaveRequest->total_days > $remaining) {
                return back()->with('error', "Tidak dapat menyetujui: sisa kuota cuti tahunan pegawai hanya tersisa {$remaining} hari, sedangkan pengajuan ini membutuhkan {$leaveRequest->total_days} hari.");
            }
        }

        $leaveRequest->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        // Automatically update attendance records for each day within range (excluding weekends)
        $statusMap = [
            'sick' => 'sick',
            'permission' => 'permission',
            'annual_leave' => 'permission',
            'emergency_leave' => 'permission',
        ];
        $attendanceStatus = $statusMap[$leaveRequest->type] ?? 'permission';

        $current = Carbon::parse($leaveRequest->start_date);
        $end = Carbon::parse($leaveRequest->end_date);

        while ($current->lte($end)) {
            if (! $current->isWeekend()) {
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $leaveRequest->employee_id,
                        'date' => $current->toDateString(),
                    ],
                    [
                        'status' => $attendanceStatus,
                        'note' => 'Pengajuan '.ucfirst(str_replace('_', ' ', $leaveRequest->type)).' disetujui: '.$leaveRequest->reason,
                    ]
                );
            }
            $current->addDay();
        }

        // Notify employee
        if ($leaveRequest->employee?->user) {
            Notification::create([
                'user_id' => $leaveRequest->employee->user->id,
                'title' => 'Pengajuan Cuti Disetujui',
                'message' => 'Permohonan cuti/izin Anda telah disetujui oleh tim HR.',
                'type' => 'leave',
                'link' => '/employee/leave-requests',
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Pengajuan cuti/izin/sakit berhasil disetujui dan data absensi telah disinkronkan.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah pernah diproses.');
        }

        $validated = $request->validate([
            'reject_reason' => 'required|string|min:5|max:1000',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'reject_reason' => $validated['reject_reason'],
        ]);

        // Notify employee
        if ($leaveRequest->employee?->user) {
            Notification::create([
                'user_id' => $leaveRequest->employee->user->id,
                'title' => 'Pengajuan Cuti Ditolak',
                'message' => 'Permohonan cuti/izin Anda ditolak: '.$validated['reject_reason'],
                'type' => 'leave',
                'link' => '/employee/leave-requests',
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Pengajuan cuti/izin/sakit telah ditolak dengan catatan.');
    }
}
