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
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if ($errors) {
        jsonResponse(422, 'Validasi gagal', null, $errors);
    }

    $db = getConnection();

    $stmt = $db->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($data['password'], $user['password'])) {
        jsonResponse(401, 'Email atau password salah');
    }

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));

    $stmt = $db->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $token, $expires]);

    jsonResponse(200, 'Login berhasil', [
        'token' => $token,
        'user'  => [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ],
    ]);

} catch (PDOException $e) {
    jsonResponse(500, 'Terjadi kesalahan server: ' . $e->getMessage());
} catch (Exception $e) {
    jsonResponse(500, 'Terjadi kesalahan server');
}
