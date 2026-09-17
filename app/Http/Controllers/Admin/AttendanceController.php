<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $department = $request->input('department', 'all');
        $search = $request->input('search');

        $query = Attendance::with(['employee.user']);

        if ($date) {
            $query->where('date', $date);
        }

        if ($department && $department !== 'all') {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('employee_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%");
                        });
                });
            });
        }

        $attendances = $query->orderBy('check_in_at', 'asc')->paginate(20)->withQueryString();

        $departments = Employee::select('department')->distinct()->pluck('department');

        return Inertia::render('Admin/Attendance/Index', [
            'attendances' => $attendances,
            'departments' => $departments,
            'filters' => [
                'date' => $date,
                'department' => $department,
                'search' => $search,
            ],
        ]);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'check_in_at' => 'nullable|date',
            'check_out_at' => 'nullable|date',
            'status' => 'required|in:present,late,absent,sick,permission,wfh',
            'note' => 'nullable|string|max:500',
        ]);

        $attendance->update($validated);

        return back()->with('success', 'Data absensi berhasil diperbarui!');
    }
}
