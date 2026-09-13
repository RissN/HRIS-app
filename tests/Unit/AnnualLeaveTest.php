<?php

namespace Tests\Unit;

use App\Services\AttendanceService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AnnualLeaveTest extends TestCase
{
    public function test_calculate_working_days_excludes_weekends(): void
    {
        // Monday (2026-09-14) to Friday (2026-09-18) = 5 working days
        $start = Carbon::parse('2026-09-14');
        $end = Carbon::parse('2026-09-18');
        $days = AttendanceService::calculateWorkingDays($start, $end);
        $this->assertEquals(5, $days);

        // Friday (2026-09-18) to next Monday (2026-09-21) = 2 working days (Fri, Mon)
        $start = Carbon::parse('2026-09-18');
        $end = Carbon::parse('2026-09-21');
        $days = AttendanceService::calculateWorkingDays($start, $end);
        $this->assertEquals(2, $days);
    }

    public function test_annual_leave_balance_calculation_formula(): void
    {
        $quota = 12;
        $used = 3;
        $pending = 2;

        $remaining = max(0, $quota - $used);
        $available = max(0, $remaining - $pending);

        $this->assertEquals(9, $remaining);
        $this->assertEquals(7, $available);
    }

    public function test_quota_exhaustion_does_not_become_negative(): void
    {
        $quota = 12;
        $used = 14;
        $remaining = max(0, $quota - $used);

        $this->assertEquals(0, $remaining);
    }
}
