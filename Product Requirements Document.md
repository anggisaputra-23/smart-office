# Product Requirements Document (PRD)

<div align="center">
  <img src="assets/images/logo.png" alt="SOMS Logo" height="100">
  <h1>Smart Office Management System</h1>
  <p>Versi 1.0.0 — Juni 2026</p>
</div>

---

## 1. Ringkasan Eksekutif

**Smart Office Management System (SOMS)** adalah aplikasi berbasis web untuk mengelola peminjaman ruangan kantor secara digital. Aplikasi ini memungkinkan karyawan untuk melihat ketersediaan ruangan, melakukan booking, dan memungkinkan admin untuk mengelola ruangan serta menyetujui atau menolak permintaan booking.

Dibangun dengan arsitektur **PHP Native + REST API + MySQL**, SOMS dirancang untuk kemudahan deployment di lingkungan Laragon tanpa dependensi framework berat.

---

## 2. Tujuan Produk

### 2.1 Visi
Menyediakan solusi digital yang efisien dan transparan untuk manajemen peminjaman ruangan kantor, menggantikan proses manual yang rentan bentrok jadwal.

### 2.2 Misi
- Mengotomatiskan validasi bentrok jadwal booking
- Memberikan visibilitas real-time terhadap ketersediaan ruangan
- Menyediakan alur approval yang jelas antara karyawan dan admin
- Mendokumentasikan seluruh API secara interaktif

### 2.3 Tujuan
- Mengurangi bentrok jadwal ruangan hingga 100% melalui validasi otomatis
- Mempercepat proses booking dari manual menjadi < 1 menit
- Menyediakan dashboard statistik untuk monitoring penggunaan ruangan

---

## 3. Target Pengguna

| Persona | Role | Kebutuhan Utama |
|---------|------|-----------------|
| **Karyawan** | User biasa | Melihat ruangan, booking, cek jadwal |
| **Admin** | Administrator | CRUD ruangan, approve/reject booking, kelola user |
| **Developer** | Integrator | Dokumentasi API untuk integrasi sistem lain |

---

## 4. Fitur Produk

### 4.1 MVP (Minimum Viable Product) ✅

#### 4.1.1 Autentikasi
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| AUTH-01 | Register akun baru (role default: karyawan) | P0 | ✅ |
| AUTH-02 | Login dengan email & password | P0 | ✅ |
| AUTH-03 | Logout (hapus token) | P0 | ✅ |
| AUTH-04 | Lihat profil user saat ini | P0 | ✅ |
| AUTH-05 | Bearer Token auth dengan expiry 7 hari | P0 | ✅ |

#### 4.1.2 Manajemen Ruangan
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| ROOM-01 | Lihat daftar semua ruangan | P0 | ✅ |
| ROOM-02 | Lihat detail ruangan | P0 | ✅ |
| ROOM-03 | Tambah ruangan baru (admin) | P0 | ✅ |
| ROOM-04 | Update data ruangan (admin) | P0 | ✅ |
| ROOM-05 | Hapus ruangan (admin) | P0 | ✅ |
| ROOM-06 | Filter status: available / maintenance | P1 | ✅ |

#### 4.1.3 Booking Ruangan
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| BOOK-01 | Buat booking baru (status: pending) | P0 | ✅ |
| BOOK-02 | Validasi bentrok jadwal otomatis | P0 | ✅ |
| BOOK-03 | Lihat daftar booking (filter: status, room, date, user) | P0 | ✅ |
| BOOK-04 | Batalkan booking | P0 | ✅ |

#### 4.1.4 Approval Booking
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| APPR-01 | Approve booking (admin) | P0 | ✅ |
| APPR-02 | Reject booking dengan catatan (admin) | P0 | ✅ |
| APPR-03 | Enforce status workflow | P0 | ✅ |

#### 4.1.5 Jadwal & Dashboard
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| SCHED-01 | Jadwal harian penggunaan ruangan | P0 | ✅ |
| SCHED-02 | Jadwal mingguan penggunaan ruangan | P1 | ✅ |
| DASH-01 | Statistik dashboard (total ruangan, booking hari ini, pending, dll) | P0 | ✅ |

#### 4.1.6 API Documentation
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| DOC-01 | Dokumentasi OpenAPI 3.0 | P0 | ✅ |
| DOC-02 | Viewer interaktif (RapiDoc) dengan Try It | P0 | ✅ |
| DOC-03 | Autentikasi Bearer Token di dokumentasi | P0 | ✅ |

### 4.2 Fitur Lanjutan (Post-MVP)

