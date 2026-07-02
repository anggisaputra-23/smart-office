<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getConnection();
    autoCompleteExpiredBookings($db);

    switch ($method) {

        case 'GET':
            $user = authenticate();

            if (isset($_GET['id'])) {
                $stmt = $db->prepare("
                    SELECT b.*, u.name AS user_name, r.name AS room_name
                    FROM bookings b
                    JOIN users u ON u.id = b.user_id
                    JOIN rooms r ON r.id = b.room_id
                    WHERE b.id = ?
                ");
                $stmt->execute([(int)$_GET['id']]);
                $booking = $stmt->fetch();

                if (!$booking) {
                    jsonResponse(404, 'Booking tidak ditemukan');
                }

                if ($user['role'] !== 'admin' && $booking['user_id'] != $user['id']) {
                    jsonResponse(403, 'Akses ditolak');
                }

                jsonResponse(200, 'Detail booking', formatBookingRow($booking));
            }

            $conditions = [];
            $params = [];

            if ($user['role'] !== 'admin') {
                $conditions[] = "b.user_id = ?";
                $params[] = $user['id'];
            }

            if (!empty($_GET['status'])) {
                $conditions[] = "b.status = ?";
                $params[] = $_GET['status'];
            }

            if (!empty($_GET['room_id'])) {
                $conditions[] = "b.room_id = ?";
                $params[] = (int)$_GET['room_id'];
            }

            if (!empty($_GET['date'])) {
                $conditions[] = "b.booking_date = ?";
                $params[] = $_GET['date'];
            }

            if (!empty($_GET['user_id']) && $user['role'] === 'admin') {
                $conditions[] = "b.user_id = ?";
                $params[] = (int)$_GET['user_id'];
            }

            $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $stmt = $db->prepare("
                SELECT b.*, u.name AS user_name, r.name AS room_name
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN rooms r ON r.id = b.room_id
                $where
                ORDER BY b.booking_date DESC, b.start_time ASC
            ");
            $stmt->execute($params);
            $bookings = $stmt->fetchAll();

            $result = array_map('formatBookingRow', $bookings);

            jsonResponse(200, 'Daftar booking', $result);
            break;

        case 'POST':
            $user = authenticate();
            $data = json_decode(file_get_contents('php://input'), true) ?? [];

            $errors = validate($data, [
                'room_id'      => 'required|integer|exists:rooms,id',
                'booking_date' => 'required|date',
                'start_time'   => 'required|time',
                'end_time'     => 'required|time',
                'purpose'      => 'max:500',
            ]);

            if ($errors) {
                jsonResponse(422, 'Validasi gagal', null, $errors);
            }

            $stmt = $db->prepare("SELECT id, name, status FROM rooms WHERE id = ?");
            $stmt->execute([$data['room_id']]);
            $room = $stmt->fetch();

            if (!$room) {
                jsonResponse(404, 'Ruangan tidak ditemukan');
            }

            if ($room['status'] === 'maintenance') {
                jsonResponse(400, 'Ruangan sedang dalam perawatan');
            }

            if ($data['end_time'] <= $data['start_time']) {
                jsonResponse(422, 'Validasi gagal', null, [
                    'end_time' => ['Waktu selesai harus setelah waktu mulai']
                ]);
            }

            $bookingDate = $data['booking_date'];
            $today = date('Y-m-d');
            if ($bookingDate < $today) {
                jsonResponse(422, 'Validasi gagal', null, [
                    'booking_date' => ['Tidak bisa booking di tanggal yang sudah lewat']
                ]);
            }

            $conflicts = checkScheduleConflict(
                $db,
                (int)$data['room_id'],
                $data['booking_date'],
                $data['start_time'],
                $data['end_time']
            );

            if (count($conflicts) > 0) {
                $conflictData = array_map(function($c) {
                    return [
                        'id'         => (int)$c['id'],
                        'start_time' => substr($c['start_time'], 0, 5),
                        'end_time'   => substr($c['end_time'], 0, 5),
                        'status'     => $c['status'],
                    ];
                }, $conflicts);

                jsonResponse(409, 'Ruangan sudah dibooking pada jam tersebut', [
                    'conflicts' => $conflictData,
                    'room'      => $room['name'],
                    'requested' => [
                        'date'       => $data['booking_date'],
                        'start_time' => $data['start_time'],
                        'end_time'   => $data['end_time'],
                    ],
                ]);
            }

            $stmt = $db->prepare("
                INSERT INTO bookings (user_id, room_id, booking_date, start_time, end_time, purpose, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $user['id'],
                $data['room_id'],
                $data['booking_date'],
                $data['start_time'],
                $data['end_time'],
                $data['purpose'] ?? null,
            ]);

            $bookingId = $db->lastInsertId();

            $stmt = $db->prepare("
                SELECT b.*, r.name AS room_name
                FROM bookings b
                JOIN rooms r ON r.id = b.room_id
                WHERE b.id = ?
            ");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();

            jsonResponse(201, 'Booking berhasil dibuat, menunggu persetujuan admin', formatBookingRow($booking));
            break;

        case 'PATCH':
            authenticate();
            requireAdmin();

            $id = $_GET['id'] ?? null;
            if (!$id) {
                jsonResponse(400, 'Parameter id diperlukan');
            }

            $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch();

            if (!$booking) {
                jsonResponse(404, 'Booking tidak ditemukan');
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? [];

            $errors = validate($data, [
                'status' => 'required|in:approved,rejected',
            ]);

            if ($errors) {
                jsonResponse(422, 'Validasi gagal', null, $errors);
            }

            if (!isValidStatusTransition($booking['status'], $data['status'])) {
                jsonResponse(400, "Booking dengan status '{$booking['status']}' tidak bisa diubah ke '{$data['status']}'");
            }

            if ($data['status'] === 'rejected' && empty($data['admin_notes'])) {
                jsonResponse(422, 'Validasi gagal', null, [
                    'admin_notes' => ['Catatan admin wajib diisi saat menolak booking']
                ]);
            }

            if ($data['status'] === 'approved') {
                $conflicts = checkScheduleConflict(
                    $db,
                    (int)$booking['room_id'],
                    $booking['booking_date'],
                    $booking['start_time'],
                    $booking['end_time'],
                    (int)$id
                );

                $approvedConflicts = array_filter($conflicts, fn($c) => $c['status'] === 'approved');
                if (count($approvedConflicts) > 0) {
                    jsonResponse(409, 'Tidak bisa approve: terdeteksi bentrok jadwal dengan booking lain yang sudah disetujui');
                }

                if ($booking['booking_date'] < date('Y-m-d')) {
                    $data['status'] = 'completed';
                }
            }

            $stmt = $db->prepare("UPDATE bookings SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([
                $data['status'],
                $data['admin_notes'] ?? $booking['admin_notes'],
                $id,
            ]);

            $stmt = $db->prepare("
                SELECT b.*, u.name AS user_name, r.name AS room_name
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN rooms r ON r.id = b.room_id
                WHERE b.id = ?
            ");
            $stmt->execute([$id]);
            $updated = $stmt->fetch();

            $statusText = [
                'approved'  => 'disetujui',
                'rejected'  => 'ditolak',
                'completed' => 'diselesaikan',
            ];

            jsonResponse(200, "Booking telah {$statusText[$data['status']]}", formatBookingRow($updated));
            break;

        case 'DELETE':
            $user = authenticate();
            $id = $_GET['id'] ?? null;

            if (!$id) {
                jsonResponse(400, 'Parameter id diperlukan');
            }

            $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch();

            if (!$booking) {
                jsonResponse(404, 'Booking tidak ditemukan');
            }

            if ($user['role'] !== 'admin') {
                if ($booking['user_id'] != $user['id']) {
                    jsonResponse(403, 'Anda hanya bisa membatalkan booking sendiri');
                }
                if ($booking['status'] !== 'pending') {
                    jsonResponse(403, 'Booking dengan status ' . $booking['status'] . ' tidak bisa dibatalkan');
                }
            }

            $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse(200, 'Booking berhasil dibatalkan');
            break;

        default:
            jsonResponse(405, 'Method tidak diizinkan');
    }

} catch (PDOException $e) {
    jsonResponse(500, 'Terjadi kesalahan server: ' . $e->getMessage());
} catch (Exception $e) {
    jsonResponse(500, 'Terjadi kesalahan server');
}
