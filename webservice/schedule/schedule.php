<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../bookings/helpers.php';

try {
    authenticate();
    $db = getConnection();
    autoCompleteExpiredBookings($db);

    $date = $_GET['date'] ?? date('Y-m-d');
    $roomId = $_GET['room_id'] ?? null;
    $view = $_GET['view'] ?? 'day';

    if (!strtotime($date)) {
        jsonResponse(400, 'Format tanggal tidak valid');
    }

    if ($view === 'week') {
        $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($date)));
        $weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($date)));

        $stmt = $db->prepare("
            SELECT b.*, u.name AS user_name, r.name AS room_name
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            JOIN rooms r ON r.id = b.room_id
            WHERE b.booking_date BETWEEN ? AND ?
              AND b.status IN ('approved', 'pending')
            ORDER BY b.booking_date, b.start_time
        ");
        $stmt->execute([$weekStart, $weekEnd]);
        $allBookings = $stmt->fetchAll();

        $scheduleByDay = [];
        $period = new DatePeriod(
            new DateTime($weekStart),
            new DateInterval('P1D'),
            (new DateTime($weekEnd))->modify('+1 day')
        );
        foreach ($period as $day) {
            $dayStr = $day->format('Y-m-d');
            $dayBookings = array_filter($allBookings, fn($b) => $b['booking_date'] === $dayStr);
            $scheduleByDay[$dayStr] = array_values(array_map('formatBookingRow', $dayBookings));
        }

        jsonResponse(200, 'Jadwal mingguan', [
            'week_start' => $weekStart,
            'week_end'   => $weekEnd,
            'schedule'   => $scheduleByDay,
        ]);
    }

    $roomCondition = '';
    $roomParams = [];

    if ($roomId) {
        $roomCondition = "WHERE id = ?";
        $roomParams[] = (int)$roomId;
    }

    $stmt = $db->prepare("SELECT id, name FROM rooms $roomCondition ORDER BY name ASC");
    $stmt->execute($roomParams);
    $rooms = $stmt->fetchAll();

    $schedule = [];

    foreach ($rooms as $room) {
        $stmt = $db->prepare("
            SELECT b.*, u.name AS user_name
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            WHERE b.room_id = ?
              AND b.booking_date = ?
              AND b.status IN ('approved', 'pending')
            ORDER BY b.start_time ASC
        ");
        $stmt->execute([$room['id'], $date]);
        $bookings = $stmt->fetchAll();

        $schedule[] = [
            'room' => [
                'id'   => (int)$room['id'],
                'name' => $room['name'],
            ],
            'bookings' => array_map(function($b) {
                return [
                    'id'         => (int)$b['id'],
                    'start_time' => substr($b['start_time'], 0, 5),
                    'end_time'   => substr($b['end_time'], 0, 5),
                    'purpose'    => $b['purpose'],
                    'status'     => $b['status'],
                    'user'       => $b['user_name'],
                ];
            }, $bookings),
        ];
    }

    jsonResponse(200, 'Jadwal ruangan', [
        'date'     => $date,
        'schedule' => $schedule,
    ]);

} catch (PDOException $e) {
    jsonResponse(500, 'Terjadi kesalahan server: ' . $e->getMessage());
} catch (Exception $e) {
    jsonResponse(500, 'Terjadi kesalahan server');
}
