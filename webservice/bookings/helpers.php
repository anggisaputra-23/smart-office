<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
function checkScheduleConflict(
    PDO    $db,
    int    $roomId,
    string $date,
    string $startTime,
    string $endTime,
    ?int   $excludeBookingId = null
): array {
    $sql = "SELECT id, start_time, end_time, status
            FROM bookings
            WHERE room_id = ?
              AND booking_date = ?
              AND status IN ('pending', 'approved')
              AND start_time < ?
              AND end_time > ?";

    $params = [$roomId, $date, $endTime, $startTime];

    if ($excludeBookingId) {
        $sql .= " AND id != ?";
        $params[] = $excludeBookingId;
    }

    $sql .= " ORDER BY start_time ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function isValidStatusTransition(string $currentStatus, string $newStatus): bool {
    $allowed = [
        'pending'   => ['approved', 'rejected'],
        'approved'  => ['completed'],
        'rejected'  => [],
        'completed' => [],
    ];
    return in_array($newStatus, $allowed[$currentStatus] ?? []);
}

function autoCompleteExpiredBookings(PDO $db): void {
    $stmt = $db->prepare("
        UPDATE bookings
        SET status = 'completed', updated_at = NOW()
        WHERE status = 'approved'
          AND (booking_date < CURDATE()
               OR (booking_date = CURDATE() AND end_time <= CURTIME()))
    ");
    $stmt->execute();
}

function formatBookingRow(array $b): array {
    return [
        'id'           => (int)$b['id'],
        'user_id'      => (int)$b['user_id'],
        'user_name'    => $b['user_name'] ?? null,
        'room_id'      => (int)$b['room_id'],
        'room_name'    => $b['room_name'] ?? null,
        'booking_date' => $b['booking_date'],
        'start_time'   => substr($b['start_time'], 0, 5),
        'end_time'     => substr($b['end_time'], 0, 5),
        'purpose'      => $b['purpose'],
        'status'       => $b['status'],
        'admin_notes'  => $b['admin_notes'],
        'created_at'   => $b['created_at'],
    ];
}
