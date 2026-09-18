<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeAppreciation;
use App\Models\EmployeeDailyScore;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerformanceScoringService
{
    public const BASE_PRESENT_SCORE = 100;

    public const BASE_LEAVE_SCORE = 80;

    public const MIN_LATE_SCORE = 40;

    /**
     * Calculate and sync daily score for an attendance record.
     */
    public function syncDailyScoreForAttendance(Attendance $attendance): EmployeeDailyScore
    {
        $dateStr = $attendance->date instanceof Carbon ? $attendance->date->toDateString() : (string) $attendance->date;
        $status = $attendance->status; // present, late, leave, absent
        $checkInTime = $attendance->check_in_at ? Carbon::parse($attendance->check_in_at)->toTimeString() : null;

        $lateMinutes = 0;
        $attendanceScore = 0;

        if ($status === 'present') {
            $attendanceScore = self::BASE_PRESENT_SCORE;
        } elseif ($status === 'late') {
            // Assume 08:00 as default work start if schedule is missing
            $startTime = '08:00:00';
            $schedule = $attendance->employee?->currentSchedule();
            if ($schedule && $schedule->start_time) {
                $startTime = $schedule->start_time;
            }

            if ($checkInTime) {
                $target = Carbon::parse($dateStr.' '.$startTime);
                $actual = Carbon::parse($attendance->check_in_at);
                if ($actual->greaterThan($target)) {
                    $lateMinutes = (int) ceil($actual->diffInMinutes($target));
                }
            }
            if ($lateMinutes === 0) {
                $lateMinutes = 15; // default penalty fallback
            }

            $deduction = (int) floor($lateMinutes / 2);
            $attendanceScore = max(self::MIN_LATE_SCORE, self::BASE_PRESENT_SCORE - $deduction);
        } elseif ($status === 'leave') {
            $attendanceScore = self::BASE_LEAVE_SCORE;
        } else {
            $attendanceScore = 0;
        }

        // Sum appreciation points for this employee on this date
        $appreciationScore = (int) EmployeeAppreciation::where('employee_id', $attendance->employee_id)
            ->where('date', $dateStr)
            ->sum('points');

        $totalScore = $attendanceScore + $appreciationScore;

        return EmployeeDailyScore::updateOrCreate(
            [
                'employee_id' => $attendance->employee_id,
                'date' => $dateStr,
            ],
            [
                'attendance_status' => $status,
                'check_in_time' => $checkInTime,
                'late_minutes' => $lateMinutes,
                'attendance_score' => $attendanceScore,
                'appreciation_score' => $appreciationScore,
                'total_score' => $totalScore,
                'notes' => $attendance->note,
            ]
        );
    }

    /**
     * Record a new appreciation by HR and update daily score.
     */
    public function recordAppreciation(array $data): EmployeeAppreciation
    {
        $appreciation = EmployeeAppreciation::create([
            'employee_id' => $data['employee_id'],
            'admin_id' => $data['admin_id'] ?? auth()->id(),
            'source' => $data['source'] ?? 'sosmed',
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'evidence_url' => $data['evidence_url'] ?? null,
            'points' => (int) ($data['points'] ?? 100),
            'date' => $data['date'] ?? now()->toDateString(),
        ]);

        $dateStr = $appreciation->date instanceof Carbon ? $appreciation->date->toDateString() : (string) $appreciation->date;

        // Recalculate daily score for that employee and date
        $dailyScore = EmployeeDailyScore::firstOrNew([
            'employee_id' => $appreciation->employee_id,
            'date' => $dateStr,
        ]);

        if (! $dailyScore->exists) {
            // Check if attendance exists
            $attendance = Attendance::where('employee_id', $appreciation->employee_id)
                ->where('date', $dateStr)
                ->first();

            if ($attendance) {
                $this->syncDailyScoreForAttendance($attendance);
            } else {
                $dailyScore->attendance_status = 'present';
                $dailyScore->attendance_score = 100;
                $dailyScore->appreciation_score = (int) $appreciation->points;
                $dailyScore->total_score = 100 + (int) $appreciation->points;
                $dailyScore->save();
            }
        } else {
            $totalAppreciations = (int) EmployeeAppreciation::where('employee_id', $appreciation->employee_id)
                ->where('date', $dateStr)
                ->sum('points');

            $dailyScore->appreciation_score = $totalAppreciations;
            $dailyScore->total_score = $dailyScore->attendance_score + $totalAppreciations;
            $dailyScore->save();
        }

        return $appreciation;
    }

    /**
     * Get monthly leaderboards split across all 4 operational positions.
     *
     * @return array<string, array{position: string, podium: array, leaderboard: array, total_candidates: int}>
     */
    public function getMonthlyLeaderboards(int $month, int $year, ?string $region = null): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $positions = Employee::POSITIONS;
        $results = [];

        foreach ($positions as $posKey => $posLabel) {
            // Query employees in this position
            $query = Employee::query()
                ->where('position', $posKey)
                ->with('user:id,name,email');

            if ($region && $region !== 'all') {
                $query->where('region', $region);
            }

            // Aggregate daily scores within the month
            $scoresSubquery = DB::table('employee_daily_scores')
                ->select(
                    'employee_id',
                    DB::raw('SUM(attendance_score) as total_attendance_score'),
                    DB::raw('SUM(appreciation_score) as total_appreciation_score'),
                    DB::raw('SUM(total_score) as final_total_score'),
                    DB::raw("COUNT(CASE WHEN attendance_status = 'present' THEN 1 END) as count_present"),
                    DB::raw("COUNT(CASE WHEN attendance_status = 'late' THEN 1 END) as count_late"),
                    DB::raw("COUNT(CASE WHEN attendance_status = 'leave' THEN 1 END) as count_leave"),
                    DB::raw("COUNT(CASE WHEN attendance_status = 'absent' THEN 1 END) as count_absent"),
                    DB::raw('COUNT(id) as total_days_evaluated')
                )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('employee_id');

            // Join scores with employees
            $leaderboard = $query
                ->leftJoinSub($scoresSubquery, 'scores', function ($join) {
                    $join->on('employees.id', '=', 'scores.employee_id');
                })
                ->select(
                    'employees.id',
                    'employees.user_id',
                    'employees.employee_code',
                    'employees.position',
                    'employees.department',
                    'employees.region',
                    'employees.pool_depot',
                    'employees.employment_status',
                    'employees.avatar',
                    DB::raw('COALESCE(scores.total_attendance_score, 0) as total_attendance_score'),
                    DB::raw('COALESCE(scores.total_appreciation_score, 0) as total_appreciation_score'),
                    DB::raw('COALESCE(scores.final_total_score, 0) as final_total_score'),
                    DB::raw('COALESCE(scores.count_present, 0) as count_present'),
                    DB::raw('COALESCE(scores.count_late, 0) as count_late'),
                    DB::raw('COALESCE(scores.count_leave, 0) as count_leave'),
                    DB::raw('COALESCE(scores.count_absent, 0) as count_absent'),
                    DB::raw('COALESCE(scores.total_days_evaluated, 0) as total_days_evaluated')
                )
                ->orderByDesc('final_total_score')
                ->orderByDesc('total_attendance_score')
                ->orderByDesc('count_present')
                ->take(20)
                ->get();

            // Total count of employees in this category
            $totalCandidates = Employee::where('position', $posKey)
                ->when($region && $region !== 'all', fn ($q) => $q->where('region', $region))
                ->count();

            // Format items with rank and badges
            $rankedList = $leaderboard->values()->map(function ($emp, $idx) {
                $rank = $idx + 1;
                $emp->rank = $rank;
                $emp->rank_badge = match ($rank) {
                    1 => 'gold',
                    2 => 'silver',
                    3 => 'bronze',
                    default => 'normal',
                };
                $emp->total_days_worked = $emp->count_present + $emp->count_late;
                $emp->punctuality_rate = $emp->total_days_worked > 0
                    ? round(($emp->count_present / $emp->total_days_worked) * 100)
                    : 100;

                return $emp;
            });

            $podium = $rankedList->slice(0, 3)->values()->all();
            $tableList = $rankedList->all();

            $results[$posKey] = [
                'position' => $posLabel,
                'podium' => $podium,
                'leaderboard' => $tableList,
                'total_candidates' => $totalCandidates,
            ];
        }

        return $results;
    }

    /**
     * Get comprehensive 360-degree employee summary for the detail modal.
     */
    public function getEmployeeSummary(int|Employee $employee, ?int $month = null, ?int $year = null): array
    {
        if (is_numeric($employee)) {
            $employee = Employee::with('user')->findOrFail($employee);
        } else {
            $employee->loadMissing('user');
        }

        $month = $month ?? (int) now()->month;
        $year = $year ?? (int) now()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        // 1. Monthly Score & Performance Stats
        $monthlyScores = EmployeeDailyScore::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalAttendanceScore = (int) $monthlyScores->sum('attendance_score');
        $totalAppreciationScore = (int) $monthlyScores->sum('appreciation_score');
        $finalScore = $totalAttendanceScore + $totalAppreciationScore;

        $countPresent = $monthlyScores->where('attendance_status', 'present')->count();
        $countLate = $monthlyScores->where('attendance_status', 'late')->count();
        $countLeave = $monthlyScores->where('attendance_status', 'leave')->count();
        $countAbsent = $monthlyScores->where('attendance_status', 'absent')->count();
        $totalDaysWorked = $countPresent + $countLate;

        $punctualityRate = $totalDaysWorked > 0
            ? round(($countPresent / $totalDaysWorked) * 100)
            : 100;

        // 2. Rank in their position for this month
        $betterScoresCount = DB::table('employee_daily_scores as s')
            ->join('employees as e', 's.employee_id', '=', 'e.id')
            ->where('e.position', $employee->position)
            ->whereBetween('s.date', [$startDate, $endDate])
            ->select('s.employee_id')
            ->groupBy('s.employee_id')
            ->havingRaw('SUM(s.total_score) > ?', [$finalScore])
            ->get()
            ->count();

        $rankInPosition = $betterScoresCount + 1;
        $totalInPosition = Employee::where('position', $employee->position)->count();

        // 3. All appreciations received by this employee
        $appreciations = EmployeeAppreciation::where('employee_id', $employee->id)
            ->with('admin:id,name')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        // 4. Recent attendance log (last 10 days)
        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->take(10)
            ->get()
            ->map(function ($att) {
                return [
                    'id' => $att->id,
                    'date' => $att->date?->format('Y-m-d') ?? (string) $att->date,
                    'status' => $att->status,
                    'check_in' => $att->check_in_at ? Carbon::parse($att->check_in_at)->format('H:i') : '-',
                    'check_out' => $att->check_out_at ? Carbon::parse($att->check_out_at)->format('H:i') : '-',
                    'note' => $att->note,
                ];
            });

        return [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->user?->name ?? 'Pegawai',
                'email' => $employee->user?->email,
                'phone' => $employee->phone ?? '-',
                'employee_code' => $employee->employee_code,
                'position' => $employee->position,
                'department' => $employee->department,
                'region' => $employee->region,
                'region_label' => $employee->region_label,
                'pool_depot' => $employee->pool_depot,
                'employment_status' => $employee->employment_status,
                'employment_status_label' => $employee->employment_status_label,
                'joined_date' => $employee->joined_date?->format('d M Y') ?? '-',
                'avatar' => $employee->avatar,
            ],
            'performance' => [
                'month' => $month,
                'year' => $year,
                'month_name' => Carbon::createFromDate($year, $month, 1)->locale('id')->isoFormat('MMMM YYYY'),
                'final_score' => $finalScore,
                'attendance_score' => $totalAttendanceScore,
                'appreciation_score' => $totalAppreciationScore,
                'rank' => $rankInPosition,
                'total_in_position' => $totalInPosition,
                'count_present' => $countPresent,
                'count_late' => $countLate,
                'count_leave' => $countLeave,
                'count_absent' => $countAbsent,
                'total_days_worked' => $totalDaysWorked,
                'punctuality_rate' => $punctualityRate,
            ],
            'appreciations' => $appreciations,
            'recent_attendances' => $recentAttendances,
        ];
    }
}
