<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\HolidayController as AdminHolidayController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Admin\PayrollController as AdminPayrollController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\ComplaintController as EmployeeComplaintController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\LeaveRequestController as EmployeeLeaveRequestController;
use App\Http\Controllers\Employee\PayrollController as EmployeePayrollController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('employee.dashboard');
    }

    return redirect()->route('login');
});

// Fallback dashboard route from breeze
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('employee.dashboard');
})->middleware(['auth'])->name('dashboard');

// ==========================================
// EMPLOYEE ROUTES (Role: Pegawai)
// ==========================================
Route::middleware(['auth', 'role:pegawai'])->prefix('employee')->name('employee.')->group(function () {
    // Dashboard (Events & Announcements)
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    // Attendance
    Route::get('/attendance', [EmployeeAttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/check-in', [EmployeeAttendanceController::class, 'checkIn'])->middleware('throttle:10,1')->name('attendance.check-in');
    Route::post('/attendance/check-out', [EmployeeAttendanceController::class, 'checkOut'])->middleware('throttle:10,1')->name('attendance.check-out');
    Route::get('/history', [EmployeeAttendanceController::class, 'history'])->name('history');

    // Leave Requests
    Route::get('/leave-requests', [EmployeeLeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('/leave-requests/create', [EmployeeLeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('/leave-requests', [EmployeeLeaveRequestController::class, 'store'])->middleware('throttle:10,1')->name('leave-requests.store');
    Route::delete('/leave-requests/{leaveRequest}', [EmployeeLeaveRequestController::class, 'cancel'])->middleware('throttle:10,1')->name('leave-requests.cancel');

    // Complaints
    Route::get('/complaints', [EmployeeComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [EmployeeComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [EmployeeComplaintController::class, 'store'])->middleware('throttle:10,1')->name('complaints.store');

    // Profile
    Route::get('/profile', [EmployeeProfileController::class, 'edit'])->name('profile');
    Route::post('/profile/personal', [EmployeeProfileController::class, 'updatePersonal'])->middleware('throttle:10,1')->name('profile.personal');
    Route::post('/profile/bank', [EmployeeProfileController::class, 'updateBank'])->middleware('throttle:10,1')->name('profile.bank');
    Route::post('/profile/password', [EmployeeProfileController::class, 'updatePassword'])->middleware('throttle:10,1')->name('profile.password');
    Route::post('/profile/avatar', [EmployeeProfileController::class, 'updateAvatar'])->middleware('throttle:10,1')->name('profile.avatar');

    // Payroll / Slip Gaji Pegawai
    Route::get('/payroll', [EmployeePayrollController::class, 'index'])->name('payroll.index');
});

// ==========================================
// NOTIFICATIONS ROUTES (All Authenticated Users)
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// ==========================================
// ADMIN ROUTES (Role: Admin/HR)
// ==========================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Employees Management
    Route::get('/employees', [AdminEmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [AdminEmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [AdminEmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [AdminEmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [AdminEmployeeController::class, 'update'])->name('employees.update');
    Route::post('/employees/{employee}/toggle-status', [AdminEmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

    // Schedules Management
    Route::get('/schedules', [AdminScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [AdminScheduleController::class, 'store'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [AdminScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [AdminScheduleController::class, 'destroy'])->name('schedules.destroy');

    // Attendance Management & Monitoring
    Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendance.update');

    // Reports & Export
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [AdminReportController::class, 'exportCsv'])->name('reports.export');

    // Leave Requests Management
    Route::get('/leave-requests', [AdminLeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::post('/leave-requests/{leaveRequest}/approve', [AdminLeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('/leave-requests/{leaveRequest}/reject', [AdminLeaveRequestController::class, 'reject'])->name('leave-requests.reject');

    // Complaints Management
    Route::get('/complaints', [AdminComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints/{complaint}/status', [AdminComplaintController::class, 'updateStatus'])->name('complaints.status');
    Route::post('/complaints/{complaint}/resolve', [AdminComplaintController::class, 'resolve'])->name('complaints.resolve');
    Route::post('/complaints/{complaint}/reject', [AdminComplaintController::class, 'reject'])->name('complaints.reject');

    // Announcements Management
    Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
    Route::post('/announcements/{announcement}/toggle-status', [AdminAnnouncementController::class, 'toggleStatus'])->name('announcements.toggle-status');
    Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Holidays Calendar Management
    Route::get('/holidays', [AdminHolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [AdminHolidayController::class, 'store'])->name('holidays.store');
    Route::delete('/holidays/{holiday}', [AdminHolidayController::class, 'destroy'])->name('holidays.destroy');

    // Payroll Estimator & Management
    Route::get('/payroll', [AdminPayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/generate', [AdminPayrollController::class, 'generate'])->name('payroll.generate');
    Route::post('/payroll/{payroll}/paid', [AdminPayrollController::class, 'markAsPaid'])->name('payroll.paid');

    // Office Geofencing & Rates Settings
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
