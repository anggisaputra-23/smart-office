<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Smart Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/logo.png">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-card text-center">
        <div class="login-logo">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Smart Office" height="96" style="margin-bottom:.5rem;">
            <h3>Sampai Jumpa!</h3>
            <p>Anda akan dialihkan ke halaman login...</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/api.js"></script>
    <script>API.logout();</script>
</body>
</html>
