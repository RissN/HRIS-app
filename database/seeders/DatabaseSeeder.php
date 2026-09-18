<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Payroll;
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
        // 1. Roles & Permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);

        // 2. Office Geolocation & Radius Settings
        Setting::set('office_name', 'Kantor Pusat Jakarta');
        Setting::set('office_latitude', '-6.2088000');
        Setting::set('office_longitude', '106.8456000');
        Setting::set('office_radius', '150'); // 150 meters

        // 3. Work Schedules (3 Shifts)
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

        $eveningSchedule = WorkSchedule::create([
            'name' => 'Shift Siang (13:00 - 21:00)',
            'start_time' => '13:00:00',
            'end_time' => '21:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            'tolerance_minutes' => 10,
        ]);

        // 4. Admin Account
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
            'employee_code' => 'TJT1000',
            'phone' => '081234567890',
            'position' => 'HR Manager & General Affairs',
            'department' => 'Human Resources',
            'region' => 'jakarta_pusat',
            'employment_status' => 'tetap',
            'pool_depot' => 'Kantor Pusat Cawang / Koridor 1',
            'bank_name' => 'Bank DKI',
            'account_number' => '8800112233',
            'joined_date' => '2023-01-15',
            'avatar' => null,
        ]);

        EmployeeSchedule::create([
            'employee_id' => $adminEmployee->id,
            'schedule_id' => $regularSchedule->id,
            'effective_date' => '2023-01-15',
        ]);

        // 5. Employees across different departments and shifts
        $employeesData = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@absensi.com',
                'employee_code' => 'TJT1001',
                'phone' => '081298765432',
                'position' => 'Pramudi',
                'department' => 'Operasional Bus',
                'region' => 'jakarta_timur',
                'employment_status' => 'tetap',
                'pool_depot' => 'Pool Cawang',
                'schedule' => $regularSchedule,
                'bank_name' => 'Bank DKI',
                'account_number' => '1234567890',
                'joined_date' => '2023-06-01',
            ],
            [
                'name' => 'Siti Rahma',
                'email' => 'siti@absensi.com',
                'employee_code' => 'TJT1002',
                'phone' => '085712345678',
                'position' => 'Pramusapa',
                'department' => 'Layanan Pelanggan',
                'region' => 'jakarta_barat',
                'employment_status' => 'tetap',
                'pool_depot' => 'Pool Rawa Buaya',
                'schedule' => $regularSchedule,
                'bank_name' => 'Bank DKI',
                'account_number' => '9876543210',
                'joined_date' => '2024-02-10',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@absensi.com',
                'employee_code' => 'TJT1003',
                'phone' => '087811223344',
                'position' => 'Karyawan Kantor',
                'department' => 'Manajemen & Pool',
                'region' => 'jakarta_pusat',
                'employment_status' => 'tetap',
                'pool_depot' => 'Kantor Pusat Cawang / Koridor 1',
                'schedule' => $regularSchedule,
                'bank_name' => 'Bank DKI',
                'account_number' => '5544332211',
                'joined_date' => '2023-09-01',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@absensi.com',
                'employee_code' => 'TJV2001',
                'phone' => '081388776655',
                'position' => 'Pramusapa',
                'department' => 'Layanan Bus Vendor',
                'region' => 'jakarta_utara',
                'employment_status' => 'vendor',
                'pool_depot' => 'Pool Pegangsaan Dua',
                'schedule' => $morningSchedule,
                'bank_name' => 'Bank DKI',
                'account_number' => '4433221100',
                'joined_date' => '2024-01-15',
            ],
            [
                'name' => 'Reza Pratama',
                'email' => 'reza@absensi.com',
                'employee_code' => 'TJV2002',
                'phone' => '085699887766',
                'position' => 'Pramujaga',
                'department' => 'Pengamanan Vendor',
                'region' => 'jakarta_timur',
                'employment_status' => 'vendor',
                'pool_depot' => 'Pool Pinang Ranti',
                'schedule' => $eveningSchedule,
                'bank_name' => 'Bank DKI',
                'account_number' => '6677889900',
                'joined_date' => '2024-03-01',
            ],
        ];

        $createdEmployees = [];
        foreach ($employeesData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'pegawai',
                'is_active' => true,
            ]);
            $user->assignRole($pegawaiRole);

            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_code' => $data['employee_code'],
                'phone' => $data['phone'],
                'position' => $data['position'],
                'department' => $data['department'],
                'region' => $data['region'],
                'employment_status' => $data['employment_status'],
                'pool_depot' => $data['pool_depot'],
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'joined_date' => $data['joined_date'],
                'avatar' => null,
            ]);

            EmployeeSchedule::create([
                'employee_id' => $employee->id,
                'schedule_id' => $data['schedule']->id,
                'effective_date' => $data['joined_date'],
            ]);

            $createdEmployees[$data['email']] = [
                'user' => $user,
                'employee' => $employee,
                'schedule' => $data['schedule'],
            ];
        }

        // 6. Attendance Records (14 Days of History: Today + past 13 days)
        $today = Carbon::today();

        // 6a. Today's live attendances
        // Budi: On time, checked in, still working
        Attendance::create([
            'employee_id' => $createdEmployees['budi@absensi.com']['employee']->id,
            'date' => $today->toDateString(),
            'check_in_at' => Carbon::parse($today->toDateString().' 07:54:00'),
            'check_out_at' => null,
            'check_in_lat' => -6.208810,
            'check_in_lng' => 106.845615,
            'status' => 'present',
            'note' => 'Hadir tepat waktu di kantor',
        ]);

        // Siti: Late, checked in, still working
        Attendance::create([
            'employee_id' => $createdEmployees['siti@absensi.com']['employee']->id,
            'date' => $today->toDateString(),
            'check_in_at' => Carbon::parse($today->toDateString().' 08:24:00'),
            'check_out_at' => null,
            'check_in_lat' => -6.208820,
            'check_in_lng' => 106.845605,
            'status' => 'late',
            'note' => 'Terlambat karena antrean kendaraan di gerbang tol',
        ]);

        // Ahmad: WFH today
        Attendance::create([
            'employee_id' => $createdEmployees['ahmad@absensi.com']['employee']->id,
            'date' => $today->toDateString(),
            'check_in_at' => Carbon::parse($today->toDateString().' 07:58:00'),
            'check_out_at' => null,
            'check_in_lat' => -6.215500,
            'check_in_lng' => 106.852000,
            'status' => 'wfh',
            'note' => 'Bekerja remote dari rumah (approved WFH)',
        ]);

        // Dewi: Shift Pagi (07:00 - 15:00), completed today
        Attendance::create([
            'employee_id' => $createdEmployees['dewi@absensi.com']['employee']->id,
            'date' => $today->toDateString(),
            'check_in_at' => Carbon::parse($today->toDateString().' 06:52:00'),
            'check_out_at' => Carbon::parse($today->toDateString().' 15:05:00'),
            'check_in_lat' => -6.208805,
            'check_in_lng' => 106.845608,
            'check_out_lat' => -6.208810,
            'check_out_lng' => 106.845612,
            'status' => 'present',
            'note' => 'Shift pagi selesai tepat waktu',
        ]);

        // 6b. Past 13 Days History
        for ($i = 1; $i <= 13; $i++) {
            $date = Carbon::today()->subDays($i);

            // Skip Sundays
            if ($date->isSunday()) {
                continue;
            }

            // Skip Saturdays for regular shift (Budi, Siti, Ahmad)
            $isSaturday = $date->isSaturday();

            foreach ($createdEmployees as $email => $empData) {
                $employee = $empData['employee'];
                $sched = $empData['schedule'];

                // Check if employee works on this day
                $dayName = strtolower($date->format('l'));
                if (! in_array($dayName, $sched->days)) {
                    continue;
                }

                // Deterministic variation based on day and employee id
                $seedFactor = ($employee->id * 7 + $i) % 10;

                // Special scenarios for specific days:
                // Ahmad was sick on day 6
                if ($email === 'ahmad@absensi.com' && $i === 6) {
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $date->toDateString(),
                        'check_in_at' => null,
                        'check_out_at' => null,
                        'status' => 'sick',
                        'note' => 'Sakit demam (surat dokter terlampir di HR)',
                    ]);

                    continue;
                }

                // Siti had approved permission on day 4
                if ($email === 'siti@absensi.com' && $i === 4) {
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $date->toDateString(),
                        'check_in_at' => null,
                        'check_out_at' => null,
                        'status' => 'permission',
                        'note' => 'Izin keperluan dinas luar kantor',
                    ]);

                    continue;
                }

                // Normal attendance with realistic variations
                $startTimeParts = explode(':', $sched->start_time);
                $endTimeParts = explode(':', $sched->end_time);

                $status = 'present';
                $inMinute = rand(45, 58); // arrive before schedule
                $inHour = (int) $startTimeParts[0] - 1;
                $note = 'Hadir dan bekerja sesuai jadwal';

                if ($seedFactor === 1 || ($email === 'budi@absensi.com' && $i === 2)) {
                    // Late
                    $status = 'late';
                    $inHour = (int) $startTimeParts[0];
                    $inMinute = rand(16, 28);
                    $note = 'Terlambat check-in karena kendala perjalanan';
                } elseif ($seedFactor === 8) {
                    // WFH
                    $status = 'wfh';
                    $inHour = (int) $startTimeParts[0] - 1;
                    $inMinute = rand(50, 59);
                    $note = 'Bekerja secara remote (WFH terkoordinasi)';
                }

                $inTimeStr = sprintf('%02d:%02d:00', $inHour, $inMinute);
                $outHour = (int) $endTimeParts[0];
                $outMinute = rand(3, 15);
                $outTimeStr = sprintf('%02d:%02d:00', $outHour, $outMinute);

                // Reza forgot checkout on day 3
                $isForgotCheckout = ($email === 'reza@absensi.com' && $i === 3);

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'check_in_at' => Carbon::parse($date->toDateString().' '.$inTimeStr),
                    'check_out_at' => $isForgotCheckout ? null : Carbon::parse($date->toDateString().' '.$outTimeStr),
                    'check_in_lat' => -6.208800 + (rand(-30, 30) / 100000),
                    'check_in_lng' => 106.845600 + (rand(-30, 30) / 100000),
                    'check_out_lat' => $isForgotCheckout ? null : -6.208800 + (rand(-30, 30) / 100000),
                    'check_out_lng' => $isForgotCheckout ? null : 106.845600 + (rand(-30, 30) / 100000),
                    'status' => $status,
                    'note' => $note,
                ]);
            }
        }

        // 7. Leave Requests (Varied Types and Statuses)
        // 7a. Pending Annual Leave (Budi)
        LeaveRequest::create([
            'employee_id' => $createdEmployees['budi@absensi.com']['employee']->id,
            'type' => 'annual_leave',
            'start_date' => Carbon::today()->addDays(5)->toDateString(),
            'end_date' => Carbon::today()->addDays(7)->toDateString(),
            'total_days' => 3,
            'reason' => 'Keperluan keluarga dan liburan tahunan ke Yogyakarta bersama keluarga besar.',
            'attachment' => null,
            'status' => 'pending',
        ]);

        // 7b. Pending Sick Leave (Ahmad)
        LeaveRequest::create([
            'employee_id' => $createdEmployees['ahmad@absensi.com']['employee']->id,
            'type' => 'sick',
            'start_date' => Carbon::today()->addDays(1)->toDateString(),
            'end_date' => Carbon::today()->addDays(2)->toDateString(),
            'total_days' => 2,
            'reason' => 'Pemeriksaan kesehatan pasca rawat jalan di RS Medika.',
            'attachment' => null,
            'status' => 'pending',
        ]);

        // 7c. Approved Permission (Siti)
        LeaveRequest::create([
            'employee_id' => $createdEmployees['siti@absensi.com']['employee']->id,
            'type' => 'permission',
            'start_date' => Carbon::today()->subDays(5)->toDateString(),
            'end_date' => Carbon::today()->subDays(4)->toDateString(),
            'total_days' => 2,
            'reason' => 'Pengurusan administrasi perpanjangan paspor dan KTP di kantor imigrasi.',
            'attachment' => null,
            'status' => 'approved',
            'reviewed_by' => $adminUser->id,
            'reviewed_at' => Carbon::today()->subDays(6)->hour(14)->minute(30),
        ]);

        // 7d. Rejected Emergency Leave (Dewi)
        LeaveRequest::create([
            'employee_id' => $createdEmployees['dewi@absensi.com']['employee']->id,
            'type' => 'emergency_leave',
            'start_date' => Carbon::today()->subDays(10)->toDateString(),
            'end_date' => Carbon::today()->subDays(9)->toDateString(),
            'total_days' => 2,
            'reason' => 'Menghadiri acara pernikahan kerabat dekat di luar kota.',
            'attachment' => null,
            'status' => 'rejected',
            'reviewed_by' => $adminUser->id,
            'reviewed_at' => Carbon::today()->subDays(11)->hour(10)->minute(15),
            'reject_reason' => 'Jadwal shift pagi operasional pada tanggal tersebut sangat padat dan personil pengganti tidak tersedia.',
        ]);

        // 8. Complaints (Varied Types and Statuses)
        // 8a. Pending Complaint (Budi - Wrong Time)
        $budiLateAttendance = Attendance::where('employee_id', $createdEmployees['budi@absensi.com']['employee']->id)
            ->where('status', 'late')
            ->first();

        Complaint::create([
            'employee_id' => $createdEmployees['budi@absensi.com']['employee']->id,
            'attendance_id' => $budiLateAttendance?->id,
            'date' => $budiLateAttendance ? $budiLateAttendance->date->toDateString() : Carbon::today()->subDays(2)->toDateString(),
            'type' => 'wrong_time',
            'description' => 'Saya sudah berada di kantor pukul 07:55 WIB namun server sedang maintenance singkat sehingga baru berhasil absen pukul 08:24 WIB. Mohon koreksi kehadiran menjadi tepat waktu.',
            'attachment' => null,
            'status' => 'pending',
        ]);

        // 8b. In Review Complaint (Reza - Forgot Checkout)
        $rezaForgotAttendance = Attendance::where('employee_id', $createdEmployees['reza@absensi.com']['employee']->id)
            ->whereNull('check_out_at')
            ->where('date', '<', Carbon::today()->toDateString())
            ->first();

        Complaint::create([
            'employee_id' => $createdEmployees['reza@absensi.com']['employee']->id,
            'attendance_id' => $rezaForgotAttendance?->id,
            'date' => $rezaForgotAttendance ? $rezaForgotAttendance->date->toDateString() : Carbon::today()->subDays(3)->toDateString(),
            'type' => 'forgot_checkout',
            'description' => 'Lupa melakukan check-out saat pulang kerja shift siang pukul 21:10 karena langsung terburu-buru ada kebutuhan operasional mendadak.',
            'attachment' => null,
            'status' => 'in_review',
        ]);

        // 8c. Resolved Complaint (Dewi - Location Error)
        Complaint::create([
            'employee_id' => $createdEmployees['dewi@absensi.com']['employee']->id,
            'attendance_id' => null,
            'date' => Carbon::today()->subDays(7)->toDateString(),
            'type' => 'location_error',
            'description' => 'GPS terbaca di luar radius 150m saat berada di lobi kantor lantai dasar karena gangguan cuaca mendung.',
            'attachment' => null,
            'status' => 'resolved',
            'admin_note' => 'Sudah diverifikasi dengan rekaman log akses pintu lobi. Kehadiran telah diperbarui.',
            'resolved_by' => $adminUser->id,
            'resolved_at' => Carbon::today()->subDays(6)->hour(16)->minute(45),
        ]);

        // 8d. Rejected Complaint (Ahmad - System Error)
        Complaint::create([
            'employee_id' => $createdEmployees['ahmad@absensi.com']['employee']->id,
            'attendance_id' => null,
            'date' => Carbon::today()->subDays(9)->toDateString(),
            'type' => 'system_error',
            'description' => 'Aplikasi error tidak bisa kirim foto selfie saat check-in pagi.',
            'attachment' => null,
            'status' => 'rejected',
            'admin_note' => 'Setelah dicek di log aktivitas sistem, aplikasi beroperasi normal pada jam tersebut dan tidak ada kegagalan upload foto.',
            'resolved_by' => $adminUser->id,
            'resolved_at' => Carbon::today()->subDays(8)->hour(11)->minute(20),
        ]);

        // 9. Additional HRIS Settings (Allowance & Deductions)
        Setting::set('rate_daily_allowance', '50000');
        Setting::set('rate_late_deduction', '25000');
        Setting::set('rate_absent_deduction', '100000');

        // 10. Company Announcements
        Announcement::create([
            'title' => 'Pembaruan Kebijakan Jam Kerja & Presensi HRIS',
            'content' => 'Mulai September 2026, seluruh karyawan diwajibkan melakukan presensi melalui portal HRIS terbaru. Toleransi keterlambatan maksimal 15 menit sebelum potongan presensi diberlakukan secara otomatis.',
            'type' => 'info',
            'is_active' => true,
            'created_by' => $adminUser->id,
        ]);

        Announcement::create([
            'title' => 'Jadwal Libur Nasional Maulid Nabi Muhammad SAW',
            'content' => 'Diberitahukan kepada seluruh karyawan bahwa kantor operasional akan libur pada tanggal 16 September 2026. Presensi pada tanggal tersebut dinonaktifkan.',
            'type' => 'primary',
            'is_active' => true,
            'created_by' => $adminUser->id,
        ]);

        Announcement::create([
            'title' => 'Pengingat Batas Pengajuan Klaim Lembur & Koreksi Presensi',
            'content' => 'Batas akhir pengajuan pengaduan koreksi jam presensi atau reimbursement bulanan adalah setiap tanggal 25. Pengajuan yang melewati batas tanggal akan diproses pada periode payroll berikutnya.',
            'type' => 'warning',
            'is_active' => true,
            'created_by' => $adminUser->id,
        ]);

        // 11. National Holidays
        Holiday::create([
            'name' => 'Maulid Nabi Muhammad SAW',
            'date' => '2026-09-16',
            'description' => 'Hari Libur Nasional Keagamaan',
        ]);

        Holiday::create([
            'name' => 'Hari Kesaktian Pancasila',
            'date' => '2026-10-01',
            'description' => 'Hari Peringatan Nasional',
        ]);

        Holiday::create([
            'name' => 'Hari Sumpah Pemuda',
            'date' => '2026-10-28',
            'description' => 'Hari Peringatan Nasional Pemuda',
        ]);

        // 12. Interactive Notifications
        $budiUser = $createdEmployees['budi@absensi.com']['user'];
        Notification::create([
            'user_id' => $budiUser->id,
            'title' => 'Pengajuan Cuti Disetujui',
            'message' => 'Pengajuan cuti tahunan Anda telah disetujui oleh tim HR.',
            'type' => 'leave',
            'link' => '/employee/leaves',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $budiUser->id,
            'title' => 'Pengaduan Presensi Diterima',
            'message' => 'Laporan koreksi presensi tanggal 10 September Anda sedang dalam proses tinjauan manajemen.',
            'type' => 'complaint',
            'link' => '/employee/complaints',
            'is_read' => true,
        ]);

        Notification::create([
            'user_id' => $adminUser->id,
            'title' => 'Pengajuan Cuti Baru Menunggu Review',
            'message' => 'Siti Rahma mengajukan permohonan cuti sakit selama 2 hari.',
            'type' => 'leave',
            'link' => '/admin/leaves',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $adminUser->id,
            'title' => 'Pengaduan Presensi Baru Masuk',
            'message' => 'Budi Santoso mengajukan komplain terkait keterlambatan presensi.',
            'type' => 'complaint',
            'link' => '/admin/complaints',
            'is_read' => false,
        ]);

        // 13. Payroll Records (August Paid, September Draft/Pending)
        Payroll::create([
            'employee_id' => $createdEmployees['budi@absensi.com']['employee']->id,
            'month' => '2026-08',
            'basic_salary' => 8500000,
            'daily_allowance' => 1050000,
            'late_deduction' => 50000,
            'absent_deduction' => 0,
            'net_salary' => 9500000,
            'status' => 'paid',
            'payment_date' => '2026-08-31',
            'note' => 'Gaji pokok + tunjangan makan & transport Agustus 2026.',
        ]);

        Payroll::create([
            'employee_id' => $createdEmployees['siti@absensi.com']['employee']->id,
            'month' => '2026-08',
            'basic_salary' => 6500000,
            'daily_allowance' => 1100000,
            'late_deduction' => 0,
            'absent_deduction' => 0,
            'net_salary' => 7600000,
            'status' => 'paid',
            'payment_date' => '2026-08-31',
            'note' => 'Gaji pokok + tunjangan kehadiran penuh Agustus 2026.',
        ]);

        Payroll::create([
            'employee_id' => $createdEmployees['ahmad@absensi.com']['employee']->id,
            'month' => '2026-08',
            'basic_salary' => 7000000,
            'daily_allowance' => 1000000,
            'late_deduction' => 25000,
            'absent_deduction' => 0,
            'net_salary' => 7975000,
            'status' => 'paid',
            'payment_date' => '2026-08-31',
            'note' => 'Gaji pokok + tunjangan Agustus 2026.',
        ]);

        // Current month estimates (Draft)
        Payroll::create([
            'employee_id' => $createdEmployees['budi@absensi.com']['employee']->id,
            'month' => '2026-09',
            'basic_salary' => 8500000,
            'daily_allowance' => 550000,
            'late_deduction' => 25000,
            'absent_deduction' => 0,
            'net_salary' => 9025000,
            'status' => 'draft',
            'payment_date' => null,
            'note' => 'Estimasi berjalan bulan September 2026.',
        ]);

        Payroll::create([
            'employee_id' => $createdEmployees['siti@absensi.com']['employee']->id,
            'month' => '2026-09',
            'basic_salary' => 6500000,
            'daily_allowance' => 600000,
            'late_deduction' => 0,
            'absent_deduction' => 0,
            'net_salary' => 7100000,
            'status' => 'draft',
            'payment_date' => null,
            'note' => 'Estimasi berjalan bulan September 2026.',
        ]);

        // 14. Transjakarta Workforce Bulk Seeding (Total 3.520 Pegawai)
        $this->call(TransjakartaEmployeeSeeder::class);
        $this->call(PerformanceAndAppreciationSeeder::class);
    }
}
