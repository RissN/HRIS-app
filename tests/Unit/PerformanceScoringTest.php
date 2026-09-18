<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\EmployeeAppreciation;
use App\Services\PerformanceScoringService;
use PHPUnit\Framework\TestCase;

class PerformanceScoringTest extends TestCase
{
    public function test_performance_scoring_constants(): void
    {
        $this->assertEquals(100, PerformanceScoringService::BASE_PRESENT_SCORE);
        $this->assertEquals(80, PerformanceScoringService::BASE_LEAVE_SCORE);
        $this->assertEquals(40, PerformanceScoringService::MIN_LATE_SCORE);
    }

    public function test_appreciation_sources_definition(): void
    {
        $this->assertArrayHasKey('sosmed', EmployeeAppreciation::SOURCES);
        $this->assertArrayHasKey('customer', EmployeeAppreciation::SOURCES);
        $this->assertArrayHasKey('service', EmployeeAppreciation::SOURCES);
        $this->assertArrayHasKey('extra_mile', EmployeeAppreciation::SOURCES);
    }

    public function test_appreciation_source_label_accessor(): void
    {
        $appreciation = new EmployeeAppreciation;
        $appreciation->source = 'sosmed';
        $this->assertStringContainsString('Media Sosial Viral', $appreciation->source_label);

        $appreciation->source = 'customer';
        $this->assertStringContainsString('Pujian Penumpang', $appreciation->source_label);
    }

    public function test_employee_positions_for_leaderboard_separation(): void
    {
        $this->assertArrayHasKey('Pramudi', Employee::POSITIONS);
        $this->assertArrayHasKey('Pramusapa', Employee::POSITIONS);
        $this->assertArrayHasKey('Pramujaga', Employee::POSITIONS);
        $this->assertArrayHasKey('Karyawan Kantor', Employee::POSITIONS);
        $this->assertCount(4, Employee::POSITIONS);
    }

    public function test_score_calculation_formula(): void
    {
        // On-time check-in
        $presentScore = PerformanceScoringService::BASE_PRESENT_SCORE;
        $this->assertEquals(100, $presentScore);

        // 20 minutes late: penalty floor(20/2) = 10 -> 90 points
        $lateMins = 20;
        $lateScore = max(PerformanceScoringService::MIN_LATE_SCORE, PerformanceScoringService::BASE_PRESENT_SCORE - (int) floor($lateMins / 2));
        $this->assertEquals(90, $lateScore);

        // 150 minutes late: penalty floor(150/2) = 75 -> 25 points, but min capped at 40
        $veryLateMins = 150;
        $cappedScore = max(PerformanceScoringService::MIN_LATE_SCORE, PerformanceScoringService::BASE_PRESENT_SCORE - (int) floor($veryLateMins / 2));
        $this->assertEquals(40, $cappedScore);

        // Total score with viral TikTok bonus (+150)
        $tiktokAppreciation = 150;
        $totalDailyScore = $presentScore + $tiktokAppreciation;
        $this->assertEquals(250, $totalDailyScore);
    }
}
