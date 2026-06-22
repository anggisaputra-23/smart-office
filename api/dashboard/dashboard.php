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

    $totalRooms = $db->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
    $availableRooms = $db->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetchColumn();
    $maintenanceRooms = $db->query("SELECT COUNT(*) FROM rooms WHERE status = 'maintenance'")->fetchColumn();
    $activeBookingsToday = $db->prepare("
        SELECT COUNT(*) FROM bookings
        WHERE booking_date = CURDATE() AND status = 'approved'
    ");
    $activeBookingsToday->execute();
    $activeBookingsToday = $activeBookingsToday->fetchColumn();

    $pendingBookings = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();

    $stmt = $db->prepare("
        SELECT r.name AS room, b.start_time, b.end_time, b.purpose, b.status
        FROM bookings b
        JOIN rooms r ON r.id = b.room_id
        WHERE b.booking_date = CURDATE() AND b.status IN ('approved', 'pending')
        ORDER BY b.start_time ASC
    ");
    $stmt->execute();
    $todaySchedule = $stmt->fetchAll();

    $todaySchedule = array_map(function($s) {
        return [
            'room'       => $s['room'],
            'start_time' => substr($s['start_time'], 0, 5),
            'end_time'   => substr($s['end_time'], 0, 5),
            'purpose'    => $s['purpose'],
            'status'     => $s['status'],
        ];
    }, $todaySchedule);

    jsonResponse(200, 'Data dashboard', [
        'total_rooms'          => (int)$totalRooms,
        'available_rooms'      => (int)$availableRooms,
        'maintenance_rooms'    => (int)$maintenanceRooms,
        'active_bookings_today'=> (int)$activeBookingsToday,
        'pending_bookings'     => (int)$pendingBookings,
        'today_schedule'       => $todaySchedule,
    ]);

} catch (PDOException $e) {
    jsonResponse(500, 'Terjadi kesalahan server: ' . $e->getMessage());
} catch (Exception $e) {
    jsonResponse(500, 'Terjadi kesalahan server');
}
