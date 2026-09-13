<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = [
            'office_name' => Setting::get('office_name', 'Kantor Pusat Jakarta'),
            'office_latitude' => Setting::get('office_latitude', '-6.2088000'),
            'office_longitude' => Setting::get('office_longitude', '106.8456000'),
            'office_radius' => Setting::get('office_radius', '150'),
            'rate_daily_allowance' => Setting::get('rate_daily_allowance', '50000'),
            'rate_late_deduction' => Setting::get('rate_late_deduction', '25000'),
            'rate_absent_deduction' => Setting::get('rate_absent_deduction', '100000'),
            'default_annual_leave_quota' => Setting::get('default_annual_leave_quota', '12'),
        ];

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'flash' => [
                'success' => session('success'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'office_name' => 'required|string|max:255',
            'office_latitude' => 'required|numeric|between:-90,90',
            'office_longitude' => 'required|numeric|between:-180,180',
            'office_radius' => 'required|numeric|min:10|max:5000',
            'rate_daily_allowance' => 'required|numeric|min:0',
            'rate_late_deduction' => 'required|numeric|min:0',
            'rate_absent_deduction' => 'required|numeric|min:0',
            'default_annual_leave_quota' => 'required|integer|min:1|max:365',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) $value);
        }

        return redirect()->back()->with('success', 'Pengaturan sistem HRIS dan lokasi kantor berhasil diperbarui.');
    }
}
