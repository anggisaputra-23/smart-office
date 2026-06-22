<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../middleware/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getConnection();

    switch ($method) {
        case 'GET':
            $user = authenticate();

            if (isset($_GET['id'])) {
                $stmt = $db->prepare("SELECT * FROM rooms WHERE id = ?");
                $stmt->execute([(int)$_GET['id']]);
                $room = $stmt->fetch();

                if (!$room) {
                    jsonResponse(404, 'Ruangan tidak ditemukan');
                }
                jsonResponse(200, 'Detail ruangan', $room);
            }

            $stmt = $db->query("SELECT * FROM rooms ORDER BY name ASC");
            $rooms = $stmt->fetchAll();

            jsonResponse(200, 'Daftar ruangan', $rooms);
            break;

        case 'POST':
            authenticate();
            requireAdmin();

            $data = json_decode(file_get_contents('php://input'), true) ?? [];

            $errors = validate($data, [
                'name'     => 'required|min:2|max:100|unique:rooms,name',
                'capacity' => 'required|integer|min:1',
                'facilities' => 'max:500',
                'status'   => 'required|in:available,maintenance',
            ]);

            if ($errors) {
                jsonResponse(422, 'Validasi gagal', null, $errors);
            }

            $stmt = $db->prepare("INSERT INTO rooms (name, capacity, facilities, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                (int)$data['capacity'],
                $data['facilities'] ?? null,
                $data['status'],
            ]);

            $roomId = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch();

            jsonResponse(201, 'Ruangan berhasil ditambahkan', $room);
            break;

        case 'PUT':
            authenticate();
            requireAdmin();

            $id = $_GET['id'] ?? null;
            if (!$id) {
                jsonResponse(400, 'Parameter id diperlukan');
            }

            $stmt = $db->prepare("SELECT * FROM rooms WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                jsonResponse(404, 'Ruangan tidak ditemukan');
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? [];

            $errors = validate($data, [
                'name'     => 'required|min:2|max:100|unique:rooms,name,' . $id,
                'capacity' => 'required|integer|min:1',
                'facilities' => 'max:500',
                'status'   => 'required|in:available,maintenance',
            ]);

            if ($errors) {
                jsonResponse(422, 'Validasi gagal', null, $errors);
            }

            $stmt = $db->prepare("UPDATE rooms SET name = ?, capacity = ?, facilities = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $data['name'],
                (int)$data['capacity'],
                $data['facilities'] ?? null,
                $data['status'],
                $id,
            ]);

            $stmt = $db->prepare("SELECT * FROM rooms WHERE id = ?");
            $stmt->execute([$id]);
            $room = $stmt->fetch();

            jsonResponse(200, 'Ruangan berhasil diperbarui', $room);
            break;

        case 'DELETE':
            authenticate();
            requireAdmin();

            $id = $_GET['id'] ?? null;
            if (!$id) {
                jsonResponse(400, 'Parameter id diperlukan');
            }

            $stmt = $db->prepare("SELECT * FROM rooms WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                jsonResponse(404, 'Ruangan tidak ditemukan');
            }

            $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND status IN ('pending','approved')");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                jsonResponse(409, 'Ruangan tidak bisa dihapus karena masih memiliki booking aktif');
            }

            $stmt = $db->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse(200, 'Ruangan berhasil dihapus');
            break;

        default:
            jsonResponse(405, 'Method tidak diizinkan');
    }

} catch (PDOException $e) {
    jsonResponse(500, 'Terjadi kesalahan server: ' . $e->getMessage());
} catch (Exception $e) {
    jsonResponse(500, 'Terjadi kesalahan server');
}
