<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Calculate distance between two coordinates in meters using the Haversine formula.
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Check if given coordinates are within the office radius.
     */
    public static function isWithinOfficeRadius(float $lat, float $lng, ?float &$calculatedDistance = null): bool
    {
        $officeLat = (float) Setting::get('office_latitude', -6.2088);
        $officeLng = (float) Setting::get('office_longitude', 106.8456);
        $maxRadius = (int) Setting::get('office_radius', 150);

        $calculatedDistance = self::calculateDistance($lat, $lng, $officeLat, $officeLng);

        return $calculatedDistance <= $maxRadius;
    }

    /**
     * Determine attendance check-in status (present vs late).
     */
    public static function determineStatus(Carbon $checkInTime, ?WorkSchedule $schedule): string
    {
        if (!$schedule) {
            return 'present';
        }

        $today = $checkInTime->toDateString();
        $startTime = Carbon::parse($today . ' ' . $schedule->start_time);
        $toleranceLimit = $startTime->copy()->addMinutes($schedule->tolerance_minutes);

        if ($checkInTime->greaterThan($toleranceLimit)) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Calculate working days excluding Saturdays and Sundays.
     */
    public static function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        $days = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if (!$current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }

        return max(1, $days);
    }
}
