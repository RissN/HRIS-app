<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\Payroll;
use App\Models\Setting;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    use LogsActivity;

    public function index(Request $request): Response
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $department = $request->input('department', 'all');

        $baseMonthQuery = Payroll::where('month', $month);

        $stats = [
            'total_expenditure' => (clone $baseMonthQuery)->sum('net_salary'),
            'total_employees' => (clone $baseMonthQuery)->count(),
            'paid_count' => (clone $baseMonthQuery)->where('status', 'paid')->count(),
            'draft_count' => (clone $baseMonthQuery)->where('status', 'draft')->count(),
        ];

        $query = Payroll::with(['employee.user'])
            ->where('month', $month);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($department && $department !== 'all') {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($search) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%");
                        });
                });
            });
        }

        $payrolls = $query->orderBy('net_salary', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Unique months available in payrolls
        $availableMonths = Payroll::select('month')->distinct()->orderBy('month', 'desc')->pluck('month');
        if (! $availableMonths->contains($month)) {
            $availableMonths->prepend($month);
        }

        $departments = Employee::select('department')->distinct()->whereNotNull('department')->pluck('department');

        return Inertia::render('Admin/Payroll/Index', [
            'payrolls' => $payrolls,
            'stats' => $stats,
            'month' => $month,
            'availableMonths' => $availableMonths,
            'departments' => $departments,
            'filters' => [
                'month' => $month,
                'search' => $search,
                'status' => $status,
                'department' => $department,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $dailyAllowanceRate = (float) Setting::get('rate_daily_allowance', 50000);
        $lateDeductionRate = (float) Setting::get('rate_late_deduction', 25000);
        $absentDeductionRate = (float) Setting::get('rate_absent_deduction', 100000);

        $employees = Employee::with('user')->whereHas('user', function ($q) {
            $q->where('is_active', true);
        })->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada pegawai aktif untuk dihitung gajinya.');
        }

        $generatedCount = 0;

        foreach ($employees as $employee) {
            $existing = Payroll::where('employee_id', $employee->id)->where('month', $month)->first();

            // Do not overwrite already paid payroll
            if ($existing && $existing->status === 'paid') {
                continue;
            }

            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $presentCount = $attendances->whereIn('status', ['present', 'wfh'])->count();
            $lateCount = $attendances->where('status', 'late')->count();
            $absentCount = $attendances->where('status', 'absent')->count();

            $basicSalary = (float) ($employee->basic_salary ?: 6000000);
            $dailyAllowance = ($presentCount + $lateCount) * $dailyAllowanceRate;
            $lateDeduction = $lateCount * $lateDeductionRate;
            $absentDeduction = $absentCount * $absentDeductionRate;

            $netSalary = max(0, $basicSalary + $dailyAllowance - $lateDeduction - $absentDeduction);

            Payroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'month' => $month,
                ],
                [
                    'basic_salary' => $basicSalary,
                    'daily_allowance' => $dailyAllowance,
                    'late_deduction' => $lateDeduction,
                    'absent_deduction' => $absentDeduction,
                    'net_salary' => $netSalary,
                    'status' => 'draft',
                    'note' => "Hadir/WFH: {$presentCount} hari, Terlambat: {$lateCount} hari, Alpa: {$absentCount} hari.",
                ]
            );

            $generatedCount++;
        }

        $this->logActivity('generated', "Generate payroll {$generatedCount} pegawai untuk {$month}");

        return redirect()->back()->with('success', "Berhasil mengkalkulasi estimasi gaji {$generatedCount} pegawai untuk periode {$month}.");
    }

    public function markAsPaid(Payroll $payroll): RedirectResponse
    {
        $payroll->update([
            'status' => 'paid',
            'payment_date' => Carbon::today()->toDateString(),
        ]);

        // Send interactive notification to employee
        if ($payroll->employee?->user) {
            Notification::create([
                'user_id' => $payroll->employee->user->id,
                'title' => 'Slip Gaji Diterbitkan',
                'message' => "Slip gaji Anda untuk periode {$payroll->month} telah disetujui dan dibayarkan.",
                'type' => 'payroll',
                'link' => '/employee/payroll',
                'is_read' => false,
            ]);
        }

        $this->logActivity('paid', "Menandai gaji LUNAS: {$payroll->employee?->user?->name} ({$payroll->month})", $payroll);

        return redirect()->back()->with('success', "Gaji {$payroll->employee?->user?->name} periode {$payroll->month} berhasil ditandai LUNAS.");
    }
}
