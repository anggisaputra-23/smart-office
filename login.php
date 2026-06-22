<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
$title = 'Login';
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Office Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/logo.png">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Smart Office" height="96" style="margin-bottom:.5rem;">
            <h3>Smart Office</h3>
            <p>Management System</p>
        </div>

        <form id="loginForm" novalidate>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="email" placeholder="admin@office.com" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" id="password" placeholder="password" required>
            </div>
            <div id="loginError" class="alert alert-danger d-none"></div>
            <button type="submit" class="btn btn-primary w-100 py-2" id="loginBtn">
                <span id="loginText">Masuk</span>
                <span id="loginSpinner" class="spinner-border spinner-border-sm d-none"></span>
            </button>
        </form>

        <div class="text-center mt-3">
            <p class="text-muted" style="font-size:.85rem;">
                Belum punya akun? <a href="#" onclick="showRegister()">Daftar</a>
            </p>
        </div>

        <div class="mt-3 p-3" style="background:#f8fafc;border-radius:8px;font-size:.8rem;color:#64748b;">
            <strong>Demo Akun:</strong><br>
            Admin: admin@office.com / password<br>
            Karyawan: karyawan@office.com / password
        </div>
    </div>

    <div class="login-card d-none" id="registerCard">
        <div class="login-logo">
            <div class="logo-icon">📝</div>
            <h3>Daftar Akun</h3>
            <p>Buat akun karyawan baru</p>
        </div>

        <form id="registerForm" novalidate>
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="regName" placeholder="Nama Anda" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="regEmail" placeholder="email@office.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" id="regPassword" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="regPasswordConfirm" placeholder="Ulangi password" required>
            </div>
            <div id="registerError" class="alert alert-danger d-none"></div>
            <button type="submit" class="btn btn-primary w-100 py-2" id="registerBtn">
                <span id="registerText">Daftar</span>
                <span id="registerSpinner" class="spinner-border spinner-border-sm d-none"></span>
            </button>
        </form>

        <div class="text-center mt-3">
            <p class="text-muted" style="font-size:.85rem;">
                Sudah punya akun? <a href="#" onclick="showLogin()">Masuk</a>
            </p>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/api.js"></script>
<script>
const token = API.getToken();
if (token) window.location.href = 'dashboard.php';

const expired = new URLSearchParams(window.location.search).get('expired');
if (expired) showError('Sesi berakhir. Silakan login kembali.');

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const btn = document.getElementById('loginBtn');
    const errorEl = document.getElementById('loginError');

    if (!email || !password) {
        errorEl.textContent = 'Email dan password wajib diisi';
        errorEl.classList.remove('d-none');
        return;
    }

    btn.disabled = true;
    document.getElementById('loginText').textContent = 'Memproses...';
    document.getElementById('loginSpinner').classList.remove('d-none');
    errorEl.classList.add('d-none');

    try {
        const res = await API.login(email, password);
        API.setToken(res.data.token);
        API.setUser(res.data.user);
        window.location.href = 'dashboard.php';
    } catch (err) {
        errorEl.textContent = err.data?.message || 'Login gagal. Periksa email dan password.';
        errorEl.classList.remove('d-none');
        btn.disabled = false;
        document.getElementById('loginText').textContent = 'Masuk';
        document.getElementById('loginSpinner').classList.add('d-none');
    }
});

document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('regName').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value;
    const confirm = document.getElementById('regPasswordConfirm').value;
    const btn = document.getElementById('registerBtn');
    const errorEl = document.getElementById('registerError');

    if (!name || !email || !password || !confirm) {
        errorEl.textContent = 'Semua field wajib diisi';
        errorEl.classList.remove('d-none');
        return;
    }

    if (password.length < 6) {
        errorEl.textContent = 'Password minimal 6 karakter';
        errorEl.classList.remove('d-none');
        return;
    }

    if (password !== confirm) {
        errorEl.textContent = 'Konfirmasi password tidak cocok';
        errorEl.classList.remove('d-none');
        return;
    }

    btn.disabled = true;
    document.getElementById('registerText').textContent = 'Memproses...';
    document.getElementById('registerSpinner').classList.remove('d-none');
    errorEl.classList.add('d-none');

    try {
        const res = await API.register({ name, email, password, password_confirmation: confirm });
        API.setToken(res.data.token);
        API.setUser(res.data.user);
        showSuccess('Registrasi berhasil!');
        setTimeout(() => window.location.href = 'dashboard.php', 500);
    } catch (err) {
        if (err.data?.errors) {
            const msgs = Object.values(err.data.errors).flat().join('<br>');
            errorEl.innerHTML = msgs;
        } else {
            errorEl.textContent = err.data?.message || 'Registrasi gagal';
        }
        errorEl.classList.remove('d-none');
        btn.disabled = false;
        document.getElementById('registerText').textContent = 'Daftar';
        document.getElementById('registerSpinner').classList.add('d-none');
    }
});

function showRegister() {
    document.querySelector('.login-card').classList.add('d-none');
    document.getElementById('registerCard').classList.remove('d-none');
}

function showLogin() {
    document.getElementById('registerCard').classList.add('d-none');
    document.querySelector('.login-card').classList.remove('d-none');
}
</script>
</body>
</html>
