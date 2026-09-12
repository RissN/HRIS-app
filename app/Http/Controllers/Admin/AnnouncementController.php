<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        $announcements = Announcement::with('author')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
            'flash' => [
                'success' => session('success'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,danger,primary',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        Announcement::create($validated);

        return redirect()->back()->with('success', 'Pengumuman baru berhasil diterbitkan.');
    }

    public function toggleStatus(Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            'is_active' => ! $announcement->is_active,
        ]);

        return redirect()->back()->with('success', 'Status tayang pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
