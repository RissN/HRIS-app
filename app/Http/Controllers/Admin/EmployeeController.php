<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $department = $request->input('department');
        $region = $request->input('region');
        $employmentStatus = $request->input('employment_status');
        $position = $request->input('position');

        $query = Employee::with(['user', 'employeeSchedules.schedule']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('pool_depot', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($department && $department !== 'all') {
            $query->where('department', $department);
        }

        if ($region && $region !== 'all') {
            $query->where('region', $region);
        }

        if ($employmentStatus && $employmentStatus !== 'all') {
            $query->where('employment_status', $employmentStatus);
        }

        if ($position && $position !== 'all') {
            $query->where('position', $position);
        }

        $paginatedEmployees = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $employees = $paginatedEmployees->through(function ($employee) {
            return [
                'id' => $employee->id,
                'user_id' => $employee->user_id,
                'employee_code' => $employee->employee_code,
                'name' => $employee->user->name,
                'email' => $employee->user->email,
                'phone' => $employee->phone,
                'position' => $employee->position,
                'department' => $employee->department,
                'region' => $employee->region,
                'region_label' => $employee->region_label,
                'employment_status' => $employee->employment_status,
                'employment_status_label' => $employee->employment_status_label,
                'pool_depot' => $employee->pool_depot,
                'joined_date' => $employee->joined_date?->format('Y-m-d'),
                'annual_leave_quota' => (int) ($employee->annual_leave_quota ?? 12),
                'is_active' => $employee->user->is_active,
                'current_schedule' => $employee->currentSchedule()?->name ?? 'Belum Diatur',
                'avatar' => $employee->avatar,
            ];
        });

        $departments = Employee::select('department')->distinct()->pluck('department');

        return Inertia::render('Admin/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'regions' => Employee::REGIONS,
            'employmentStatuses' => Employee::EMPLOYMENT_STATUSES,
            'positions' => Employee::POSITIONS,
            'filters' => [
                'search' => $search,
                'department' => $department,
                'region' => $region,
                'employment_status' => $employmentStatus,
                'position' => $position,
            ],
            'totalStats' => [
                'total' => Employee::count(),
                'tetap' => Employee::where('employment_status', 'tetap')->count(),
                'vendor' => Employee::where('employment_status', 'vendor')->count(),
                'magang' => Employee::where('employment_status', 'magang')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $schedules = WorkSchedule::all();
        $defaultQuota = (int) Setting::get('default_annual_leave_quota', '12');

        return Inertia::render('Admin/Employees/Create', [
            'schedules' => $schedules,
            'defaultAnnualLeaveQuota' => $defaultQuota,
            'regions' => Employee::REGIONS,
            'employmentStatuses' => Employee::EMPLOYMENT_STATUSES,
            'positions' => Employee::POSITIONS,
            'pools' => Employee::POOLS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'employee_code' => 'nullable|string|max:30|unique:employees,employee_code',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'region' => ['required', Rule::in(array_keys(Employee::REGIONS))],
            'employment_status' => ['required', Rule::in(array_keys(Employee::EMPLOYMENT_STATUSES))],
            'pool_depot' => 'nullable|string|max:100',
            'annual_leave_quota' => 'nullable|integer|min:0|max:365',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'joined_date' => 'required|date',
            'schedule_id' => 'required|exists:work_schedules,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'pegawai',
            'is_active' => true,
        ]);

        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);
        $user->assignRole($pegawaiRole);

        $defaultQuota = (int) Setting::get('default_annual_leave_quota', '12');

        $employeeCode = ! empty($validated['employee_code'])
            ? $validated['employee_code']
            : Employee::generateEmployeeCode($validated['employment_status']);

        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => $employeeCode,
            'phone' => $validated['phone'],
            'position' => $validated['position'],
            'department' => $validated['department'],
            'region' => $validated['region'],
            'employment_status' => $validated['employment_status'],
            'pool_depot' => $validated['pool_depot'],
            'annual_leave_quota' => $validated['annual_leave_quota'] ?? $defaultQuota,
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'joined_date' => $validated['joined_date'],
        ]);

        EmployeeSchedule::create([
            'employee_id' => $employee->id,
            'schedule_id' => $validated['schedule_id'],
            'effective_date' => $validated['joined_date'],
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Pegawai baru Transjakarta berhasil ditambahkan!');
    }

    public function edit(Employee $employee): Response
    {
        $employee->load(['user', 'employeeSchedules.schedule']);
        $schedules = WorkSchedule::all();
        $currentSchedule = $employee->currentSchedule();

        return Inertia::render('Admin/Employees/Edit', [
            'employee' => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'name' => $employee->user->name,
                'email' => $employee->user->email,
                'phone' => $employee->phone,
                'position' => $employee->position,
                'department' => $employee->department,
                'region' => $employee->region,
                'employment_status' => $employee->employment_status,
                'pool_depot' => $employee->pool_depot,
                'annual_leave_quota' => (int) ($employee->annual_leave_quota ?? 12),
                'bank_name' => $employee->bank_name,
                'account_number' => $employee->account_number,
                'joined_date' => $employee->joined_date?->format('Y-m-d'),
                'schedule_id' => $currentSchedule?->id,
                'is_active' => $employee->user->is_active,
            ],
            'schedules' => $schedules,
            'regions' => Employee::REGIONS,
            'employmentStatuses' => Employee::EMPLOYMENT_STATUSES,
            'positions' => Employee::POSITIONS,
            'pools' => Employee::POOLS,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $user = $employee->user;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'employee_code' => ['nullable', 'string', 'max:30', Rule::unique('employees', 'employee_code')->ignore($employee->id)],
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'region' => ['required', Rule::in(array_keys(Employee::REGIONS))],
            'employment_status' => ['required', Rule::in(array_keys(Employee::EMPLOYMENT_STATUSES))],
            'pool_depot' => 'nullable|string|max:100',
            'annual_leave_quota' => 'required|integer|min:0|max:365',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'joined_date' => 'required|date',
            'schedule_id' => 'required|exists:work_schedules,id',
            'is_active' => 'required|boolean',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'],
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        $employeeData = [
            'phone' => $validated['phone'],
            'position' => $validated['position'],
            'department' => $validated['department'],
            'region' => $validated['region'],
            'employment_status' => $validated['employment_status'],
            'pool_depot' => $validated['pool_depot'],
            'annual_leave_quota' => $validated['annual_leave_quota'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'joined_date' => $validated['joined_date'],
        ];

        if (! empty($validated['employee_code'])) {
            $employeeData['employee_code'] = $validated['employee_code'];
        }

        $employee->update($employeeData);

        // Check if schedule changed
        $currentSchedule = $employee->currentSchedule();
        if (! $currentSchedule || $currentSchedule->id != $validated['schedule_id']) {
            EmployeeSchedule::create([
                'employee_id' => $employee->id,
                'schedule_id' => $validated['schedule_id'],
                'effective_date' => now()->toDateString(),
            ]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data pegawai Transjakarta berhasil diperbarui!');
    }

    public function toggleStatus(Employee $employee)
    {
        $user = $employee->user;
        $user->update(['is_active' => ! $user->is_active]);

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun pegawai berhasil {$statusText}.");
    }
}
