/* Smart Office Management System — by Anggi Dwi Saputra */
if (typeof console !== 'undefined') console.log('🏢 SOMS — Anggi Dwi Saputra');

const API = {
    BASE: 'http://localhost/smart-office/api',

    getToken() {
        return localStorage.getItem('soms_token');
    },

    setToken(token) {
        localStorage.setItem('soms_token', token);
    },

    clearToken() {
        localStorage.removeItem('soms_token');
    },

    getUser() {
        const raw = localStorage.getItem('soms_user');
        return raw ? JSON.parse(raw) : null;
    },

    setUser(user) {
        localStorage.setItem('soms_user', JSON.stringify(user));
    },

    clearUser() {
        localStorage.removeItem('soms_user');
    },

    isAdmin() {
        const u = this.getUser();
        return u && u.role === 'admin';
    },

    logout() {
        this.request('POST', '/auth/logout.php').catch(() => {});
        this.clearToken();
        this.clearUser();
        window.location.href = 'login.php';
    },

    async request(method, endpoint, data = null) {
        const options = {
            method,
            headers: { 'Content-Type': 'application/json' },
        };

        const token = this.getToken();
        if (token) {
            options.headers['Authorization'] = `Bearer ${token}`;
        }

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const res = await fetch(`${this.BASE}${endpoint}`, options);
            const json = await res.json();

            if (!res.ok) {
                const err = new Error(json.message || 'Terjadi kesalahan');
                err.status = res.status;
                err.data = json;
                throw err;
            }

            return json;
        } catch (err) {
            if (err.status === 401) {
                const currentPage = window.location.pathname.split('/').pop();
                if (currentPage !== 'login.php') {
                    this.clearToken();
                    this.clearUser();
                    window.location.href = 'login.php?expired=1';
                }
            }
            throw err;
        }
    },

    login(email, password) {
        return this.request('POST', '/auth/login.php', { email, password });
    },

    register(data) {
        return this.request('POST', '/auth/register.php', data);
    },

    me() {
        return this.request('GET', '/auth/me.php');
    },

    getRooms(id) {
        const qs = id ? `?id=${id}` : '';
        return this.request('GET', `/rooms/rooms.php${qs}`);
    },

    createRoom(data) {
        return this.request('POST', '/rooms/rooms.php', data);
    },

    updateRoom(id, data) {
        return this.request('PUT', `/rooms/rooms.php?id=${id}`, data);
    },

    deleteRoom(id) {
        return this.request('DELETE', `/rooms/rooms.php?id=${id}`);
    },

    getBookings(params = {}) {
        const qs = Object.entries(params)
            .filter(([_, v]) => v !== undefined && v !== null && v !== '')
            .map(([k, v]) => `${k}=${encodeURIComponent(v)}`)
            .join('&');
        return this.request('GET', `/bookings/bookings.php${qs ? '?' + qs : ''}`);
    },

    createBooking(data) {
        return this.request('POST', '/bookings/bookings.php', data);
    },

    updateBooking(id, data) {
        return this.request('PATCH', `/bookings/bookings.php?id=${id}`, data);
    },

    cancelBooking(id) {
        return this.request('DELETE', `/bookings/bookings.php?id=${id}`);
    },

    getSchedule(params = {}) {
        const qs = Object.entries(params)
            .filter(([_, v]) => v !== undefined && v !== null && v !== '')
            .map(([k, v]) => `${k}=${encodeURIComponent(v)}`)
            .join('&');
        return this.request('GET', `/schedule/schedule.php${qs ? '?' + qs : ''}`);
    },

    getDashboard() {
        return this.request('GET', '/dashboard/dashboard.php');
    },

    statusBadge(status) {
        const map = {
            pending: '<span class="badge badge-status badge-pending">Pending</span>',
            approved: '<span class="badge badge-status badge-approved">Disetujui</span>',
            rejected: '<span class="badge badge-status badge-rejected">Ditolak</span>',
            completed: '<span class="badge badge-status badge-completed">Selesai</span>',
            available: '<span class="badge badge-status badge-available">Tersedia</span>',
            maintenance: '<span class="badge badge-status badge-maintenance">Perawatan</span>',
        };
        return map[status] || status;
    },

    formatDate(dateStr) {
        const d = new Date(dateStr + 'T00:00:00');
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    },

    formatTime(timeStr) {
        return timeStr ? timeStr.substring(0, 5) : '';
    },
};

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
    const icons = { success: 'bi-check-circle', error: 'bi-x-circle', warning: 'bi-exclamation-circle', info: 'bi-info-circle' };

    const toast = document.createElement('div');
    toast.className = 'toast toast-custom';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
        <div class="toast-body">
            <i class="bi ${icons[type] || icons.info}" style="color: ${colors[type] || colors.info}; font-size: 1.25rem;"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    container.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast, { delay: type === 'error' ? 6000 : 4000 });
    bsToast.show();

    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function showError(message) {
    showToast(message, 'error');
}

function showSuccess(message) {
    showToast(message, 'success');
}

function showFieldError(fieldId, message) {
    const el = document.getElementById(fieldId);
    if (el) {
        el.classList.add('is-invalid');
        const feedback = el.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = message;
        } else {
            const div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = message;
            el.parentElement.appendChild(div);
        }
    }
}

function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
}

async function loadUserInfo() {
    const user = API.getUser();
    if (!user) return;

    const nameEl = document.getElementById('userName');
    const roleEl = document.getElementById('userRole');
    const avatarEl = document.getElementById('userAvatar');

    if (nameEl) nameEl.textContent = user.name;
    if (roleEl) roleEl.textContent = user.role === 'admin' ? 'Administrator' : 'Karyawan';
    if (avatarEl) avatarEl.textContent = user.name.charAt(0).toUpperCase();

    const adminMenus = document.querySelectorAll('.admin-only');
    adminMenus.forEach(el => {
        el.style.display = user.role === 'admin' ? '' : 'none';
    });

    const navUser = document.getElementById('navUserName');
    if (navUser) navUser.textContent = user.name;
    const navRole = document.getElementById('navUserRole');
    if (navRole) navRole.textContent = user.role === 'admin' ? 'Administrator' : 'Karyawan';
}

document.addEventListener('DOMContentLoaded', () => {
    loadUserInfo();
});
