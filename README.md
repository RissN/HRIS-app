# HRIS - Human Resource Information System

Platform manajemen sumber daya manusia dan presensi berbasis web dengan arsitektur SPA (Single Page Application) yang dibangun menggunakan Laravel 11, Inertia.js (Vue 3), dan Tailwind CSS.

---

## Fitur Utama

### 1. Portal Pegawai
- Presensi Geofencing GPS: Jam digital sinkron server, validasi koordinat GPS radius kantor dengan formula Haversine, kamera selfie kehadiran, dan opsi status (Hadir, WFH, Izin, Sakit).
- Saldo & Kuota Cuti Tahunan: Kuota standar 12 hari kerja per tahun, kartu saldo cuti (Hak Kuota, Terpakai, Menunggu Review, Sisa Kuota), validasi pencegahan pengajuan melebihi kuota, dan upload berkas pendukung.
- Riwayat Kehadiran: Rekap bulanan, jam kerja, dan KPI presensi.
- Komplain Presensi: Pelaporan kendala presensi dengan bukti screenshot.
- Slip Gaji: Pratinjau dan cetak slip gaji bulanan mandiri.
- Profil & Notifikasi: Pembaruan data pribadi, rekening bank, serta lonceng notifikasi real-time.

### 2. Portal Admin HR
- Dashboard Monitoring: Statistik harian kehadiran pegawai dan daftar permohonan yang menunggu tindak lanjut.
- Manajemen Pegawai & Shift: Pengelolaan data staf, penugasan multi-shift kerja, status akun, dan kuota cuti kustom.
- Pengaturan Kantor & Geofencing: Peta interaktif Leaflet & OpenStreetMap, konfigurasi radius presensi, serta tarif tunjangan dan denda.
- Verifikasi Cuti & Komplain: Tinjauan permohonan staf dengan info sisa cuti pegawai, sinkronisasi otomatis status presensi, dan dialog konfirmasi modern.
- Estimator Payroll: Perhitungan gaji bersih otomatis berbasis kehadiran, tunjangan, dan denda, serta cetak slip gaji resmi.
- Rekapitulasi Laporan: Filter multi-parameter, ringkasan KPI kehadiran, cetak laporan, dan ekspor CSV aman (proteksi formula injection).
- Pengumuman & Hari Libur: Publikasi pengumuman kantor dan kalender hari libur operasional.

---

## Teknologi yang Digunakan

- Backend: Laravel 11, PHP 8.3
- Frontend: Vue 3 (Composition API, `<script setup>`), Inertia.js v2
- Styling: Tailwind CSS, Bootstrap Icons
- Peta: Leaflet.js, OpenStreetMap
- Database: MySQL 8.x
- Otorisasi: Spatie Laravel Permission
- Build Tool: Vite
- Container: Docker & Docker Compose

---

## Cara Menjalankan

### Opsi 1: Menggunakan Docker (Direkomendasikan)

1. Salin file konfigurasi environment:
```bash
cp .env.docker.example .env
```

2. Jalankan container:
```bash
docker compose up -d --build
```

3. Jalankan migrasi dan seeding data:
```bash
docker compose exec app php artisan migrate:fresh --seed
```

- Aplikasi: `http://localhost:8000`
- phpMyAdmin: `http://localhost:8080`
- Panduan Docker lebih lanjut: [DOCKER.md](DOCKER.md)

### Opsi 2: Instalasi Manual (Localhost)

Pastikan telah terpasang PHP >= 8.2, Composer, Node.js >= 18, dan MySQL.

1. Pasang dependensi:
```bash
composer install
npm install
```

2. Konfigurasi environment:
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan konfigurasi database pada file `.env`.

3. Setup database dan storage:
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

4. Kompilasi aset:
```bash
npm run build
```
Untuk mode pengembangan: `npm run dev`

5. Jalankan server:
```bash
php artisan serve
```
Akses aplikasi di `http://localhost:8000`.

---

## Akun Demo Pengujian

| Peran | Email | Kata Sandi | Keterangan |
| :--- | :--- | :--- | :--- |
| Admin HR | `admin@absensi.com` | `password` | Akses penuh dashboard, pengaturan, laporan, dan verifikasi. |
| Pegawai 1 | `budi@absensi.com` | `password` | Presensi GPS/selfie, saldo cuti, dan slip gaji. |
| Pegawai 2 | `siti@absensi.com` | `password` | Akun staf operasional untuk pengujian notifikasi dan shift. |

---

## Keamanan Sistem

- Middleware penonaktifan akun real-time (`EnsureUserIsActive`).
- Security Response Headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`).
- Rate limiting pada endpoint mutasi data pegawai (`10 requests/menit`).
- Sanitasi CSV dari potensi formula injection saat ekspor data.
- Enkripsi session cookie dan nomor rekening payroll.
- Validasi anti-spoofing GPS dan sanitasi format berkas upload.

---

## Lisensi

Aplikasi ini dirilis di bawah lisensi terbuka [MIT License](LICENSE).
