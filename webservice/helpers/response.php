<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
function jsonResponse(int $code, string $message, mixed $data = null, mixed $errors = null): void {
    http_response_code($code);
    header('Content-Type: application/json');
    $body = [
        'status' => ($code >= 200 && $code < 300) ? 'success' : 'error',
        'message' => $message,
    ];
    if ($data !== null) {
        $body['data'] = $data;
    }
    if ($errors !== null) {
        $body['errors'] = $errors;
    }
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
