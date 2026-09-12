<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\Payroll;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $payrolls = Payroll::with(['employee.user'])
            ->where('month', $month)
            ->orderBy('net_salary', 'desc')
            ->get();

        $stats = [
            'total_expenditure' => $payrolls->sum('net_salary'),
            'total_employees' => $payrolls->count(),
            'paid_count' => $payrolls->where('status', 'paid')->count(),
            'draft_count' => $payrolls->where('status', 'draft')->count(),
        ];

        // Unique months available in payrolls
        $availableMonths = Payroll::select('month')->distinct()->orderBy('month', 'desc')->pluck('month');
        if (! $availableMonths->contains($month)) {
            $availableMonths->prepend($month);
        }

        return Inertia::render('Admin/Payroll/Index', [
            'payrolls' => $payrolls,
            'stats' => $stats,
            'month' => $month,
            'availableMonths' => $availableMonths,
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

        $employees = Employee::with('user')->where('status', 'active')->get();

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

        return redirect()->back()->with('success', "Gaji {$payroll->employee?->user?->name} periode {$payroll->month} berhasil ditandai LUNAS.");
    }
}
