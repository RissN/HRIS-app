<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeAppreciation;
use App\Models\EmployeeDailyScore;
use App\Models\User;
use Illuminate\Database\Seeder;

class PerformanceAndAppreciationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $adminId = $admin?->id;

        $positions = Employee::POSITIONS;
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // 1. Generate sample daily attendance scores for this month (from 1st of month to today)
        $today = now();
        $startOfMonth = now()->startOfMonth();

        // Sample viral & exemplary stories for Transjakarta
        $sampleAppreciations = [
            [
                'position' => 'Pramudi',
                'source' => 'sosmed',
                'title' => 'Viral di TikTok: Sangat Sabar & Halus Mengemudikan Bus Gandeng Saat Macet Total di Gatot Subroto',
                'description' => 'Mendapat 1,2 juta views di TikTok. Netizen memuji kesabaran dan kenyamanan pengereman bus TJ koridor 9.',
                'evidence_url' => 'https://www.tiktok.com/@transjakarta_fans/video/741294819283',
                'points' => 200,
            ],
            [
                'position' => 'Pramusapa',
                'source' => 'sosmed',
                'title' => 'Viral di X (Twitter): Sigap Menolong Penumpang Ibu Hamil yang Pingsan di Dalam Bus Koridor 1',
                'description' => 'Apresiasi dari akun @jktinfo & @infotije atas kecepatan tanggap memberikan pertolongan pertama dan mengamankan barang bawaan.',
                'evidence_url' => 'https://twitter.com/jktinfo/status/17896481029',
                'points' => 250,
            ],
            [
                'position' => 'Pramujaga',
                'source' => 'customer',
                'title' => 'Pujian Penumpang Halte Tosari: Mengembalikan Dompet Berisi Surat Penting & Uang Tunai Utuh',
                'description' => 'Laporan masuk via Call Center 1500-102. Penumpang sangat berterima kasih atas integritas dan kejujuran luar biasa.',
                'evidence_url' => null,
                'points' => 150,
            ],
            [
                'position' => 'Pramudi',
                'source' => 'service',
                'title' => 'Pelayanan Prima Rute Mikrotrans JAK-10: Selalu Ramah Menyapa Penumpang Lansia',
                'description' => 'Mendapatkan nilai sempurna pada audit mystery shopper divisi operasional PT Transportasi Jakarta.',
                'evidence_url' => null,
                'points' => 100,
            ],
            [
                'position' => 'Karyawan Kantor',
                'source' => 'extra_mile',
                'title' => 'Dedikasi Ekstra: Lembur Mengamankan Sistem Jadwal Armada Saat Banjir Musiman',
                'description' => 'Memastikan rute pengalihan Transjakarta tetap terintegrasi tanpa ada penumpukan di halte sentral.',
                'evidence_url' => null,
                'points' => 120,
            ],
            [
                'position' => 'Pramusapa',
                'source' => 'customer',
                'title' => 'Apresiasi Penumpang Koridor 13: Membantu Tunanetra Berpindah Halte CSW dengan Ramah',
                'description' => 'Pujian resmi pelanggan melalui form saran halte integrasi CSW - ASEAN.',
                'evidence_url' => null,
                'points' => 100,
            ],
        ];

        // Seed daily scores and appreciations for top candidates in each position
        foreach ($positions as $posKey => $posLabel) {
            $employees = Employee::where('position', $posKey)->take(25)->get();

            foreach ($employees as $idx => $emp) {
                // Determine candidate performance level based on index to create a natural podium
                $attendanceRate = $idx < 3 ? 1.0 : ($idx < 10 ? 0.92 : 0.85);

                $dayCursor = $startOfMonth->copy();
                while ($dayCursor->lte($today)) {
                    if (! $dayCursor->isWeekend()) {
                        $dateStr = $dayCursor->toDateString();

                        // Deterministic random
                        $rand = (crc32($emp->employee_code.$dateStr) % 100) / 100;

                        if ($rand < $attendanceRate) {
                            $status = 'present';
                            $score = 100;
                            $lateMins = 0;
                            $checkIn = '07:45:00';
                        } elseif ($rand < $attendanceRate + 0.08) {
                            $status = 'late';
                            $lateMins = 12;
                            $score = 88;
                            $checkIn = '08:12:00';
                        } else {
                            $status = 'leave';
                            $lateMins = 0;
                            $score = 80;
                            $checkIn = null;
                        }

                        EmployeeDailyScore::updateOrCreate(
                            [
                                'employee_id' => $emp->id,
                                'date' => $dateStr,
                            ],
                            [
                                'attendance_status' => $status,
                                'check_in_time' => $checkIn,
                                'late_minutes' => $lateMins,
                                'attendance_score' => $score,
                                'appreciation_score' => 0,
                                'total_score' => $score,
                            ]
                        );
                    }
                    $dayCursor->addDay();
                }
            }
        }

        // Now seed the appreciations to elevate the top performers
        foreach ($sampleAppreciations as $item) {
            $emp = Employee::where('position', $item['position'])->first();
            if ($emp) {
                $appreciation = EmployeeAppreciation::create([
                    'employee_id' => $emp->id,
                    'admin_id' => $adminId,
                    'source' => $item['source'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'evidence_url' => $item['evidence_url'],
                    'points' => $item['points'],
                    'date' => now()->subDays(rand(1, 5))->toDateString(),
                ]);

                // Update that day's score
                $dateStr = $appreciation->date->toDateString();
                $dailyScore = EmployeeDailyScore::firstOrNew([
                    'employee_id' => $emp->id,
                    'date' => $dateStr,
                ]);
                $dailyScore->attendance_score = $dailyScore->attendance_score ?: 100;
                $dailyScore->appreciation_score = ($dailyScore->appreciation_score ?? 0) + $item['points'];
                $dailyScore->total_score = $dailyScore->attendance_score + $dailyScore->appreciation_score;
                $dailyScore->save();
            }
        }
    }
}
