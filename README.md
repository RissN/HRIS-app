# HRIS — Human Resource Information System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="220" alt="Laravel Logo" />
</p>

**HRIS (Human Resource Information System)** adalah platform manajemen sumber daya manusia dan presensi modern berbasis web dengan arsitektur SPA (*Single Page Application*) yang dibangun menggunakan **Laravel 11**, **Inertia.js (Vue 3)**, dan **Tailwind CSS**.

Aplikasi dirancang dengan pendekatan *mobile-friendly* dan estetika minimalis modern (*clean white base*, *royal blue accent*, dan *slate typography*), memberikan kemudahan pencatatan kehadiran mandiri bagi pegawai serta kendali komprehensif bagi tim HRD dalam mengelola data pegawai, jadwal shift, geofencing kantor, rekapitulasi laporan, notifikasi, hingga kalkulasi payroll otomatis.

---

## 🚀 Fitur Unggulan Sistem

### 1. Portal Pegawai (Mobile & Desktop)
- **Presensi GPS & Geofencing Real-time**:
  - Jam digital presisi sinkron waktu server.
  - Pengecekan jarak koordinat GPS instan dengan formula *Haversine* terhadap radius kantor resmi.
  - Indikator status jarak visual ("Di Dalam Radius" vs "Di Luar Radius") sebelum check-in.
  - Dukungan kamera selfie perangkat untuk verifikasi kehadiran fisik.
  - Opsi status kehadiran: **Hadir**, **WFH (Work From Home)**, **Izin**, dan **Sakit**.
- **Riwayat Presensi Interaktif**:
  - Rekap bulanan, kartu KPI (*Total Hadir, Terlambat, Izin/Sakit, Alpa*), dan detail jam kerja.
  - Akses pengajuan komplain langsung dari baris riwayat.
- **Pengajuan Cuti / Izin / Sakit**:
  - Formulir cuti dengan penghitungan otomatis hari kerja efektif (otomatis mengecualikan hari libur akhir pekan).
  - Unggah berkas pendukung (surat dokter / formulir tugas) format PDF/JPG/PNG.
  - Pelacakan status verifikasi HR (*Pending, Approved, Rejected*) dengan fitur pembatalan mandiri.
- **Komplain Koreksi Presensi**:
  - Laporkan kendala jam salah, gangguan sinyal GPS, lupa check-out pulang, atau kendala teknis.
  - Unggah bukti tangkapan layar dan pantau respon perbaikan dari HR.
- **Slip Gaji Pegawai (Payroll)**:
  - Pratinjau take-home pay bulanan, tunjangan kehadiran, dan potongan presensi.
  - Tombol cetak slip gaji pribadi siap cetak.
- **Profil Mandiri & Notifikasi**:
  - Pengelolaan data kontak, foto profil avatar, dan rekening bank.
  - Lonceng notifikasi interaktif real-time dengan counter unread.

---

### 2. Portal Manajemen HR & Administrator
- **Dashboard & Analitik Presensi**:
  - Ringkasan harian pegawai hadir, terlambat, izin/WFH, dan tidak hadir.
  - Quick badge indikator pengajuan cuti dan komplain yang butuh respon segera.
- **Rekapitulasi & Ekspor Laporan Presensi**:
  - Filter rentang bulan, departemen, status kehadiran, dan pencarian NIK/Nama.
  - 6 Kartu KPI statistik kehadiran bulanan.
  - Tombol **Unduh CSV / Excel** (berstandar UTF-8 BOM agar rapi di Microsoft Excel) dan tombol **Cetak Laporan / PDF**.
- **Pengaturan Lokasi Kantor & Geofencing GPS**:
  - **Peta Interaktif Leaflet & OpenStreetMap**: Geser pin marker atau klik peta untuk menentukan titik koordinat kantor secara akurat.
  - **Lingkaran Radius Geofence**: Visual lingkaran radius meter yang responsif terhadap input toleransi jarak.
  - Tombol "Gunakan GPS Saya" untuk mengisi koordinat dari lokasi fisik admin.
  - Konfigurasi tarif tunjangan makan/transport harian, denda keterlambatan, dan denda alpa.
- **Papan Pengumuman Perusahaan**:
  - Terbitkan pengumuman internal dengan badge kategori warna (*Informasi, Agenda, Peringatan, Mendesak*).
  - Pengumuman aktif langsung muncul pada beranda presensi seluruh pegawai.
- **Kalender Hari Libur Nasional & Cuti Bersama**:
  - Manajemen hari libur resmi untuk mencegah sistem menandai alpa keliru pada hari libur operasional.
  - Indikator otomatis hari libur terdekat pada dashboard pegawai.
