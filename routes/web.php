<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\ComplaintController as EmployeeComplaintController;
use App\Http\Controllers\Employee\LeaveRequestController as EmployeeLeaveRequestController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('employee.attendance');
    }
    return redirect()->route('login');
});

// Fallback dashboard route from breeze
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('employee.attendance');
})->middleware(['auth'])->name('dashboard');

// ==========================================
// EMPLOYEE ROUTES (Role: Pegawai)
// ==========================================
Route::middleware(['auth'])->prefix('employee')->name('employee.')->group(function () {
    // Attendance
    Route::get('/attendance', [EmployeeAttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/check-in', [EmployeeAttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [EmployeeAttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/history', [EmployeeAttendanceController::class, 'history'])->name('history');

    // Leave Requests
    Route::get('/leave-requests', [EmployeeLeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('/leave-requests/create', [EmployeeLeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('/leave-requests', [EmployeeLeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::delete('/leave-requests/{leaveRequest}', [EmployeeLeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');

    // Complaints
    Route::get('/complaints', [EmployeeComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [EmployeeComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [EmployeeComplaintController::class, 'store'])->name('complaints.store');

    // Profile
    Route::get('/profile', [EmployeeProfileController::class, 'edit'])->name('profile');
    Route::post('/profile/personal', [EmployeeProfileController::class, 'updatePersonal'])->name('profile.personal');
    Route::post('/profile/bank', [EmployeeProfileController::class, 'updateBank'])->name('profile.bank');
    Route::post('/profile/password', [EmployeeProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [EmployeeProfileController::class, 'updateAvatar'])->name('profile.avatar');
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

    // Attendance Management
    Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendance.update');

    // Leave Requests Management
    Route::get('/leave-requests', [AdminLeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::post('/leave-requests/{leaveRequest}/approve', [AdminLeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('/leave-requests/{leaveRequest}/reject', [AdminLeaveRequestController::class, 'reject'])->name('leave-requests.reject');

    // Complaints Management
    Route::get('/complaints', [AdminComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints/{complaint}/status', [AdminComplaintController::class, 'updateStatus'])->name('complaints.status');
    Route::post('/complaints/{complaint}/resolve', [AdminComplaintController::class, 'resolve'])->name('complaints.resolve');
    Route::post('/complaints/{complaint}/reject', [AdminComplaintController::class, 'reject'])->name('complaints.reject');
});

require __DIR__.'/auth.php';
