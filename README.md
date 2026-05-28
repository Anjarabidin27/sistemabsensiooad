# SIHADIR — Sistem Informasi Kehadiran
### Universitas Dian Nuswantoro (UDINUS)

> Sistem presensi berbasis pengenalan wajah (Face Recognition Attendance System)

---

## 🏗️ Arsitektur

```
Browser ──► PHP/Laravel (port 8000) ──► Python/Flask AI Engine (port 5000)
                    │                              │
                    └──────────── MySQL ───────────┘
                                 (port 3306)
```

**Stack:**
| Layer | Teknologi |
|-------|-----------|
| Web Backend | PHP 8.2 + Laravel 11 |
| AI Engine | Python 3.11 + Flask + DeepFace (ArcFace) |
| Database | MySQL 8.0 |
| Containerization | Docker + Docker Compose |
| Frontend | Blade Templates + Vanilla JS |

---

## ⚙️ Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows/Mac/Linux)
- Git

> **Tidak perlu** install PHP, Python, atau MySQL secara lokal. Semua berjalan di Docker.

---

## 🚀 Cara Menjalankan (Development)

### 1. Clone & Setup Environment

```bash
git clone <repo-url>
cd ooaduas

# Copy environment file
copy .env.example .env
```

### 2. Jalankan dengan Docker Compose

```bash
docker-compose up -d
```

Tunggu ±3-5 menit pertama kali (download image + build).

### 3. Setup Database & Seeder

```bash
# Jalankan migrations & seeders
docker-compose exec web php artisan migrate --seed
```

### 4. Akses Aplikasi

| Service | URL | Keterangan |
|---------|-----|------------|
| **Web (Student)** | http://localhost:8000 | Halaman utama mahasiswa |
| **Admin Panel** | http://localhost:8000/admin | Dashboard admin |
| **phpMyAdmin** | http://localhost:8080 | Database manager |
| **AI Engine** | http://localhost:5000/api/health | Cek status AI |

### 5. Default Login Admin

```
Email    : admin@udinus.ac.id
Password : admin123
```

---

## 📁 Struktur Project

```
ooaduas/
├── docker-compose.yml          # Orchestrate semua service
├── .env.example                # Template konfigurasi
├── README.md
│
├── ai-engine/                  # 🐍 Python Flask AI Engine
│   ├── app.py                  # Entry point
│   ├── config.py
│   ├── requirements.txt
│   ├── Dockerfile
│   ├── routes/                 # API endpoints
│   │   ├── health.py           # GET  /api/health
│   │   ├── recognize.py        # POST /api/recognize
│   │   └── register.py        # POST /api/register
│   ├── services/               # Business logic
│   │   ├── face_detector.py
│   │   ├── face_recognizer.py
│   │   ├── liveness.py
│   │   └── embedding_store.py
│   ├── models/                 # DB models (SQLAlchemy)
│   ├── utils/
│   └── tests/
│
├── web/                        # 🐘 PHP Laravel
│   ├── app/Http/Controllers/   # Controllers
│   ├── app/Models/             # Eloquent models
│   ├── app/Services/           # Business services
│   ├── database/migrations/    # DB schema
│   ├── database/seeders/       # Sample data
│   ├── resources/views/        # Blade templates
│   ├── public/css/             # Styles
│   ├── public/js/              # JavaScript
│   └── routes/                 # web.php, api.php
│
├── docs/                       # 📖 Documentation
│   ├── FRS.md
│   ├── E2E_Flow.md
│   ├── API_Contract.md
│   └── Infrastructure.md
│
└── scripts/                    # Helper scripts
    └── init.sql
```

---

## 🔌 API Reference (AI Engine)

### `GET /api/health`
Cek status AI engine.
```json
{ "status": "ok", "model": "ArcFace", "uptime_seconds": 3600 }
```

### `POST /api/recognize`
Kenali wajah dari gambar.
- **Body**: `multipart/form-data` → `image` (file)
- **Response**:
```json
{
  "status": "recognized",
  "student_id": 42,
  "confidence": 0.913,
  "processing_time_ms": 340,
  "liveness_score": 87.5
}
```

### `POST /api/register`
Daftarkan wajah mahasiswa.
- **Body**: `multipart/form-data` → `image` (file), `student_id` (int), `student_name` (str)

### `DELETE /api/register/{student_id}`
Hapus data wajah mahasiswa.

---

## 🗄️ Database Schema

| Tabel | Deskripsi |
|-------|-----------|
| `students` | Data mahasiswa |
| `users` | Akun admin |
| `courses` | Mata kuliah |
| `face_embeddings` | Vektor wajah ArcFace |
| `attendances` | Rekap kehadiran |
| `recognition_logs` | Audit log setiap scan |
| `system_settings` | Konfigurasi sistem |

---

## 🔧 Konfigurasi Admin

Login ke admin panel → **Pengaturan Sistem** untuk mengatur:
- Logo & identitas universitas
- Warna tema
- Threshold confidence recognition (default: 80%)
- Batas menit terlambat (default: 15 menit)
- Liveness detection on/off

---

## 🛠️ Development Commands

```bash
# Lihat logs
docker-compose logs -f

# Restart service tertentu
docker-compose restart ai-engine
docker-compose restart web

# Masuk ke container
docker-compose exec web bash
docker-compose exec ai-engine bash

# Jalankan tests AI engine
docker-compose exec ai-engine python -m pytest tests/ -v

# Jalankan tests Laravel
docker-compose exec web php artisan test

# Buat migration baru
docker-compose exec web php artisan make:migration create_xxx_table

# Clear cache Laravel
docker-compose exec web php artisan cache:clear && php artisan config:clear
```

---

## 👥 Tim Developer

Universitas Dian Nuswantoro — Semester Genap 2025/2026

---

## 📄 Lisensi

Internal use — Universitas Dian Nuswantoro
