# Absensi Pro — Sistem Absensi Kerja Berbasis Web Mobile-Friendly

**Absensi Pro** adalah aplikasi sistem presensi kerja modern berbasis web yang dirancang dengan pendekatan *mobile-first*. Aplikasi ini memudahkan pegawai melakukan pencatatan kehadiran mandiri dari perangkat smartphone maupun komputer, sekaligus memberikan kendali penuh bagi tim HR / Administrator untuk mengelola jadwal kerja, meninjau pengajuan cuti, dan menindaklanjuti komplain presensi secara terpusat.

---

## Fitur Utama

### 1. Untuk Pegawai
- **Presensi Real-time & Validasi Radius GPS**:
  - Jam digital real-time dengan penanggalan otomatis.
  - Pengecekan lokasi akurat via GPS dengan formula *Haversine* (harus berada dalam radius maksimal 150 meter dari kantor untuk status Hadir).
  - Pilihan status kehadiran: **Hadir**, **WFH**, **Izin**, dan **Sakit**.
  - Pengambilan foto selfie via kamera perangkat saat check-in.
  - Tombol aksi dinamis Check-in & Check-out yang ramah sentuhan layar HP.
- **Riwayat Absensi**:
  - Filter berdasarkan bulan dan tahun.
  - Kartu ringkasan (*Total Hadir, Terlambat, Izin/Sakit/WFH, Absen*).
  - Tombol cepat untuk mengajukan komplain langsung pada baris absensi terkait.
- **Pengajuan Cuti / Izin / Sakit**:
  - Formulir pengajuan cuti tahunan, sakit, izin pribadi, atau cuti darurat.
  - Otomatis menghitung durasi hari kerja (mengecualikan hari Sabtu dan Minggu).
  - Unggah berkas lampiran (surat dokter / formulir) format PDF atau foto.
  - Pantau status pengajuan (*Menunggu, Disetujui, Ditolak*) serta opsi pembatalan mandiri jika masih berstatus pending.
- **Komplain Presensi**:
  - Laporkan kendala seperti waktu tidak sesuai, gagal lokasi GPS, lupa check-out, atau error sistem.
  - Unggah bukti tangkapan layar (screenshot) dan pantau catatan tanggapan dari HR.
- **Profil Pegawai**:
  - Pengelolaan data diri, nomor kontak, dan ganti foto profil avatar.
  - Penyimpanan rekening bank payroll dengan enkripsi keamanan (*Laravel Crypt*).
  - Pembaruan kata sandi akun.

### 2. Untuk Admin & HR
- **Dashboard Monitoring**:
  - Statistik harian pegawai yang hadir, terlambat, izin/WFH, dan belum absen.
  - Indikator notifikasi pengajuan cuti dan komplain yang membutuhkan peninjauan.
  - Daftar presensi harian seluruh pegawai dengan filter tanggal dan departemen.
- **Manajemen Pegawai**:
  - Tambah, edit, dan kelola data seluruh pegawai.
  - Penugasan shift jadwal kerja.
  - Nonaktifkan atau aktifkan akses akun pegawai.
- **Pengaturan Jadwal Kerja (Shift)**:
  - Kelola jam masuk, jam pulang, toleransi keterlambatan (menit), dan checklist hari kerja operasional.
- **Monitoring & Koreksi Presensi**:
  - Lihat foto selfie absensi dan koordinat pegawai.
  - Koreksi manual waktu check-in, check-out, maupun status absensi pegawai.
- **Verifikasi Pengajuan Cuti**:
  - Setujui atau tolak pengajuan cuti (wajib alasan jika menolak).
  - Saat disetujui, sistem **otomatis menyinkronkan data presensi pegawai** pada rentang tanggal cuti tersebut.
- **Penanganan Komplain**:
  - Buka detail keluhan pegawai melalui offcanvas drawer.
  - Berikan tanggapan admin dan selesaikan komplain (bisa langsung sekaligus mengoreksi data absensi terkait).

