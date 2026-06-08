# Laporan Penjelasan Teknis & Arsitektur SIHADIR

**SIHADIR** (Sistem Informasi Kehadiran) adalah aplikasi absensi berbasis *Face Recognition* (Pengenalan Wajah) yang dirancang untuk keperluan pencatatan kehadiran mahasiswa secara otomatis, aman, dan efisien. Laporan ini disusun untuk menjelaskan arsitektur, teknologi, logika sistem, dan metode deployment.

---

## 1. Arsitektur Sistem & Teknologi yang Digunakan

Sistem ini dirancang menggunakan arsitektur **Microservices (Monolithic hybrid)** yang dipisahkan ke dalam beberapa *container* terisolasi agar performa komputasi AI tidak membebani server web utama. 

Secara keseluruhan, *tech stack* yang digunakan adalah:
- **Frontend / UI**: Menggunakan **Blade Templates** (dari Laravel), HTML5, CSS3, dan Vanilla JavaScript. Digunakan untuk merender antarmuka pengguna, akses webcam untuk scanner, dan UI dashboard.
- **Web Backend (Core App)**: Dibangun dengan **PHP 8.2** dan framework **Laravel 11**. Bertugas mengelola sesi *login*, manajemen data mahasiswa, rekapitulasi absen, *routing*, dan memproses data dari/ke database.
- **AI Engine (Face Recognition)**: Berjalan sebagai service terpisah menggunakan **Python 3.11** dan **Flask**. Mesin ini memanfaatkan library **DeepFace** (dengan model algoritma **ArcFace**) untuk melakukan ekstraksi dan perbandingan fitur wajah.
- **Database**: Menggunakan **MySQL 8.0** sebagai penyimpanan data relasional (menyimpan akun, log presensi, hingga data vektor/embedding wajah).
- **Containerization**: Menggunakan **Docker** & **Docker Compose** agar sistem bisa berjalan stabil secara konsisten di komputer pengembang maupun server VPS tanpa masalah "works on my machine".
- **Reverse Proxy**: Menggunakan **Nginx Proxy Manager** untuk mengatur *domain routing* dan sertifikat keamanan SSL/HTTPS.

---

## 2. Logika Sistem (Alur Kerja)

### A. Alur Pendaftaran Wajah (Enrollment)
1. **Input**: Mahasiswa atau Admin mengunggah/mengambil foto wajah sebagai data acuan.
2. **Proses Web**: Laravel menerima gambar tersebut dan meneruskannya ke Service AI Engine melalui protokol REST API (`POST /api/register`).
3. **Proses AI**: Python (Flask) menerima gambar, lalu algoritma pengenalan wajah akan memotong (*crop*) bagian wajah dan melakukan proses *feature extraction* (mengubah pola visual wajah menjadi rentetan angka matematis atau *Face Embeddings*).
4. **Penyimpanan**: *Face Embeddings* (vektor wajah) tersebut kemudian disimpan ke dalam database MySQL untuk dicocokkan di kemudian hari.

### B. Alur Presensi (Scanning)
1. **Input (Kamera)**: Saat mahasiswa melakukan absen, halaman website akan membuka akses kamera via JavaScript dan mengambil *snapshot* (foto).
2. **Liveness Detection**: Sebelum dikenali wajahnya, sistem (di AI Engine) akan melakukan pengecekan *liveness* (keaslian). Hal ini untuk membedakan apakah wajah di depan kamera adalah manusia asli atau sekadar foto/layar HP (mencegah kecurangan spoofing).
3. **Face Recognition (Pencocokan)**: Jika terdeteksi asli, AI Engine akan mengubah foto terbaru itu menjadi *Face Embeddings* lalu membandingkannya (menghitung jarak matematis / *Cosine Similarity*) dengan seluruh data vektor wajah yang ada di database.
4. **Verifikasi**: Jika sistem menemukan kecocokan dengan tingkat kepercayaan (*Confidence Score*) lebih dari ambang batas (misal: > 80%), maka AI akan mengembalikan respons "Dikenali" beserta ID mahasiswa tersebut.
5. **Pencatatan**: Laravel akan memproses respons dari AI dan mencatat waktu presensi ke dalam tabel `attendances` di database MySQL.

---

## 3. Cara Deployment (Panduan ke Server / VPS)

Proses perilisan (deployment) ke server VPS dilakukan dengan menggunakan Docker Compose, yang membuat prosesnya sangat rapi. Berikut adalah langkah logis saat sistem dideploy:

1. **Persiapan Server (VPS)**
   - Server harus sudah ter-install **Docker**, **Docker Compose**, dan **Git**.
   - Sistem operasi yang disarankan adalah Ubuntu/Linux.

2. **Pengambilan Kode (Clone Repository)**
   - Developer mengunduh source code dari repository (GitHub) ke server VPS:
     ```bash
     git clone https://github.com/Anjarabidin27/sistemabsensiooad.git sihadir
     cd sihadir
     ```

3. **Konfigurasi Environment (.env)**
   - Developer menyalin file konfigurasi `cp .env.example .env`.
   - Di dalam file `.env`, developer mengatur *Environment Variables* seperti password database, Base URL aplikasi, environment stat (production), dan pengaturan cache.

4. **Orkestrasi Container (Build & Run)**
   - Menjalankan seluruh sistem cukup dengan satu perintah:
     ```bash
     docker compose up -d --build
     ```
   - **Logika di balik perintah ini**: Docker Compose membaca file `docker-compose.yml`. Ia akan membuat jaringan privat virtual bernama `sihadir_net`. Lalu ia mengunduh *image* MySQL dan Nginx, serta merakit (*build*) sistem operasi mini Linux untuk Laravel dan Python berdasarkan file `Dockerfile` masing-masing. Setelah dirakit, 5 container tersebut akan dijalankan di latar belakang secara bersamaan.

5. **Migrasi Database & Seeding**
   - Menjalankan *schema builder* untuk membentuk tabel di MySQL kosong agar sesuai dengan sistem SIHADIR:
     ```bash
     docker compose exec web php artisan migrate --seed
     ```
   - Perintah ini dieksekusi **di dalam** container Laravel (`sihadir_web`), yang otomatis membuat tabel akun, tabel absensi, dan mengisi data awal (seeder) seperti akun Admin default.

6. **Pengaturan Domain & HTTPS (Nginx Proxy Manager)**
   - Mengarahkan nama domain (contoh: `absensiooad-udinus.web.id`) ke IP Server VPS di pengaturan DNS Cloudflare/Provider Domain.
   - Masuk ke portal Nginx Proxy Manager di port 81, menambahkan *Proxy Host* baru yang meneruskan *traffic* dari port 80/443 luar menuju ke container `sihadir_web` pada port 8000.
   - Mengaktifkan "Force SSL" agar website aman diakses menggunakan gembok hijau (HTTPS) dengan bantuan Let's Encrypt.
