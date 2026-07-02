CREATE DATABASE IF NOT EXISTS smart_office
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE smart_office;

DROP TABLE IF EXISTS user_tokens;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id          INT           NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100)  NOT NULL,
  email       VARCHAR(255)  NOT NULL,
  password    VARCHAR(255)  NOT NULL,
  role        ENUM('admin', 'karyawan') NOT NULL DEFAULT 'karyawan',
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rooms (
  id          INT           NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100)  NOT NULL,
  capacity    INT           NOT NULL,
  facilities  TEXT          NULL,
  status      ENUM('available', 'maintenance') NOT NULL DEFAULT 'available',
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_rooms_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bookings (
  id            INT           NOT NULL AUTO_INCREMENT,
  user_id       INT           NOT NULL,
  room_id       INT           NOT NULL,
  booking_date  DATE          NOT NULL,
  start_time    TIME          NOT NULL,
  end_time      TIME          NOT NULL,
  purpose       TEXT          NULL,
  status        ENUM('pending', 'approved', 'rejected', 'completed')
                              NOT NULL DEFAULT 'pending',
  admin_notes   TEXT          NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_bookings_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_bookings_room
    FOREIGN KEY (room_id) REFERENCES rooms (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_bookings_time_range
    CHECK (end_time > start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_bookings_room_date
  ON bookings (room_id, booking_date, start_time, end_time, status);
CREATE INDEX idx_bookings_user
  ON bookings (user_id, status);
CREATE INDEX idx_bookings_status
  ON bookings (status);

CREATE TABLE user_tokens (
  id         INT          NOT NULL AUTO_INCREMENT,
  user_id    INT          NOT NULL,
  token      VARCHAR(64)  NOT NULL,
  expires_at DATETIME     NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_token (token),
  INDEX idx_user_id (user_id),
  INDEX idx_expires (expires_at),
  CONSTRAINT fk_tokens_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email, password, role) VALUES
  ('Admin Utama', 'admin@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'admin'),
  ('Admin Pendukung', 'admin2@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'admin'),
  ('Karyawan Demo', 'karyawan@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 01', 'karyawan01@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 02', 'karyawan02@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 03', 'karyawan03@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 04', 'karyawan04@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 05', 'karyawan05@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 06', 'karyawan06@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 07', 'karyawan07@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 08', 'karyawan08@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 09', 'karyawan09@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan'),
  ('Karyawan 10', 'karyawan10@office.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'karyawan');

INSERT INTO rooms (name, capacity, facilities, status) VALUES
  ('Meeting Room A', 6,  'Proyektor, Whiteboard, AC, Wi-Fi',               'available'),
  ('Meeting Room B', 10, 'Proyektor, TV 65", Whiteboard, AC, Wi-Fi',     'available'),
  ('Training Room',  20, 'Proyektor, Sound System, AC, Meja Kursi, Wi-Fi','available'),
  ('VIP Room',       4,  'TV 75", Sofa, Kulkas, AC, Wi-Fi',              'available'),
  ('Open Space',     15, 'Meja besar, Proyektor, AC, Wi-Fi, Papan Tulis', 'available');

INSERT INTO bookings (user_id, room_id, booking_date, start_time, end_time, purpose, status, admin_notes) VALUES
  ((SELECT id FROM users WHERE email = 'karyawan01@office.com'), (SELECT id FROM rooms WHERE name = 'Meeting Room A'), DATE_SUB(CURDATE(), INTERVAL 3 DAY), '08:00:00', '09:00:00', 'Review mingguan', 'approved', 'Riwayat 3 hari lalu'),
  ((SELECT id FROM users WHERE email = 'karyawan02@office.com'), (SELECT id FROM rooms WHERE name = 'Meeting Room B'), DATE_SUB(CURDATE(), INTERVAL 2 DAY), '11:00:00', '12:00:00', 'Diskusi vendor', 'approved', 'Riwayat 2 hari lalu'),
  ((SELECT id FROM users WHERE email = 'karyawan03@office.com'), (SELECT id FROM rooms WHERE name = 'Training Room'), DATE_SUB(CURDATE(), INTERVAL 1 DAY), '13:30:00', '15:00:00', 'Workshop tim', 'approved', 'Riwayat kemarin'),
  ((SELECT id FROM users WHERE email = 'karyawan04@office.com'), (SELECT id FROM rooms WHERE name = 'VIP Room'), CURDATE(), '09:00:00', '10:00:00', 'Rapat tim harian', 'approved', 'Disetujui oleh admin'),
  ((SELECT id FROM users WHERE email = 'karyawan05@office.com'), (SELECT id FROM rooms WHERE name = 'Open Space'), CURDATE(), '10:30:00', '11:30:00', 'Diskusi proyek', 'approved', 'Disetujui oleh admin'),
  ((SELECT id FROM users WHERE email = 'karyawan06@office.com'), (SELECT id FROM rooms WHERE name = 'Training Room'), CURDATE(), '13:00:00', '15:00:00', 'Workshop internal', 'pending', 'Menunggu persetujuan'),
  ((SELECT id FROM users WHERE email = 'karyawan07@office.com'), (SELECT id FROM rooms WHERE name = 'VIP Room'), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', 'Pertemuan klien', 'approved', 'Disetujui oleh admin'),
  ((SELECT id FROM users WHERE email = 'karyawan08@office.com'), (SELECT id FROM rooms WHERE name = 'Open Space'), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', 'Brainstorming', 'pending', 'Menunggu persetujuan'),
  ((SELECT id FROM users WHERE email = 'karyawan09@office.com'), (SELECT id FROM rooms WHERE name = 'Meeting Room A'), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:30:00', '09:30:00', 'Review sprint', 'approved', 'Disetujui oleh admin'),
  ((SELECT id FROM users WHERE email = 'karyawan10@office.com'), (SELECT id FROM rooms WHERE name = 'Meeting Room B'), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '15:00:00', 'Presentasi vendor', 'pending', 'Menunggu persetujuan'),
  ((SELECT id FROM users WHERE email = 'karyawan01@office.com'), (SELECT id FROM rooms WHERE name = 'Training Room'), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '12:00:00', 'Pelatihan onboarding', 'approved', 'Disetujui oleh admin'),
  ((SELECT id FROM users WHERE email = 'karyawan02@office.com'), (SELECT id FROM rooms WHERE name = 'VIP Room'), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '16:00:00', 'Interview kandidat', 'pending', 'Menunggu persetujuan'),
  ((SELECT id FROM users WHERE email = 'karyawan03@office.com'), (SELECT id FROM rooms WHERE name = 'Open Space'), DATE_ADD(CURDATE(), INTERVAL 4 DAY), '12:00:00', '13:00:00', 'Social event', 'approved', 'Disetujui oleh admin');