---

## Cara Penggunaan

### 1. Instalasi & Menjalankan Aplikasi

Jika baru pertama kali menyalin/clone repositori ini:

1. **Pasang Dependensi Backend & Frontend**:
   ```bash
   composer install
   npm install
   ```

2. **Salin File Konfigurasi Lingkungan**:
   ```bash
   cp .env.example .env
   ```
   Pastikan koneksi database MySQL pada file `.env` sudah sesuai (misal: database `absensi_app`).

3. **Generate App Key & Database Setup**:
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

4. **Build Frontend**:
   ```bash
   npm run build
   # atau untuk mode pengembangan: npm run dev
   ```

5. **Jalankan Server**:
   ```bash
   php artisan serve
   ```
   Akses aplikasi melalui browser di: **`http://localhost:8000`**.

---

### 2. Akun Demo & Kredensial Masuk

Pada halaman login (`http://localhost:8000/login`), tersedia tombol **Akses Cepat Demo (1-Klik)**:

| Peran | Email | Kata Sandi | Deskripsi Akses |
| :--- | :--- | :--- | :--- |
| **Admin HR** | `admin@absensi.com` | `password` | Mengelola data pegawai, shift, menyetujui cuti, dan menindaklanjuti komplain |
| **Pegawai** | `budi@absensi.com` | `password` | Melakukan check-in/out, cek riwayat, mengajukan izin/cuti, dan komplain |

---

### 3. Alur Penggunaan Aplikasi

#### A. Melakukan Presensi (Pegawai)
1. Masuk menggunakan akun pegawai.
2. Buka menu **Absen** (halaman utama).
3. Izinkan browser mengakses **Lokasi GPS** dan **Kamera**.
4. Pilih status kehadiran (*Hadir*, *WFH*, *Izin*, atau *Sakit*).
5. Masukkan catatan (opsional) atau ambil foto selfie kamera (opsional).
6. Klik tombol **Check-in Sekarang**. Jika memilih status *Hadir*, pastikan berada dalam radius 150 meter dari kantor.
7. Saat jam pulang tiba, buka kembali halaman absensi dan klik tombol **Check-out Pulang**.

#### B. Mengajukan Cuti atau Izin (Pegawai)
1. Dari menu navigasi, pilih **Pengajuan**.
2. Klik tombol **Buat Pengajuan Baru**.
3. Pilih jenis pengajuan (*Cuti Tahunan, Sakit, Izin, Cuti Darurat*).
4. Tentukan tanggal mulai dan selesai (durasi hari kerja otomatis dihitung tanpa hari libur akhir pekan).
5. Tuliskan alasan dan unggah lampiran surat/bukti (jika ada).
6. Klik **Kirimkan Pengajuan ke HR**.

#### C. Mengajukan Komplain Presensi (Pegawai)
1. Buka menu **Komplain** (atau klik tombol **Komplain** langsung di baris menu **Riwayat**).
2. Pilih tanggal kejadian dan kategori kendala yang dialami.
3. Tuliskan penjelasan kendala dan lampirkan bukti tangkapan layar jika ada.
4. Klik **Kirimkan Komplain ke HR**.

#### D. Pengelolaan oleh HR / Admin
1. Masuk menggunakan akun admin.
2. Pada **Dashboard**, pantau statistik kehadiran hari ini serta badge notifikasi untuk pengajuan cuti dan komplain baru.
3. Masuk ke menu **Pengajuan Cuti** untuk meninjau detail permohonan staf, lalu klik **Setujui** (absensi otomatis terisi) atau **Tolak** dengan alasan.
4. Masuk ke menu **Komplain Absensi**, klik **Tinjau & Tindak** pada komplain staf untuk memberikan tanggapan dan melakukan koreksi data presensi jika diperlukan.
5. Gunakan menu **Data Pegawai** dan **Jadwal Kerja** untuk mengelola staf dan shift kerja perusahaan.
