<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAppreciation;
use App\Models\EmployeeDailyScore;
use App\Services\PerformanceScoringService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PerformanceController extends Controller
{
    public function __construct(
        protected PerformanceScoringService $scoringService
    ) {}

    /**
     * Display Employee of the Month leaderboard and appreciation hub.
     */
    public function index(Request $request): Response
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $region = $request->input('region', 'all');

        $leaderboards = $this->scoringService->getMonthlyLeaderboards($month, $year, $region);

        // Recent appreciations awarded by HR
        $recentAppreciations = EmployeeAppreciation::with(['employee.user', 'admin:id,name'])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        // Overall performance stats
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $totalAppreciationsCount = EmployeeAppreciation::whereBetween('date', [$startDate, $endDate])->count();
        $totalAppreciationPoints = (int) EmployeeAppreciation::whereBetween('date', [$startDate, $endDate])->sum('points');
        $averageAttendanceScore = round((float) EmployeeDailyScore::whereBetween('date', [$startDate, $endDate])->avg('attendance_score'), 1);

        // Employee candidate list for appreciation modal dropdown
        $employeeOptions = Employee::with('user:id,name')
            ->select('id', 'user_id', 'employee_code', 'position', 'region', 'pool_depot')
            ->orderBy('employee_code')
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->user?->name ?? 'Pegawai',
                    'employee_code' => $emp->employee_code,
                    'position' => $emp->position,
                    'region' => $emp->region_label,
                    'pool_depot' => $emp->pool_depot,
                    'label' => "{$emp->employee_code} - ".($emp->user?->name ?? 'Pegawai')." ({$emp->position})",
                ];
            });

        return Inertia::render('Admin/Performance/Index', [
            'month' => $month,
            'year' => $year,
            'region' => $region,
            'regions' => Employee::REGIONS,
            'leaderboards' => $leaderboards,
            'recentAppreciations' => $recentAppreciations,
            'stats' => [
                'total_appreciations' => $totalAppreciationsCount,
                'total_points' => $totalAppreciationPoints,
                'average_score' => $averageAttendanceScore ?: 100,
            ],
            'employeeOptions' => $employeeOptions,
            'appreciationSources' => EmployeeAppreciation::SOURCES,
        ]);
    }

    /**
     * Store an appreciation awarded by HR admin.
     */
    public function storeAppreciation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'source' => 'required|in:sosmed,customer,service,extra_mile',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'evidence_url' => 'nullable|url|max:500',
            'points' => 'required|integer|min:10|max:1000',
            'date' => 'required|date',
        ]);

        $validated['admin_id'] = $request->user()->id;

        $appreciation = $this->scoringService->recordAppreciation($validated);

        return back()->with('success', "Apresiasi berhasil diberikan kepada {$appreciation->employee->user->name} (+{$appreciation->points} poin)!");
    }

    /**
     * Remove an appreciation.
     */
    public function destroyAppreciation(EmployeeAppreciation $appreciation): RedirectResponse
    {
        $employeeId = $appreciation->employee_id;
        $dateStr = $appreciation->date->toDateString();

        $appreciation->delete();

        // Recalculate daily score
        $totalAppreciations = (int) EmployeeAppreciation::where('employee_id', $employeeId)
            ->where('date', $dateStr)
            ->sum('points');

        $dailyScore = EmployeeDailyScore::where('employee_id', $employeeId)
            ->where('date', $dateStr)
            ->first();

        if ($dailyScore) {
            $dailyScore->appreciation_score = $totalAppreciations;
            $dailyScore->total_score = $dailyScore->attendance_score + $totalAppreciations;
            $dailyScore->save();
        }

        return back()->with('success', 'Apresiasi berhasil dihapus.');
    }

    /**
     * Get 360-degree employee summary JSON for the quick recap window.
     */
    public function employeeSummary(Employee $employee, Request $request): JsonResponse
    {
        $month = $request->has('month') ? (int) $request->input('month') : null;
        $year = $request->has('year') ? (int) $request->input('year') : null;

        $summary = $this->scoringService->getEmployeeSummary($employee, $month, $year);

        return response()->json($summary);
    }
}
