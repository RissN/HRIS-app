# Panduan Menjalankan HRIS dengan Docker 🐳

Dokumen ini berisi panduan lengkap untuk menjalankan aplikasi **HRIS (Laravel 13 + Vue 3 Inertia + MySQL + Nginx)** di dalam Docker menggunakan Docker Compose.

---

## 1. Arsitektur Layanan Docker

| Layanan | Container Name | Image Base | Port Host | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| **`app`** | `hris-app` | PHP 8.3 FPM + Node 22 + Composer | Internal (9000) | Menjalankan backend Laravel, worker, dan utilitas development |
| **`web`** | `hris-web` | Nginx Alpine | `8000` | Web server reverse proxy yang melayani aplikasi web |
| **`db`** | `hris-db` | MySQL 8.0 | `3306` | Database server dengan volume persisten `dbdata` |
| **`phpmyadmin`** | `hris-phpmyadmin` | phpMyAdmin Latest | `8080` | GUI Web untuk mengelola database MySQL |

---

## 2. Prasyarat

Pastikan komputer Anda sudah terpasang:
- **Docker Desktop** (dengan WSL 2 backend aktif jika di Windows).
- Pastikan Docker daemon sudah aktif (ditandai ikon Docker berjalan di system tray).

---

## 3. Langkah Menjalankan Aplikasi

### Langkah 1: Persiapkan File Environment (.env)

Jika Anda ingin menjalankan proyek di Docker secara terpisah dari Laragon lokal:
```bash
# Salin konfigurasi lingkungan Docker
cp .env.docker.example .env
```
> **Catatan Pengguna Windows (Laragon Aktif):**
> Jika Anda memiliki MySQL lokal dari Laragon yang sedang aktif di port `3306`, Anda bisa mengubah port MySQL Docker agar tidak bertabrakan dengan menyetel di `.env`:
> ```env
> FORWARD_DB_PORT=3307
> ```

### Langkah 2: Bangun & Jalankan Seluruh Container

Jalankan perintah berikut pada terminal di folder proyek:
```bash
docker compose up -d --build
```
> Flag `-d` (*detached*) membuat container berjalan di latar belakang, dan `--build` memastikan image PHP dan Nginx dibangun sesuai konfigurasi terbaru.

### Langkah 3: Setup Database & Dummy Data

Setelah container berjalan (tunggu beberapa detik hingga status database `healthy`):
```bash
# Jalankan migrasi dan seeder data lengkap (akun HR, pegawai, jadwal shift, dan absensi)
docker compose exec app php artisan migrate:fresh --seed
```

### Langkah 4: Kompilasi Aset Frontend

Asset frontend (Vite) dapat dikompilasi langsung di dalam container:
```bash
# Untuk build produksi
docker compose exec app npm run build

# ATAU untuk mode development dengan hot reload
docker compose exec app npm run dev
```

---

## 4. Mengakses Aplikasi

Buka browser Anda dan akses tautan berikut:

- 🌐 **Portal Aplikasi HRIS**: [http://localhost:8000](http://localhost:8000)
- 🗄️ **phpMyAdmin GUI**: [http://localhost:8080](http://localhost:8080)
  - **Server**: `db`
  - **Username**: `root`
  - **Password**: `root` (atau sesuai konfigurasi di `.env`)

---

## 5. Kredensial Akun Bawaan (Hasil Seeder)

### Akun Administrator / HR Manager
- **Email**: `admin@absensi.com`
- **Password**: `password`
- **Hak Akses**: Dashboard analitik, verifikasi cuti & komplain, pengaturan kantor/geofencing GPS, pengumuman, dan slip gaji.

### Akun Pegawai (Contoh)
- **Senior Software Engineer**: `budi@absensi.com` / `password`
- **Marketing Specialist**: `siti@absensi.com` / `password`
- **Finance Analyst**: `ahmad@absensi.com` / `password`
- **Customer Support**: `dewi@absensi.com` / `password`
- **Field Ops Officer**: `reza@absensi.com` / `password`

---

## 6. Perintah Perawatan & Utilitas

### Melihat Status & Log Container
```bash
# Cek status container yang sedang berjalan
docker compose ps

# Melihat log aplikasi secara real-time
docker compose logs -f app

# Melihat log web server Nginx
docker compose logs -f web

# Melihat log database MySQL
docker compose logs -f db
```

### Menjalankan Perintah Artisan
Anda tidak perlu install PHP di host jika menggunakan Docker:
```bash
# Contoh menjalankan artisan
docker compose exec app php artisan route:list
docker compose exec app php artisan cache:clear
```

### Mengakses Bash / Shell di Dalam Container
```bash
docker compose exec app bash
```

### Menghentikan Container
```bash
# Menghentikan seluruh layanan tanpa menghapus data database
docker compose stop

# Menghentikan dan menghapus container (data database volume tetap aman)
docker compose down

# Menghentikan dan MENGHAPUS SEMUA DATA (reset total)
docker compose down -v
```
