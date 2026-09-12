<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);

        // 2. Office Settings
        Setting::set('office_name', 'Kantor Pusat Jakarta');
        Setting::set('office_latitude', '-6.2088000');
        Setting::set('office_longitude', '106.8456000');
        Setting::set('office_radius', '150'); // 150 meters

        // 3. Work Schedules
        $regularSchedule = WorkSchedule::create([
            'name' => 'Shift Reguler (08:00 - 17:00)',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'tolerance_minutes' => 15,
        ]);

        $morningSchedule = WorkSchedule::create([
            'name' => 'Shift Pagi (07:00 - 15:00)',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            'tolerance_minutes' => 10,
        ]);

        // 4. Admin User
        $adminUser = User::create([
            'name' => 'HR Manager Admin',
            'email' => 'admin@absensi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $adminUser->assignRole($adminRole);

        $adminEmployee = Employee::create([
            'user_id' => $adminUser->id,
            'phone' => '081234567890',
            'position' => 'HR Manager',
            'department' => 'Human Resources',
            'bank_name' => 'BCA',
            'account_number' => '8800112233',
            'joined_date' => '2023-01-15',
            'avatar' => null,
        ]);

        EmployeeSchedule::create([
            'employee_id' => $adminEmployee->id,
            'schedule_id' => $regularSchedule->id,
            'effective_date' => '2023-01-15',
        ]);

        // 5. Employee Users
        $budiUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@absensi.com',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'is_active' => true,
        ]);
        $budiUser->assignRole($pegawaiRole);

        $budiEmployee = Employee::create([
            'user_id' => $budiUser->id,
            'phone' => '081298765432',
            'position' => 'Senior Software Engineer',
            'department' => 'IT & Technology',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'joined_date' => '2023-06-01',
            'avatar' => null,
        ]);

        EmployeeSchedule::create([
            'employee_id' => $budiEmployee->id,
            'schedule_id' => $regularSchedule->id,
            'effective_date' => '2023-06-01',
        ]);

        $sitiUser = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti@absensi.com',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'is_active' => true,
        ]);
        $sitiUser->assignRole($pegawaiRole);

        $sitiEmployee = Employee::create([
            'user_id' => $sitiUser->id,
            'phone' => '085712345678',
            'position' => 'Digital Marketing Specialist',
            'department' => 'Marketing',
            'bank_name' => 'Mandiri',
            'account_number' => '9876543210',
            'joined_date' => '2024-02-10',
            'avatar' => null,
        ]);

        EmployeeSchedule::create([
            'employee_id' => $sitiEmployee->id,
            'schedule_id' => $regularSchedule->id,
            'effective_date' => '2024-02-10',
        ]);

        // 6. Recent Attendances (Past 7 days for Budi and Siti)
        for ($i = 6; $i >= 1; $i--) {
            $date = Carbon::now()->subDays($i);
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // Budi attendances
            $status = ($i === 2) ? 'late' : 'present';
            $inTime = ($i === 2) ? '08:25:00' : '07:52:00';
            Attendance::create([
                'employee_id' => $budiEmployee->id,
                'date' => $date->toDateString(),
                'check_in_at' => Carbon::parse($date->toDateString() . ' ' . $inTime),
                'check_out_at' => Carbon::parse($date->toDateString() . ' 17:05:00'),
                'check_in_lat' => -6.208812,
                'check_in_lng' => 106.845610,
                'check_out_lat' => -6.208820,
                'check_out_lng' => 106.845615,
                'status' => $status,
                'note' => ($status === 'late') ? 'Macet di jalan tol' : 'Hadir tepat waktu di kantor',
            ]);

            // Siti attendances
            Attendance::create([
                'employee_id' => $sitiEmployee->id,
                'date' => $date->toDateString(),
                'check_in_at' => Carbon::parse($date->toDateString() . ' 07:55:00'),
                'check_out_at' => Carbon::parse($date->toDateString() . ' 17:02:00'),
                'check_in_lat' => -6.208805,
                'check_in_lng' => 106.845608,
                'check_out_lat' => -6.208810,
                'check_out_lng' => 106.845612,
                'status' => ($i === 3) ? 'wfh' : 'present',
                'note' => ($i === 3) ? 'WFH atas persetujuan manajer' : 'Hadir di kantor',
            ]);
        }

        // 7. Seed sample Leave Request (Pending)
        LeaveRequest::create([
            'employee_id' => $budiEmployee->id,
            'type' => 'annual_leave',
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(7)->toDateString(),
            'total_days' => 3,
            'reason' => 'Keperluan keluarga dan liburan tahunan.',
            'attachment' => null,
            'status' => 'pending',
        ]);

        // 8. Seed sample Complaint (Pending)
        $lateAttendance = Attendance::where('employee_id', $budiEmployee->id)
            ->where('status', 'late')
            ->first();

        Complaint::create([
            'employee_id' => $budiEmployee->id,
            'attendance_id' => $lateAttendance?->id,
            'date' => $lateAttendance ? $lateAttendance->date->toDateString() : Carbon::now()->subDays(2)->toDateString(),
            'type' => 'wrong_time',
            'description' => 'Saya sudah sampai kantor pukul 08:05 WIB namun koneksi internet HP sempat gangguan sehingga baru bisa check-in pukul 08:25 WIB. Mohon penyesuaian jam masuk.',
            'attachment' => null,
            'status' => 'pending',
        ]);
    }
}
