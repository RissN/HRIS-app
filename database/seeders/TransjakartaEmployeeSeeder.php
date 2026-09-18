<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TransjakartaEmployeeSeeder extends Seeder
{
    /**
     * Seed 3,520 Transjakarta Employees:
     * - 1,300 Karyawan Tetap
     * - 2,100 Vendor
     * - 120 Karyawan Magang
     *
     * Across 4 Regions:
     * - Jakarta Timur
     * - Jakarta Barat
     * - Jakarta Pusat
     * - Jakarta Utara
     *
     * Positions:
     * - Pramudi (Driver)
     * - Pramusapa (Layanan Bus/Halte)
     * - Pramujaga (Keamanan Halte/Jalur)
     * - Karyawan Kantor (Operasional, Dispatcher, IT, GA)
     */
    public function run(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);
        $passwordHash = Hash::make('password');

        // Existing schedule IDs
        $schedules = WorkSchedule::pluck('id')->toArray();
        if (empty($schedules)) {
            $reg = WorkSchedule::create([
                'name' => 'Shift Reguler (08:00 - 17:00)',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'tolerance_minutes' => 15,
            ]);
            $schedules = [$reg->id];
        }

        $regionConfigs = [
            'jakarta_timur' => [
                'name' => 'Jakarta Timur',
                'tetap' => 309, // + Budi (1 tetap) = 310
                'vendor' => 519, // + Reza (1 vendor) = 520
                'magang' => 25,
                'pools' => ['Pool Cawang', 'Pool Pinang Ranti', 'Pool Klender', 'Terminal Kampung Rambutan'],
                'center_lat' => -6.2250,
                'center_lng' => 106.9004,
            ],
            'jakarta_barat' => [
                'name' => 'Jakarta Barat',
                'tetap' => 269, // + Siti (1 tetap) = 270
                'vendor' => 440,
                'magang' => 25,
                'pools' => ['Pool Rawa Buaya', 'Pool Pesing', 'Terminal Kalideres'],
                'center_lat' => -6.1683,
                'center_lng' => 106.7589,
            ],
            'jakarta_selatan' => [
                'name' => 'Jakarta Selatan',
                'tetap' => 250,
                'vendor' => 420,
                'magang' => 25,
                'pools' => ['Pool Cipedak', 'Pool Lebak Bulus', 'Terminal Blok M', 'Halte CSW / ASEAN'],
                'center_lat' => -6.2615,
                'center_lng' => 106.8106,
            ],
            'jakarta_pusat' => [
                'name' => 'Jakarta Pusat',
                'tetap' => 238, // + Admin (1 tetap) + Ahmad (1 tetap) = 240
                'vendor' => 380,
                'magang' => 25,
                'pools' => ['Kantor Pusat Cawang / Koridor 1', 'Halte Sentral Harmoni', 'Depo Monas'],
                'center_lat' => -6.1805,
                'center_lng' => 106.8284,
            ],
            'jakarta_utara' => [
                'name' => 'Jakarta Utara',
                'tetap' => 230,
                'vendor' => 339, // + Dewi (1 vendor) = 340
                'magang' => 20,
                'pools' => ['Pool Pegangsaan Dua', 'Pool Tanjung Priok', 'Halte Sentral Pluit'],
                'center_lat' => -6.1384,
                'center_lng' => 106.8640,
            ],
        ];

        $maleFirst = [
            'Ahmad', 'Budi', 'Agus', 'Joko', 'Bambang', 'Hendra', 'Fajar', 'Eko', 'Wahyu', 'Bayu',
            'Rizky', 'Arif', 'Aditya', 'Doni', 'Ilham', 'Gilang', 'Surya', 'Dimas', 'Rian', 'Teguh',
            'Yoga', 'Dedi', 'Faisal', 'Angga', 'Prasetyo', 'Heru', 'Faris', 'Hadi', 'Tri', 'Danang',
            'Yudi', 'Taufik', 'Iwan', 'Rahmat', 'Firman', 'Bagus', 'Hasan', 'Lukman', 'Rudi', 'Gunawan',
            'Zainal', 'Farhan', 'Rangga', 'Alif', 'Wildan', 'Syahrul', 'Ridwan', 'Panji', 'Pandu', 'Guntur',
            'Gading', 'Cahyo', 'Asep', 'Dadang', 'Cecep', 'Maman', 'Ade', 'Ujang', 'Irfan', 'Anwar',
            'Imam', 'Iqbal', 'Wahid', 'Sholeh', 'Zulham', 'Dwi', 'Kurnia', 'Sulistyo', 'Maulana', 'Ridho',
            'Satria', 'Bagas', 'Rendra', 'Yusuf', 'Indra', 'Reza', 'Sigit', 'Wawan', 'Candra', 'Galih',
        ];

        $maleMiddle = [
            'Agung', 'Bagus', 'Cahya', 'Dwi', 'Eka', 'Fajar', 'Giri', 'Hadi', 'Indra', 'Jaya',
            'Kusuma', 'Laksana', 'Mulya', 'Nugraha', 'Purnama', 'Putra', 'Raden', 'Satria', 'Tri', 'Utama',
            'Wira', 'Yuda', 'Bhakti', 'Chandra', 'Dharma', 'Firmansyah', 'Gemilang', 'Hakim', 'Iskandar', 'Kurnia',
            'Mahendra', 'Nasrullah', 'Pratama', 'Ramadhan', 'Saputra', 'Taufiq', 'Wardhana', 'Yusuf', 'Alamsyah', 'Budiman',
            'Cahyadi', 'Darmawan', 'Effendi', 'Gunawan', 'Hamzah', 'Ilyas', 'Kuncoro', 'Lesmana', 'Mardiansyah', 'Novian',
        ];

        $femaleFirst = [
            'Siti', 'Dewi', 'Rina', 'Sri', 'Ratna', 'Nur', 'Indah', 'Dian', 'Putri', 'Maya',
            'Mega', 'Tari', 'Fitri', 'Wulan', 'Gita', 'Anisa', 'Nadia', 'Lestari', 'Ayu', 'Rani',
            'Yuni', 'Ratih', 'Siska', 'Nita', 'Sari', 'Nurul', 'Novi', 'Rahayu', 'Kartika', 'Widiastuti',
            'Amalia', 'Safitri', 'Latifah', 'Marlina', 'Endang', 'Retno', 'Triana', 'Desi', 'Ika', 'Anggraini',
            'Yuliana', 'Astuti', 'Puspa', 'Susanti', 'Melati', 'Kusumawati', 'Sulastri', 'Suci', 'Hana', 'Zahra',
        ];

        $femaleMiddle = [
            'Aulia', 'Bella', 'Citra', 'Dian', 'Elvira', 'Febriani', 'Gisela', 'Hapsari', 'Intan', 'Juwita',
            'Kharisma', 'Larasati', 'Melati', 'Nirmala', 'Oktavia', 'Puspa', 'Qonita', 'Rizkia', 'Suci', 'Tantri',
            'Wulandari', 'Anggraeni', 'Permatasari', 'Dewi', 'Lestari', 'Putri', 'Rahmawati', 'Kusuma', 'Maharani', 'Pratiwi',
        ];

        $lastNames = [
            'Santoso', 'Rahmawati', 'Kusuma', 'Pratama', 'Hidayat', 'Saputra', 'Wibowo', 'Utami', 'Nugroho', 'Wijaya',
            'Setiawan', 'Permana', 'Lestari', 'Siregar', 'Purnomo', 'Nasution', 'Kurniawan', 'Firmansyah', 'Suryono', 'Ramadhan',
            'Gunawan', 'Supriadi', 'Subekti', 'Handayani', 'Widodo', 'Hartono', 'Pangestu', 'Suharto', 'Pramono', 'Mustofa',
            'Simanjuntak', 'Hutapea', 'Sitorus', 'Panjaitan', 'Sinaga', 'Pasaribu', 'Lubis', 'Harahap', 'Batubara', 'Daulay',
            'Tanjung', 'Chaniago', 'Piliang', 'Koto', 'Sikumbang', 'Ginting', 'Tarigan', 'Sembiring', 'Karo-Karo', 'Perangin-Angin',
            'Sujatmiko', 'Sudirman', 'Mangkualam', 'Kertanegara', 'Prabowo', 'Hardjono', 'Sumantri', 'Suhendra', 'Daniswara',
            'Mulyono', 'Subagyo', 'Purwanto', 'Haryanto', 'Kusnadi', 'Budiman', 'Iskandar', 'Wicaksono', 'Pambudi', 'Wardoyo',
            'Sastrowardoyo', 'Baskoro', 'Baskara', 'Prakoso', 'Maheswara', 'Dananjaya', 'Ardiansyah', 'Budiyanto', 'Hermawan',
            'Al-Fatih', 'Al-Ghifari', 'Al-Farizi', 'Al-Banjari', 'Al-Habsyi', 'Assegaf', 'Al-Idrus', 'Al-Attas', 'Al-Kaff',
            'Sihombing', 'Manurung', 'Situmorang', 'Nainggolan', 'Simatupang', 'Sitompul', 'Marpaung', 'Nababan', 'Tambunan', 'Siagian',
            'Subianto', 'Wahyudi', 'Priambodo', 'Sulaiman', 'Zulkarnaen', 'Syahputra', 'Hasibuan', 'Pohan', 'Matondang',
        ];

        $usedNames = [];
        foreach (DB::table('users')->pluck('name')->toArray() as $existingName) {
            $usedNames[$existingName] = true;
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $userBatch = [];
        $employeeBatch = [];
        $scheduleBatch = [];
        $attendanceBatch = [];
        $roleBatch = [];

        $totalInserted = 0;
        $globalCounter = 1;
        $tetapCounter = 1004;
        $vendorCounter = 2003;
        $magangCounter = 1101;

        // Clean previous bulk employees (user_id > 6) if re-seeding
        $bulkUserIds = DB::table('users')->where('role', 'pegawai')->where('id', '>', 6)->pluck('id')->toArray();
        if (! empty($bulkUserIds)) {
            $bulkEmpIds = DB::table('employees')->whereIn('user_id', $bulkUserIds)->pluck('id')->toArray();
            DB::table('attendances')->whereIn('employee_id', $bulkEmpIds)->delete();
            DB::table('employee_daily_scores')->whereIn('employee_id', $bulkEmpIds)->delete();
            DB::table('employee_appreciations')->whereIn('employee_id', $bulkEmpIds)->delete();
            DB::table('employee_schedules')->whereIn('employee_id', $bulkEmpIds)->delete();
            DB::table('employees')->whereIn('id', $bulkEmpIds)->delete();
            DB::table('model_has_roles')->whereIn('model_id', $bulkUserIds)->where('model_type', 'App\Models\User')->delete();
            DB::table('users')->whereIn('id', $bulkUserIds)->delete();
        }

        $this->command->info('Memulai seeding 3.520 Karyawan Transjakarta dengan nama asli...');
        $startId = 7;

        foreach ($regionConfigs as $regionKey => $cfg) {
            $pools = $cfg['pools'];

            // Prepare list of (status, position) for this region
            // 1. Tetap
            $tetapItems = [];
            for ($i = 0; $i < $cfg['tetap']; $i++) {
                // ~45% Pramudi, ~30% Pramusapa, ~12% Pramujaga, ~13% Kantor
                $pIndex = $i % 100;
                if ($pIndex < 45) {
                    $pos = 'Pramudi';
                    $dept = 'Operasional Bus';
                } elseif ($pIndex < 75) {
                    $pos = 'Pramusapa';
                    $dept = 'Layanan Pelanggan';
                } elseif ($pIndex < 87) {
                    $pos = 'Pramujaga';
                    $dept = 'Pengamanan & Jalur';
                } else {
                    $pos = 'Karyawan Kantor';
                    $dept = 'Manajemen & Pool';
                }
                $tetapItems[] = ['status' => 'tetap', 'position' => $pos, 'department' => $dept];
            }

            // 2. Vendor
            $vendorItems = [];
            for ($i = 0; $i < $cfg['vendor']; $i++) {
                // ~48% Pramudi, ~32% Pramusapa, ~20% Pramujaga
                $pIndex = $i % 100;
                if ($pIndex < 48) {
                    $pos = 'Pramudi';
                    $dept = 'Mitra Operator Bus';
                } elseif ($pIndex < 80) {
                    $pos = 'Pramusapa';
                    $dept = 'Layanan Bus Vendor';
                } else {
                    $pos = 'Pramujaga';
                    $dept = 'Pengamanan Vendor';
                }
                $vendorItems[] = ['status' => 'vendor', 'position' => $pos, 'department' => $dept];
            }

            // 3. Magang
            $magangItems = [];
            for ($i = 0; $i < $cfg['magang']; $i++) {
                $magangItems[] = [
                    'status' => 'magang',
                    'position' => 'Karyawan Kantor',
                    'department' => 'Operasional & Layanan Magang',
                ];
            }

            $allItems = array_merge($tetapItems, $vendorItems, $magangItems);
            shuffle($allItems);

            foreach ($allItems as $item) {
                $isMale = ($globalCounter % 10 < 7);
                $fList = $isMale ? $maleFirst : $femaleFirst;
                $mList = $isMale ? $maleMiddle : $femaleMiddle;

                $fIdx = ($globalCounter + crc32($regionKey)) % count($fList);
                $mIdx = ($globalCounter * 7 + (int) ($globalCounter / count($fList))) % count($mList);
                $lIdx = ($globalCounter * 13 + (int) ($globalCounter / count($mList))) % count($lastNames);

                if ($globalCounter % 3 === 0) {
                    $name = $fList[$fIdx].' '.$lastNames[$lIdx];
                } else {
                    $name = $fList[$fIdx].' '.$mList[$mIdx].' '.$lastNames[$lIdx];
                }

                $collisionOffset = 1;
                while (isset($usedNames[$name])) {
                    $mIdx = ($mIdx + $collisionOffset) % count($mList);
                    $lIdx = ($lIdx + $collisionOffset) % count($lastNames);
                    $name = $fList[$fIdx].' '.$mList[$mIdx].' '.$lastNames[$lIdx];
                    $collisionOffset++;
                }
                $usedNames[$name] = true;

                $email = 'tj.'.strtolower($regionKey).'.'.$globalCounter.'@transjakarta.co.id';
                $phone = '08'.str_pad((string) (100000000 + $globalCounter), 10, '0', STR_PAD_LEFT);
                $pool = $pools[$globalCounter % count($pools)];
                $scheduleId = $schedules[$globalCounter % count($schedules)];

                if ($item['status'] === 'magang') {
                    $employeeCode = 'TJB'.($magangCounter++);
                } elseif ($item['status'] === 'vendor') {
                    $employeeCode = 'TJV'.($vendorCounter++);
                } else {
                    $employeeCode = 'TJT'.($tetapCounter++);
                }

                $currentUserId = $startId++;

                $userBatch[] = [
                    'id' => $currentUserId,
                    'name' => $name,
                    'email' => $email,
                    'password' => $passwordHash,
                    'role' => 'pegawai',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $roleBatch[] = [
                    'role_id' => $pegawaiRole->id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $currentUserId,
                ];

                $employeeBatch[] = [
                    'user_id' => $currentUserId,
                    'employee_code' => $employeeCode,
                    'phone' => $phone,
                    'position' => $item['position'],
                    'department' => $item['department'],
                    'region' => $regionKey,
                    'employment_status' => $item['status'],
                    'pool_depot' => $pool,
                    'annual_leave_quota' => $item['status'] === 'magang' ? 0 : 12,
                    'bank_name' => 'Bank DKI',
                    'account_number' => '10'.str_pad((string) $globalCounter, 8, '0', STR_PAD_LEFT),
                    'joined_date' => Carbon::now()->subMonths(($globalCounter % 24) + 1)->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $scheduleBatch[] = [
                    'user_id' => $currentUserId, // we'll map employee_id after insert
                    'schedule_id' => $scheduleId,
                    'effective_date' => Carbon::now()->subMonths(6)->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Attendance randomizer for today
                // ~82% Present, ~8% Late, ~4% Leave/Sick, ~6% Not Checked In
                $attRand = $globalCounter % 100;
                if ($attRand < 94) {
                    $status = 'present';
                    $checkInHour = '06:';
                    $checkInMin = str_pad((string) ($attRand % 55), 2, '0', STR_PAD_LEFT);

                    if ($attRand >= 82 && $attRand < 90) {
                        $status = 'late';
                        $checkInHour = '07:';
                        $checkInMin = str_pad((string) (20 + ($attRand % 35)), 2, '0', STR_PAD_LEFT);
                    } elseif ($attRand >= 90 && $attRand < 92) {
                        $status = 'sick';
                    } elseif ($attRand >= 92 && $attRand < 94) {
                        $status = 'permission';
                    }

                    $checkInAt = in_array($status, ['sick', 'permission']) ? null : ($today.' '.$checkInHour.$checkInMin.':00');
                    $checkOutAt = null;

                    $attendanceBatch[] = [
                        'user_id' => $currentUserId,
                        'date' => $today,
                        'check_in_at' => $checkInAt,
                        'check_out_at' => $checkOutAt,
                        'check_in_lat' => $cfg['center_lat'] + (mt_rand(-50, 50) / 10000),
                        'check_in_lng' => $cfg['center_lng'] + (mt_rand(-50, 50) / 10000),
                        'status' => $status,
                        'note' => $status === 'late' ? 'Trafik jalur padat' : ($status === 'sick' ? 'Izin sakit operasional' : null),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $globalCounter++;
                $totalInserted++;

                // Chunk insert every 500 records
                if (count($userBatch) >= 500) {
                    $this->flushBatches($userBatch, $roleBatch, $employeeBatch, $scheduleBatch, $attendanceBatch);
                }
            }
        }

        // Flush remaining records
        if (! empty($userBatch)) {
            $this->flushBatches($userBatch, $roleBatch, $employeeBatch, $scheduleBatch, $attendanceBatch);
        }

        $this->command->info("Selesai! Total {$totalInserted} Karyawan Transjakarta berhasil dimasukkan.");
    }

    private function flushBatches(
        array &$userBatch,
        array &$roleBatch,
        array &$employeeBatch,
        array &$scheduleBatch,
        array &$attendanceBatch
    ): void {
        DB::transaction(function () use (
            &$userBatch,
            &$roleBatch,
            &$employeeBatch,
            &$scheduleBatch,
            &$attendanceBatch
        ) {
            // 1. Insert Users
            DB::table('users')->insert($userBatch);

            // 2. Insert Roles
            DB::table('model_has_roles')->insert($roleBatch);

            // 3. Insert Employees
            DB::table('employees')->insert($employeeBatch);

            // 4. Map user_id to employee_id
            $userIds = array_column($userBatch, 'id');
            $userEmpMap = DB::table('employees')
                ->whereIn('user_id', $userIds)
                ->pluck('id', 'user_id')
                ->toArray();

            // 5. Insert Employee Schedules
            $formattedSchedules = [];
            foreach ($scheduleBatch as $s) {
                if (isset($userEmpMap[$s['user_id']])) {
                    $formattedSchedules[] = [
                        'employee_id' => $userEmpMap[$s['user_id']],
                        'schedule_id' => $s['schedule_id'],
                        'effective_date' => $s['effective_date'],
                        'created_at' => $s['created_at'],
                        'updated_at' => $s['updated_at'],
                    ];
                }
            }
            if (! empty($formattedSchedules)) {
                DB::table('employee_schedules')->insert($formattedSchedules);
            }

            // 6. Insert Attendances
            $formattedAttendances = [];
            foreach ($attendanceBatch as $a) {
                if (isset($userEmpMap[$a['user_id']])) {
                    $formattedAttendances[] = [
                        'employee_id' => $userEmpMap[$a['user_id']],
                        'date' => $a['date'],
                        'check_in_at' => $a['check_in_at'],
                        'check_out_at' => $a['check_out_at'],
                        'check_in_lat' => $a['check_in_lat'],
                        'check_in_lng' => $a['check_in_lng'],
                        'status' => $a['status'],
                        'note' => $a['note'],
                        'created_at' => $a['created_at'],
                        'updated_at' => $a['updated_at'],
                    ];
                }
            }
            if (! empty($formattedAttendances)) {
                DB::table('attendances')->insert($formattedAttendances);
            }
        });

        $userBatch = [];
        $roleBatch = [];
        $employeeBatch = [];
        $scheduleBatch = [];
        $attendanceBatch = [];
    }
}