| ID | Fitur | Prioritas |
|----|-------|-----------|
| FUT-01 | Export laporan PDF / Excel | P2 |
| FUT-02 | Notifikasi email saat booking di-approve/reject | P2 |
| FUT-03 | Dark mode toggle | P2 |
| FUT-04 | Manajemen user (CRUD) oleh admin | P2 |
| FUT-05 | Recurring booking (booking berulang) | P3 |
| FUT-06 | Upload foto ruangan | P3 |
| FUT-07 | Kalender integrasi (Google Calendar, Outlook) | P3 |
| FUT-08 | Mobile app (Android/iOS) | P4 |

---

## 5. Use Case & Alur

### 5.1 Alur Booking

```
Karyawan                  Sistem                    Admin
   │                        │                        │
   ├─ Login ──────────────► │                        │
   │◄── Token ───────────── │                        │
   │                        │                        │
   ├─ Booking ruangan ────► │                        │
   │                        ├─ Validasi bentrok ──── │
   │◄── Status: pending ──── │                        │
   │                        │                        │
   │                        │◄── Approve/Reject ──── ├
   │◄── Status: approved ─── │                        │
   │       / rejected ───── │                        │
   │                        │                        │
   │                        ├─ Auto complete ─────── │
   │◄── Status: completed ── │                        │
```

### 5.2 Status Workflow

```
           ┌─────────┐
           │ pending │
           └────┬────┘
           ╱          ╲
          ╱            ╲
    ┌─────────┐   ┌──────────┐
    │approved │   │ rejected │
    └────┬────┘   └──────────┘
         │
    ┌───────────┐
    │ completed │
    └───────────┘
```

**Aturan transisi status:**
- `pending` → `approved` (admin)
- `pending` → `rejected` (admin, wajib isi `admin_notes`)
- `approved` → `completed` (otomatis ketika waktu booking telah lewat)
- Transisi lain: **tidak diizinkan** (akan return 400)

### 5.3 Aturan Bisnis

- Karyawan hanya bisa melihat & membatalkan booking milik sendiri
- Admin bisa melihat & mengelola semua booking
- Booking baru otomatis status `pending`
- Validasi bentrok: `existing.start < new.end AND existing.end > new.start`
- Hanya booking `pending` dan `approved` yang dicek bentroknya
- Ruangan dengan booking aktif (`pending`/`approved`) tidak bisa dihapus
- `admin_notes` wajib diisi saat reject

---

## 6. Spesifikasi Teknis

### 6.1 Arsitektur

```
┌─────────────────────────────────────────────────────┐
│                   Browser (Client)                    │
│  HTML + CSS + Bootstrap 5.3.3 + Vanilla JS (api.js) │
└───────────────────┬─────────────────────────────────┘
                    │  HTTP/JSON (Fetch API)
                    │  Authorization: Bearer <token>
┌───────────────────▼─────────────────────────────────┐
│              REST API (PHP Native)                    │
│  ┌──────────┐  ┌──────────┐  ┌──────────────────┐  │
│  │  Auth    │  │  Rooms   │  │  Bookings        │  │
│  └──────────┘  └──────────┘  └──────────────────┘  │
│  ┌──────────┐  ┌──────────┐  ┌──────────────────┐  │
│  │ Schedule │  │Dashboard │  │  Middleware Auth │  │
│  └──────────┘  └──────────┘  └──────────────────┘  │
└───────────────────┬─────────────────────────────────┘
                    │  PDO / MySQLi
┌───────────────────▼─────────────────────────────────┐
│              MySQL 8.0 Database                       │
│  users │ rooms │ bookings │ user_tokens              │
└─────────────────────────────────────────────────────┘
```

### 6.2 Database Schema

#### users
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | — |
| name | VARCHAR(100) | Nama lengkap |
| email | VARCHAR(100) (UNIQUE) | Email |
| password | VARCHAR(255) | Password (hash bcrypt) |
| role | ENUM('admin','karyawan') | Role user |
| created_at | TIMESTAMP | — |
| updated_at | TIMESTAMP | — |

#### rooms
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | — |
| name | VARCHAR(100) (UNIQUE) | Nama ruangan |
| capacity | INT | Kapasitas |
| facilities | TEXT | Fasilitas (opsional) |
| status | ENUM('available','maintenance') | Status |
| created_at | TIMESTAMP | — |
| updated_at | TIMESTAMP | — |

#### bookings
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | — |
| user_id | INT (FK → users.id) | Peminjam |
| room_id | INT (FK → rooms.id) | Ruangan |
| booking_date | DATE | Tanggal |
| start_time | TIME | Mulai |
| end_time | TIME | Selesai |
| purpose | VARCHAR(255) | Tujuan |
| status | ENUM('pending','approved','rejected','completed') | Status |
| admin_notes | TEXT | Catatan admin |
| created_at | TIMESTAMP | — |

#### user_tokens
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | — |
| user_id | INT (FK → users.id) | — |
| token | VARCHAR(64) (UNIQUE) | Token |
| expires_at | TIMESTAMP | Expiry (7 hari) |
| created_at | TIMESTAMP | — |

