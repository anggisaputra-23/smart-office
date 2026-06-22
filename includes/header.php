<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Anggi Dwi Saputra">
    <title><?= $title ?? 'Smart Office Management' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/logo.png">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script src="<?= BASE_URL ?>/assets/js/api.js"></script>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<?php if (basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
<div class="d-flex">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="SOMS" height="48" style="border-radius:6px;">
            <div style="line-height:1.1;">
                <div style="font-size:.75rem;font-weight:700;color:#f1f5f9;letter-spacing:.02em;">Smart Office</div>
                <div style="font-size:.65rem;font-weight:500;color:#94a3b8;letter-spacing:.01em;">Management System</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">Menu Utama</div>
            <a href="<?= BASE_URL ?>/dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>/rooms.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'rooms.php' ? 'active' : '' ?>">
                <i class="bi bi-door-open-fill"></i> Ruangan
            </a>
            <a href="<?= BASE_URL ?>/booking.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'booking.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar-plus-fill"></i> Booking
            </a>
            <a href="<?= BASE_URL ?>/schedule.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'schedule.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar-week-fill"></i> Jadwal
            </a>

            <div class="nav-section admin-only" style="display:none">Administrasi</div>
            <a href="<?= BASE_URL ?>/admin/rooms.php" class="nav-item admin-only" style="display:none">
                <i class="bi bi-gear-fill"></i> Kelola Ruangan
            </a>
            <a href="<?= BASE_URL ?>/admin/bookings.php" class="nav-item admin-only" style="display:none">
                <i class="bi bi-check2-square"></i> Approval Booking
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="d-flex align-items-center gap-2">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:.85rem;" id="userAvatar">A</div>
                <div>
                    <div class="user-name" id="navUserName">User</div>
                    <div class="user-role" id="navUserRole">-</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="btn btn-sm btn-light sidebar-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-title"><?= $title ?? 'Dashboard' ?></h1>
            </div>
            <div class="topbar-right">
                <span style="font-size:.85rem;color:#64748b;" id="userName"></span>
                <span class="badge bg-light text-dark ms-1" id="userRole" style="font-size:.7rem;"></span>
                <button class="btn btn-sm btn-outline-danger ms-2" onclick="API.logout()">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </header>
        <div class="content-wrapper">
<?php endif; ?>
<script>
function toggleSidebar() {
  const s = document.getElementById('sidebar');
  const m = document.querySelector('.main-content');
  if (window.innerWidth < 769) {
    s.classList.toggle('show');
  } else {
    s.classList.toggle('collapsed');
    m.classList.toggle('expanded');
    localStorage.setItem('sidebarCollapsed', s.classList.contains('collapsed'));
  }
}
document.addEventListener('DOMContentLoaded', function() {
  if (window.innerWidth >= 769 && localStorage.getItem('sidebarCollapsed') === 'true') {
    document.getElementById('sidebar').classList.add('collapsed');
    document.querySelector('.main-content').classList.add('expanded');
  }
});
</script>
