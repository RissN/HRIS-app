<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
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

        $query = Employee::with(['user', 'employeeSchedules.schedule']);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('position', 'like', "%{$search}%");
        }

        if ($department && $department !== 'all') {
            $query->where('department', $department);
        }

        $employees = $query->orderBy('id', 'desc')->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'user_id' => $employee->user_id,
                'name' => $employee->user->name,
                'email' => $employee->user->email,
                'phone' => $employee->phone,
                'position' => $employee->position,
                'department' => $employee->department,
                'joined_date' => $employee->joined_date?->format('Y-m-d'),
                'is_active' => $employee->user->is_active,
                'current_schedule' => $employee->currentSchedule()?->name ?? 'Belum Diatur',
                'avatar' => $employee->avatar,
            ];
        });

        $departments = Employee::select('department')->distinct()->pluck('department');

        return Inertia::render('Admin/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'filters' => [
                'search' => $search,
                'department' => $department,
            ],
        ]);
    }

    public function create(): Response
    {
        $schedules = WorkSchedule::all();

        return Inertia::render('Admin/Employees/Create', [
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
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

        $employee = Employee::create([
            'user_id' => $user->id,
            'phone' => $validated['phone'],
            'position' => $validated['position'],
            'department' => $validated['department'],
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
            ->with('success', 'Pegawai baru berhasil ditambahkan!');
    }

    public function edit(Employee $employee): Response
    {
        $employee->load(['user', 'employeeSchedules.schedule']);
        $schedules = WorkSchedule::all();
        $currentSchedule = $employee->currentSchedule();

        return Inertia::render('Admin/Employees/Edit', [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->user->name,
                'email' => $employee->user->email,
                'phone' => $employee->phone,
                'position' => $employee->position,
                'department' => $employee->department,
                'bank_name' => $employee->bank_name,
                'account_number' => $employee->account_number,
                'joined_date' => $employee->joined_date?->format('Y-m-d'),
                'schedule_id' => $currentSchedule?->id,
                'is_active' => $employee->user->is_active,
            ],
            'schedules' => $schedules,
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
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
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

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        $employee->update([
            'phone' => $validated['phone'],
            'position' => $validated['position'],
            'department' => $validated['department'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'joined_date' => $validated['joined_date'],
        ]);

        // Check if schedule changed
        $currentSchedule = $employee->currentSchedule();
        if (!$currentSchedule || $currentSchedule->id != $validated['schedule_id']) {
            EmployeeSchedule::create([
                'employee_id' => $employee->id,
                'schedule_id' => $validated['schedule_id'],
                'effective_date' => now()->toDateString(),
            ]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data pegawai berhasil diperbarui!');
    }

    public function toggleStatus(Employee $employee)
    {
        $user = $employee->user;
        $user->update(['is_active' => !$user->is_active]);

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun pegawai berhasil {$statusText}.");
    }
}
