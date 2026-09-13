<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status', 'all');
        $type = $request->input('type', 'all');
        $department = $request->input('department', 'all');
        $date = $request->input('date');

        $query = Complaint::with(['employee.user', 'attendance', 'resolver']);

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

        if ($date) {
            $query->where('date', $date);
        }

        $complaints = $query->orderBy('created_at', 'desc')->get();
        $departments = Employee::select('department')->distinct()->pluck('department');

        return Inertia::render('Admin/Complaints/Index', [
            'complaints' => $complaints,
            'departments' => $departments,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'department' => $department,
                'date' => $date,
            ],
        ]);
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        if ($complaint->status !== 'pending') {
            return back()->with('error', 'Komplain ini sudah ditinjau atau telah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'status' => 'required|in:in_review',
        ]);

        $complaint->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status komplain diubah menjadi Sedang Ditinjau.');
    }

    public function resolve(Request $request, Complaint $complaint)
    {
        if (in_array($complaint->status, ['resolved', 'rejected'])) {
            return back()->with('error', 'Komplain ini sudah selesai diproses dan tidak dapat diubah kembali.');
        }

        $validated = $request->validate([
            'admin_note' => 'required|string|min:5|max:1000',
            // Optional direct correction of attendance
            'correct_attendance' => 'nullable|boolean',
            'check_in_at' => 'nullable|date',
            'check_out_at' => 'nullable|date',
            'status' => 'nullable|in:present,late,wfh,permission,sick,absent',
        ]);

        $complaint->update([
            'status' => 'resolved',
            'admin_note' => $validated['admin_note'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        // If admin chose to correct attendance
        if (! empty($validated['correct_attendance'])) {
            $attendance = $complaint->attendance;
            if (! $attendance) {
                $attendance = Attendance::firstOrCreate(
                    [
                        'employee_id' => $complaint->employee_id,
                        'date' => $complaint->date,
                    ],
                    [
                        'status' => $validated['status'] ?? 'present',
                        'note' => 'Koreksi dari komplain #'.$complaint->id,
                    ]
                );
            }

            $updateData = [
                'note' => ($attendance->note ? $attendance->note.' | ' : '').'Koreksi: '.$validated['admin_note'],
            ];

            if (! empty($validated['check_in_at'])) {
                $updateData['check_in_at'] = $validated['check_in_at'];
            }
            if (! empty($validated['check_out_at'])) {
                $updateData['check_out_at'] = $validated['check_out_at'];
            }
            if (! empty($validated['status'])) {
                $updateData['status'] = $validated['status'];
            }

            $attendance->update($updateData);
        }

        // Notify employee
        if ($complaint->employee?->user) {
            Notification::create([
                'user_id' => $complaint->employee->user->id,
                'title' => 'Komplain Presensi Diselesaikan',
                'message' => 'Laporan koreksi presensi Anda telah diselesaikan: '.$validated['admin_note'],
                'type' => 'complaint',
                'link' => '/employee/complaints',
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Komplain absensi telah diselesaikan dan direspon.');
    }

    public function reject(Request $request, Complaint $complaint)
    {
        if (in_array($complaint->status, ['resolved', 'rejected'])) {
            return back()->with('error', 'Komplain ini sudah selesai diproses dan tidak dapat diubah kembali.');
        }

        $validated = $request->validate([
            'admin_note' => 'required|string|min:5|max:1000',
        ]);

        $complaint->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        // Notify employee
        if ($complaint->employee?->user) {
            Notification::create([
                'user_id' => $complaint->employee->user->id,
                'title' => 'Komplain Presensi Ditolak',
                'message' => 'Laporan koreksi presensi Anda ditolak: '.$validated['admin_note'],
                'type' => 'complaint',
                'link' => '/employee/complaints',
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Komplain absensi ditolak dengan alasan.');
    }
}
