<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\WorkSchedule;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /**
     * Display main employee attendance dashboard.
     */
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            abort(403, 'Profil pegawai tidak ditemukan.');
        }

        $today = Carbon::today()->toDateString();
        $currentSchedule = $employee->currentSchedule() ?? WorkSchedule::first();

        // Today's attendance
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // 7 days history
        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->take(7)
            ->get();

        // Announcements & Holidays
        $announcements = Announcement::where('is_active', true)->latest()->take(3)->get();
        $upcomingHolidays = Holiday::where('date', '>=', $today)
            ->orderBy('date', 'asc')
            ->take(3)
            ->get();

        return Inertia::render('Employee/Attendance', [
            'employee' => $employee->load('user'),
            'schedule' => $currentSchedule,
            'todayAttendance' => $todayAttendance,
            'recentAttendances' => $recentAttendances,
            'announcements' => $announcements,
            'upcomingHolidays' => $upcomingHolidays,
            'officeLocation' => [
                'name' => Setting::get('office_name', 'Kantor Pusat Jakarta'),
                'latitude' => (float) Setting::get('office_latitude', -6.2088),
                'longitude' => (float) Setting::get('office_longitude', 106.8456),
                'radius' => (int) Setting::get('office_radius', 150),
            ],
            'serverTime' => now()->toISOString(),
        ]);
    }

    /**
     * Handle employee check-in.
     */
    public function checkIn(Request $request)
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return back()->with('error', 'Profil pegawai tidak ditemukan.');
        }

        $today = Carbon::today()->toDateString();

        // Check if already checked in
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->check_in_at) {
            return back()->with('error', 'Anda sudah melakukan check-in hari ini.');
        }

        $validated = $request->validate([
            'status' => 'required|in:present,wfh,permission,sick',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'note' => 'nullable|string|max:500',
            'photo' => 'nullable|string', // base64 data url from camera capture
        ]);

        $status = $validated['status'];
        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $distance = 0.0;

        // Radius check for present
        if ($status === 'present') {
            $isWithinRadius = AttendanceService::isWithinOfficeRadius($lat, $lng, $distance);
            $maxRadius = (int) Setting::get('office_radius', 150);
            if (! $isWithinRadius) {
                return back()->with('error', "Lokasi Anda berada di luar radius kantor ({$distance}m dari kantor, batas maksimal {$maxRadius}m).");
            }

            // Determine if present or late based on schedule
            $schedule = $employee->currentSchedule() ?? WorkSchedule::first();
            $status = AttendanceService::determineStatus(now(), $schedule);
        }

        // Save selfie photo if provided
        $photoPath = null;
        if (! empty($validated['photo']) && str_contains($validated['photo'], 'data:image')) {
            $imageData = $validated['photo'];
            $imageParts = explode(';base64,', $imageData);
            $imageTypeAux = explode('image/', $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'jpg';

            // Whitelist allowed image types to prevent arbitrary file extension injection
            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
            if (! in_array($imageType, $allowedTypes)) {
                $imageType = 'jpg';
            }

            $imageBase64 = base64_decode($imageParts[1]);

            $fileName = 'selfies/'.$employee->id.'_'.time().'.'.$imageType;
            Storage::disk('public')->put($fileName, $imageBase64);
            $photoPath = '/storage/'.$fileName;
        }

        Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $today,
            ],
            [
                'check_in_at' => now(),
                'check_in_lat' => $lat,
                'check_in_lng' => $lng,
                'status' => $status,
                'note' => $validated['note'] ?? null,
                'photo_path' => $photoPath,
            ]
        );

        return back()->with('success', 'Check-in berhasil dicatat!');
    }

    /**
     * Handle employee check-out.
     */
    public function checkOut(Request $request)
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return back()->with('error', 'Profil pegawai tidak ditemukan.');
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in_at) {
            return back()->with('error', 'Anda belum melakukan check-in hari ini.');
        }

        if ($attendance->check_out_at) {
            return back()->with('error', 'Anda sudah melakukan check-out hari ini.');
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'note' => 'nullable|string|max:500',
        ]);

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        // If present status, check radius on check-out
        if (in_array($attendance->status, ['present', 'late'])) {
            $distance = 0.0;
            $isWithinRadius = AttendanceService::isWithinOfficeRadius($lat, $lng, $distance);
            if (! $isWithinRadius) {
                return back()->with('error', "Lokasi check-out di luar radius kantor ({$distance}m dari kantor).");
            }
        }

        $note = $attendance->note;
        if (! empty($validated['note'])) {
            $note = $note ? ($note.' | Keluar: '.$validated['note']) : ('Keluar: '.$validated['note']);
        }

        $attendance->update([
            'check_out_at' => now(),
            'check_out_lat' => $lat,
            'check_out_lng' => $lng,
            'note' => $note,
        ]);

        return back()->with('success', 'Check-out berhasil dicatat. Sampai jumpa besok!');
    }

    /**
     * Display attendance history with month/year filters and summary cards.
     */
    public function history(Request $request): Response
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            abort(403, 'Profil pegawai tidak ditemukan.');
        }

        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $query = Attendance::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        $attendances = (clone $query)->orderBy('date', 'desc')->get();

        // Summary counts
        $totalPresent = (clone $query)->where('status', 'present')->count();
        $totalLate = (clone $query)->where('status', 'late')->count();
        $totalWfh = (clone $query)->where('status', 'wfh')->count();
        $totalSickOrPermit = (clone $query)->whereIn('status', ['sick', 'permission'])->count();
        $totalAbsent = (clone $query)->where('status', 'absent')->count();

        return Inertia::render('Employee/AttendanceHistory', [
            'attendances' => $attendances,
            'filters' => [
                'month' => $month,
                'year' => $year,
            ],
            'summary' => [
                'present' => $totalPresent,
                'late' => $totalLate,
                'wfh' => $totalWfh,
                'permission' => $totalSickOrPermit,
                'absent' => $totalAbsent,
            ],
        ]);
    }
}