- **Estimator Penggajian (Payroll)**:
  - Kalkulasi payroll bulanan otomatis satu-klik untuk seluruh pegawai aktif berdasarkan:
    $$\text{Gaji Bersih} = \text{Gaji Pokok} + (\text{Hari Hadir} \times \text{Tunjangan}) - (\text{Hari Terlambat} \times \text{Denda}) - (\text{Hari Alpa} \times \text{Denda})$$
  - Modal pratinjau dan cetak slip gaji pegawai resmi.
  - Aksi "Tandai Lunas" yang otomatis mengirim notifikasi penerbitan slip ke akun pegawai.
- **Manajemen Pegawai & Shift Kerja**:
  - Pengelolaan master data pegawai, departemen, jabatan, dan nomor rekening payroll terenkripsi.
  - Konfigurasi multi-shift kerja (jam mulai, jam pulang, batas toleransi menit, dan hari kerja aktif).
- **Verifikasi Cuti & Komplain**:
  - Jendela modal dialog terpusat (*centered modal*) dengan backdrop blur halus.
  - Persetujuan cuti otomatis menyinkronkan data absensi harian pada rentang tanggal terkait.
  - Penyelesaian komplain dengan opsi koreksi otomatis jam masuk, pulang, dan status presensi.

---

## 🛠️ Tumpukan Teknologi (Tech Stack)

| Komponen | Teknologi |
| :--- | :--- |
| **Backend Framework** | [Laravel 11](https://laravel.com/) (PHP 8.3) |
| **Client-Server Bridge** | [Inertia.js v2](https://inertiajs.com/) |
| **Frontend Library** | [Vue 3](https://vuejs.org/) (Composition API, `<script setup>`) |
| **Styling & CSS** | [Tailwind CSS](https://tailwindcss.com/) & Bootstrap Icons |
| **Peta & Geofencing** | [Leaflet.js](https://leafletjs.com/) & OpenStreetMap |
| **Database** | MySQL 8.x |
| **Role & Permission** | [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) |
| **Asset Bundler** | [Vite](https://vitejs.dev/) |

---

## 📦 Panduan Instalasi & Menjalankan Aplikasi

Pastikan sistem Anda telah terpasang **PHP >= 8.2**, **Composer**, **Node.js >= 18**, dan **MySQL**.

### 1. Salin Repositori
```bash
git clone https://github.com/RissN/Absensi-app.git
cd Absensi-app
```

### 2. Pasang Dependensi PHP & JavaScript
```bash
composer install
npm install
```

### 3. Salin Konfigurasi Lingkungan (.env)
```bash
cp .env.example .env
```
Sesuaikan konfigurasi database Anda pada file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_app
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Buat Kunci Aplikasi & Setup Database
```bash
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
```

### 5. Kompilasi Aset Frontend
```bash
npm run build
```
*(Untuk mode pengembangan dengan hot-reloading: `npm run dev`)*

### 6. Jalankan Server Aplikasi
```bash
php artisan serve
```
Akses aplikasi melalui browser di tautan: **`http://localhost:8000`**

---

## 🔑 Akun Demo & Kredensial Pengujian

Pada halaman masuk (`/login`), tersedia opsi pengisian cepat demo akun untuk mempermudah evaluasi:

| Peran | Email | Kata Sandi | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Admin HR** | `admin@absensi.com` | `password` | Akses penuh dashboard, rekap laporan, geofencing kantor, pengumuman, hari libur, payroll, dan persetujuan cuti/komplain. |
| **Pegawai (Demo 1)** | `budi@absensi.com` | `password` | Presensi selfie & GPS, riwayat kehadiran, pengajuan cuti, komplain absensi, dan cetak slip gaji. |
| **Pegawai (Demo 2)** | `siti@absensi.com` | `password` | Pengujian interaksi notifikasi dan status shift kerja. |

---

## 🛡️ Keamanan & Kualitas Kode

- **Perlindungan Berkas Rahasia**: Konfigurasi [.gitignore](file:///c:/laragon/www/absensi-app/.gitignore) telah dikonfigurasi untuk mencegah kebocoran file kredensial `.env`, kunci privat, cache, sesi, dan berkas unggahan pengguna.
- **Validasi Kustom**: Seluruh formulir menggunakan validasi kustom sisi klien dan server tanpa popup kaku bawaan browser (`novalidate`).
- **Standardisasi Kode**: Diformat mengikuti standar PSR-12 menggunakan **Laravel Pint**.
- **Unit Testing**: Pengujian formula matematika jarak geofencing Haversine dan formula kalkulasi payroll melalui PHPUnit.

---

## 📄 Lisensi
Aplikasi ini dirilis di bawah lisensi terbuka [MIT License](LICENSE).
