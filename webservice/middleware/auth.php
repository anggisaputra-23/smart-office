<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

function authenticate(): array {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (!preg_match('/^Bearer\s(.+)$/', $auth, $matches)) {
        jsonResponse(401, 'Token tidak ditemukan. Gunakan header: Authorization: Bearer <token>');
    }

    $db = getConnection();
    $stmt = $db->prepare("
        SELECT u.id, u.name, u.email, u.role
        FROM user_tokens t
        JOIN users u ON u.id = t.user_id
        WHERE t.token = ? AND t.expires_at > NOW()
    ");
    $stmt->execute([$matches[1]]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(401, 'Token tidak valid atau sudah expired. Silakan login ulang.');
    }

    return $user;
}

function requireAdmin(): void {
    $user = $GLOBALS['auth_user'] ?? authenticate();
    if ($user['role'] !== 'admin') {
        jsonResponse(403, 'Akses ditolak. Hanya admin yang dapat mengakses fitur ini.');
    }
}
