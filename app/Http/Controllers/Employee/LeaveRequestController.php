<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            abort(403, 'Profil pegawai tidak ditemukan.');
        }

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Employee/LeaveRequest/Index', [
            'leaveRequests' => $leaveRequests,
            'leaveBalance' => $employee->getAnnualLeaveBalance(),
        ]);
    }

    public function create(Request $request): Response
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            abort(403, 'Profil pegawai tidak ditemukan.');
        }

        return Inertia::render('Employee/LeaveRequest/Create', [
            'leaveBalance' => $employee->getAnnualLeaveBalance(),
        ]);
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return back()->with('error', 'Profil pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'type' => 'required|in:sick,permission,annual_leave,emergency_leave',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:5|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = AttendanceService::calculateWorkingDays($startDate, $endDate);

        if ($validated['type'] === 'annual_leave') {
            $year = $startDate->year;
            $balance = $employee->getAnnualLeaveBalance($year);

            if ($totalDays > $balance['available']) {
                return back()->withErrors([
                    'type' => "Sisa kuota cuti tahunan Anda tidak mencukupi ({$balance['available']} hari tersisa). Pengajuan membutuhkan {$totalDays} hari kerja.",
                ])->withInput();
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = 'leave_'.$employee->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('attachments/leaves', $fileName, 'public');
            $attachmentPath = '/storage/'.$path;
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        // Notify Admins
        $adminUsers = User::role('admin')->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Pengajuan Cuti Baru',
                'message' => "{$employee->user->name} mengajukan ".ucfirst(str_replace('_', ' ', $validated['type'])),
                'type' => 'leave',
                'link' => '/admin/leave-requests',
                'is_read' => false,
            ]);
        }

        return redirect()->route('employee.leave-requests.index')
            ->with('success', 'Pengajuan cuti/izin/sakit berhasil dikirimkan dan menunggu peninjauan HR.');
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest)
    {
        $employee = $request->user()->employee;
        if (! $employee || $leaveRequest->employee_id !== $employee->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        }

        $leaveRequest->delete();

        return back()->with('success', 'Pengajuan berhasil dibatalkan.');
    }
}
