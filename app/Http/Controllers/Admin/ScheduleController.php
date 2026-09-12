<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function index(): Response
    {
        $schedules = WorkSchedule::withCount('employeeSchedules')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Admin/Schedules/Index', [
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'days' => 'required|array|min:1',
            'days.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'tolerance_minutes' => 'required|integer|min:0|max:120',
        ]);

        WorkSchedule::create($validated);

        return back()->with('success', 'Jadwal kerja berhasil ditambahkan!');
    }

    public function update(Request $request, WorkSchedule $schedule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'days' => 'required|array|min:1',
            'days.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'tolerance_minutes' => 'required|integer|min:0|max:120',
        ]);

        $schedule->update($validated);

        return back()->with('success', 'Jadwal kerja berhasil diperbarui!');
    }

    public function destroy(WorkSchedule $schedule)
    {
        if ($schedule->employeeSchedules()->exists()) {
            return back()->with('error', 'Jadwal sedang digunakan oleh pegawai dan tidak dapat dihapus.');
        }

        $schedule->delete();

        return back()->with('success', 'Jadwal kerja berhasil dihapus.');
    }
}
