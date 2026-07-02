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
require_once __DIR__ . '/../middleware/auth.php';

try {
    $user = authenticate();
    $db = getConnection();

    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    preg_match('/^Bearer\s(.+)$/', $auth, $matches);
    $token = $matches[1];

    $stmt = $db->prepare("DELETE FROM user_tokens WHERE token = ?");
    $stmt->execute([$token]);

    jsonResponse(200, 'Logout berhasil');

} catch (PDOException $e) {
    jsonResponse(500, 'Terjadi kesalahan server');
} catch (Exception $e) {
    jsonResponse(500, 'Terjadi kesalahan server');
}