### 6.3 REST API Endpoints

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| POST | `/auth/register.php` | ❌ | Semua |
| POST | `/auth/login.php` | ❌ | Semua |
| POST | `/auth/logout.php` | ✅ | Semua |
| GET | `/auth/me.php` | ✅ | Semua |
| GET | `/rooms/rooms.php` | ✅ | Semua |
| POST | `/rooms/rooms.php` | ✅ | Admin |
| PUT | `/rooms/rooms.php` | ✅ | Admin |
| DELETE | `/rooms/rooms.php` | ✅ | Admin |
| GET | `/bookings/bookings.php` | ✅ | Semua |
| POST | `/bookings/bookings.php` | ✅ | Semua |
| PATCH | `/bookings/bookings.php` | ✅ | Admin |
| DELETE | `/bookings/bookings.php` | ✅ | Semua |
| GET | `/schedule/schedule.php` | ✅ | Semua |
| GET | `/dashboard/dashboard.php` | ✅ | Semua |

### 6.4 HTTP Response Format

**Sukses:**
```json
{
  "status": "success",
  "message": "Pesan",
  "data": { ... }
}
```

**Error:**
```json
{
  "status": "error",
  "message": "Pesan error"
}
```

**Validasi (422):**
```json
{
  "status": "error",
  "message": "Validasi gagal",
  "errors": {
    "field": ["Error 1", "Error 2"]
  }
}
```

**Conflict (409):**
```json
{
  "status": "error",
  "message": "Ruangan sudah dibooking di jam tersebut",
  "data": {
    "conflicts": [ ... ]
  }
}
```

---

## 7. Non-Functional Requirements

### 7.1 Keamanan
- Password di-hash menggunakan bcrypt (`password_hash`)
- Token autentikasi: string random 64 karakter hex
- Token expiry: 7 hari sejak dibuat
- Semua endpoint (kecuali login/register) wajib menyertakan `Authorization: Bearer <token>`
- Input divalidasi di sisi server (tidak hanya client-side)
- Prepared statements (PDO) untuk mencegah SQL injection

### 7.2 Performa
- Waktu respons API: < 500ms (database lokal)
- Validasi bentrok: < 100ms
- Database di-index pada kolom: `bookings.room_id`, `bookings.booking_date`, `user_tokens.token`

### 7.3 Kompatibilitas
- Chrome 90+, Firefox 90+, Edge 90+
- Resolusi minimum: 1024×768 (desktop)
- Responsive: tampilan mobile via media queries

---

## 8. Asumsi & Dependensi

### 8.1 Asumsi
- Server menggunakan Laragon dengan Apache + MySQL aktif
- PHP 8.4+ dengan ekstensi yang diperlukan sudah terinstall
- Browser mendukung Fetch API dan ES6+
- Tidak ada load balancing atau caching (single server)

### 8.2 Dependensi Eksternal
| Dependensi | Versi | Kegunaan |
|------------|-------|----------|
| Bootstrap | 5.3.3 | CSS framework |
| Bootstrap Icons | 1.11.3 | Icon set |
| Google Fonts (Inter) | — | Tipografi |
| RapiDoc | latest | API documentation viewer |

---

## 9. Kriteria Sukses

### 9.1 Kriteria Wajib (P0)
- [x] User bisa register dan login
- [x] Karyawan bisa booking ruangan
- [x] Sistem menolak booking yang bentrok (HTTP 409)
- [x] Admin bisa approve/reject booking
- [x] Admin bisa CRUD ruangan
- [x] Jadwal dan dashboard menampilkan data akurat
- [x] Dokumentasi API dapat diakses dan diuji langsung

### 9.2 Kriteria Tambahan (P1)
- [x] Jadwal tampilan mingguan
- [x] Filter booking berdasarkan status, ruangan, tanggal, user
- [x] Status otomatis berubah ke completed

---

## 10. Rencana Pengembangan Selanjutnya

| Fase | Fitur | Timeline |
|------|-------|----------|
| **Fase 1** (Selesai) | MVP: Auth, CRUD Ruangan, Booking, Approval, Schedule, Dashboard, API Docs | Juni 2026 |
| **Fase 2** (Rencana) | Export laporan, notifikasi email, dark mode | Q3 2026 |
| **Fase 3** (Rencana) | Manajemen user, recurring booking, upload foto ruangan | Q4 2026 |
| **Fase 4** (Rencana) | Integrasi kalender, mobile app | 2027 |

---

<div align="center">
  <p><strong>Smart Office Management System — PRD v1.0.0</strong></p>
  <p>Dokumen ini dibuat untuk kebutuhan UAS mata kuliah PBP</p>
</div>
