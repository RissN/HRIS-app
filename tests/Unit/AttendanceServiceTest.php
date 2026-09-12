<?php

namespace Tests\Unit;

use App\Services\AttendanceService;
use PHPUnit\Framework\TestCase;

class AttendanceServiceTest extends TestCase
{
    public function test_calculate_distance_same_coordinates_returns_zero(): void
    {
        $distance = AttendanceService::calculateDistance(-6.2088, 106.8456, -6.2088, 106.8456);
        $this->assertEquals(0.0, $distance);
    }

    public function test_calculate_distance_known_points(): void
    {
        // Monas to Bundaran HI is approx 2.2 km
        $distance = AttendanceService::calculateDistance(-6.1753924, 106.8271528, -6.1949511, 106.8231267);
        $this->assertGreaterThan(2000, $distance);
        $this->assertLessThan(2500, $distance);
    }

    public function test_payroll_formula(): void
    {
        $basicSalary = 8000000;
        $presentDays = 20;
        $dailyAllowanceRate = 50000;
        $lateDays = 2;
        $lateDeductionRate = 25000;
        $absentDays = 1;
        $absentDeductionRate = 100000;

        $allowances = ($presentDays + $lateDays) * $dailyAllowanceRate;
        $lateDeduction = $lateDays * $lateDeductionRate;
        $absentDeduction = $absentDays * $absentDeductionRate;

        $netSalary = $basicSalary + $allowances - $lateDeduction - $absentDeduction;

        $this->assertEquals(8950000, $netSalary);
    }
}
