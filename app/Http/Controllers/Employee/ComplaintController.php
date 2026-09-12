<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            abort(403, 'Profil pegawai tidak ditemukan.');
        }

        $complaints = Complaint::with(['attendance'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Employee/Complaint/Index', [
            'complaints' => $complaints,
        ]);
    }

    public function create(Request $request): Response
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            abort(403, 'Profil pegawai tidak ditemukan.');
        }

        // Recent attendances (last 30 days) for linking
        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->where('date', '>=', Carbon::now()->subDays(30)->toDateString())
            ->orderBy('date', 'desc')
            ->get(['id', 'date', 'check_in_at', 'check_out_at', 'status']);

        $selectedAttendanceId = $request->query('attendance_id');

        return Inertia::render('Employee/Complaint/Create', [
            'recentAttendances' => $recentAttendances,
            'selectedAttendanceId' => $selectedAttendanceId,
        ]);
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return back()->with('error', 'Profil pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:wrong_time,location_error,forgot_checkout,system_error,other',
            'attendance_id' => 'nullable|exists:attendances,id',
            'description' => 'required|string|min:10|max:2000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = 'complaint_'.$employee->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('attachments/complaints', $fileName, 'public');
            $attachmentPath = '/storage/'.$path;
        }

        Complaint::create([
            'employee_id' => $employee->id,
            'attendance_id' => $validated['attendance_id'] ?? null,
            'date' => $validated['date'],
            'type' => $validated['type'],
            'description' => $validated['description'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        // Notify Admins
        $adminUsers = User::role('admin')->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Komplain Presensi Baru',
                'message' => "{$employee->user->name} mengajukan komplain presensi tanggal {$validated['date']}.",
                'type' => 'complaint',
                'link' => '/admin/complaints',
                'is_read' => false,
            ]);
        }

        return redirect()->route('employee.complaints.index')
            ->with('success', 'Komplain absensi berhasil diajukan. Tim HR akan segera meninjaunya.');
    }
}
