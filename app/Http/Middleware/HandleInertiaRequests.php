<?php

namespace App\Http\Middleware;

use App\Models\Complaint;
use App\Models\LeaveRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        if ($user) {
            $user->load('employee');
        }

        $pendingLeaveCount = 0;
        $pendingComplaintCount = 0;

        if ($user) {
            if ($user->isAdmin()) {
                $pendingLeaveCount = LeaveRequest::where('status', 'pending')->count();
                $pendingComplaintCount = Complaint::whereIn('status', ['pending', 'in_review'])->count();
            } elseif ($user->employee) {
                // For employee, count active pending items
                $pendingLeaveCount = LeaveRequest::where('employee_id', $user->employee->id)
                    ->where('status', 'pending')->count();
                $pendingComplaintCount = Complaint::where('employee_id', $user->employee->id)
                    ->whereIn('status', ['pending', 'in_review'])->count();
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'office' => [
                'name' => Setting::get('office_name', 'Kantor Pusat Jakarta'),
                'latitude' => (float) Setting::get('office_latitude', -6.2088),
                'longitude' => (float) Setting::get('office_longitude', 106.8456),
                'radius' => (int) Setting::get('office_radius', 150),
            ],
            'pendingCounts' => [
                'leaveRequests' => $pendingLeaveCount,
                'complaints' => $pendingComplaintCount,
            ],
        ];
    }
}
