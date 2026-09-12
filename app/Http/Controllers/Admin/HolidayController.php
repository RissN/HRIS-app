<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HolidayController extends Controller
{
    public function index(): Response
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();

        return Inertia::render('Admin/Holidays/Index', [
            'holidays' => $holidays,
            'flash' => [
                'success' => session('success'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            'description' => 'nullable|string|max:500',
        ], [
            'date.unique' => 'Tanggal libur ini sudah ada di dalam kalender.',
        ]);

        Holiday::create($validated);

        return redirect()->back()->with('success', 'Hari libur berhasil ditambahkan ke kalender.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return redirect()->back()->with('success', 'Hari libur berhasil dihapus.');
    }
}
