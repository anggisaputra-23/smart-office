<div align="center">
  <img src="assets/images/logo.png" alt="SOMS Logo" height="120">
  
<h1>Smart Office Management System</h1>
  <p>Sistem manajemen peminjaman ruangan kantor berbasis web</p>
</div>

## Fitur

- **Autentikasi** — Register, login, logout dengan Bearer Token (expiry 7 hari)
- **Manajemen Ruangan** — CRUD ruangan (admin), filter status available/maintenance
- **Booking Ruangan** — Booking dengan validasi bentrok jadwal otomatis (HTTP 409)
- **Approval Booking** — Admin approve/reject booking dengan catatan
- **Jadwal** — Tampilan jadwal harian & mingguan
- **Dashboard** — Statistik total ruangan, booking aktif, pending, jadwal hari ini
- **API Documentation** — Dokumentasi interaktif dengan RapiDoc (Try It langsung)

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8.4 (Native, tanpa framework) |
| Database | MySQL 8.0.30 |
| Frontend | Bootstrap 5.3.3, Bootstrap Icons, Inter Font |
| API Docs | RapiDoc (OpenAPI 3.0) |
| Server | Laragon (Apache) |

## Requirements

- Laragon (atau XAMPP/LAMP dengan Apache)
- PHP 8.4+ (dengan ekstensi: PDO, pdo_mysql, json, mbstring, openssl)
- MySQL 8.0+
- Browser modern (Chrome, Firefox, Edge)

## Instalasi

1. Clone atau copy folder proyek ke `C:\laragon\www\smart-office`

2. Import database:
   - Buka phpMyAdmin (`http://localhost/phpmyadmin`)
   - Buat database baru: `smart_office`
   - Import file `sql/schema.sql`

3. Start Laragon:
   - Klik **Start All**
   - Pastikan Apache dan MySQL running

4. Akses aplikasi:
   - Web: `http://localhost/smart-office/`
   - API Docs: `http://localhost/smart-office/api-docs/`

## Akun Demo

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@office.com | password |
| Karyawan | karyawan@office.com | password |

## API Documentation

Dokumentasi interaktif tersedia di:  
➡️ **`http://localhost/smart-office/api-docs/`**

### Daftar Endpoint

| Grup | Endpoint | Method | Auth | Deskripsi |
|------|----------|--------|------|-----------|
| **Authentication** | `/auth/register.php` | POST | ❌ | Register akun baru |
| | `/auth/login.php` | POST | ❌ | Login dapat token |
| | `/auth/logout.php` | POST | ✅ | Hapus token aktif |
| | `/auth/me.php` | GET | ✅ | Profil user saat ini |
| **Rooms** | `/rooms/rooms.php` | GET | ✅ | Daftar/detail ruangan |
| | `/rooms/rooms.php` | POST | ✅ | Tambah ruangan (admin) |
| | `/rooms/rooms.php` | PUT | ✅ | Update ruangan (admin) |
| | `/rooms/rooms.php` | DELETE | ✅ | Hapus ruangan (admin) |
| **Bookings** | `/bookings/bookings.php` | GET | ✅ | Daftar booking |
| | `/bookings/bookings.php` | POST | ✅ | Buat booking baru |
| | `/bookings/bookings.php` | PATCH | ✅ | Approve/reject (admin) |
| | `/bookings/bookings.php` | DELETE | ✅ | Batalkan booking |
| **Schedule** | `/schedule/schedule.php` | GET | ✅ | Jadwal harian/mingguan |
| **Dashboard** | `/dashboard/dashboard.php` | GET | ✅ | Statistik dashboard |

### Alur Booking

```
Karyawan login → dapat token → booking ruangan → status: pending
                                                    ↓
                                          Admin approve / reject
                                           ↓              ↓
                                      approved       rejected
                                           ↓
                                      completed (otomatis)
```

## Struktur Folder

```
smart-office/
├── api/
│   ├── auth/
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── me.php
│   │   └── register.php
│   ├── bookings/
│   │   ├── bookings.php
│   │   └── helpers.php
│   ├── config/
│   │   └── database.php
│   ├── dashboard/
│   │   └── dashboard.php
│   ├── middleware/
│   │   └── auth.php
│   ├── rooms/
│   │   └── rooms.php
│   └── schedule/
│       └── schedule.php
├── api-docs/
│   ├── index.html
│   └── openapi.json
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── images/
│   │   └── logo.png
│   └── js/
│       └── api.js
├── admin/
│   ├── bookings.php
│   └── rooms.php
├── includes/
│   ├── config.php
│   ├── footer.php
│   └── header.php
├── sql/
│   └── schema.sql
├── booking.php
├── dashboard.php
├── login.php
├── logout.php
├── rooms.php
├── schedule.php
└── README.md
```

## Lisensi

MIT License
