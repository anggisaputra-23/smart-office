<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validator.php';

try {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    $errors = validate($data, [
        'name'                  => 'required|min:3|max:100',
        'email'                 => 'required|email|unique:users,email',
        'password'              => 'required|min:6',
        'password_confirmation' => 'required',
    ]);

    if ($errors) {
        jsonResponse(422, 'Validasi gagal', null, $errors);
    }

    if ($data['password'] !== $data['password_confirmation']) {
        jsonResponse(422, 'Validasi gagal', null, [
            'password_confirmation' => ['Konfirmasi password tidak cocok']
        ]);
    }

    $db = getConnection();

    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'karyawan')");
    $stmt->execute([
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_BCRYPT),
    ]);

    $userId = $db->lastInsertId();

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));

    $stmt = $db->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $token, $expires]);

    jsonResponse(201, 'Registrasi berhasil', [
        'user' => [
            'id'    => (int)$userId,
            'name'  => $data['name'],
            'email' => $data['email'],
            'role'  => 'karyawan',
        ],
        'token' => $token,
    ]);

} catch (PDOException $e) {
    jsonResponse(500, 'Terjadi kesalahan server: ' . $e->getMessage());
} catch (Exception $e) {
    jsonResponse(500, 'Terjadi kesalahan server');
}
