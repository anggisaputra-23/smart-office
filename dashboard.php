<?php
/* Smart Office Management System - by Anggi Dwi Saputra */ $title = 'Dashboard'; require_once __DIR__ . '/includes/header.php'; ?>

<div class="row g-4 mb-4" id="statsRow">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;"><i class="bi bi-door-open-fill" style="color:#2563eb;"></i></div>
            <div class="stat-value" id="statTotalRooms">0</div>
            <div class="stat-label">Total Ruangan</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;"><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></div>
            <div class="stat-value" id="statActiveBookings">0</div>
            <div class="stat-label">Booking Aktif Hari Ini</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;"><i class="bi bi-building-fill" style="color:#6366f1;"></i></div>
            <div class="stat-value" id="statAvailableRooms">0</div>
            <div class="stat-label">Ruangan Tersedia</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;"><i class="bi bi-clock-fill" style="color:#f59e0b;"></i></div>
            <div class="stat-value" id="statPendingBookings">0</div>
            <div class="stat-label">Pending Booking</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="table-container">
            <div class="table-header">
                <h5><i class="bi bi-calendar-day me-2"></i>Jadwal Hari Ini</h5>
                <span style="font-size:.85rem;color:#64748b;" id="todayDate"></span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ruangan</th>
                            <th>Waktu</th>
                            <th>Keperluan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="todaySchedule">
                        <tr><td colspan="4" class="text-center text-muted py-4">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="table-container">
            <div class="table-header">
                <h5><i class="bi bi-lightning-fill me-2"></i>Aksi Cepat</h5>
            </div>
            <div class="p-3">
                <a href="rooms.php" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-door-open-fill me-2"></i>Cari Ruangan
                </a>
                <a href="booking.php" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-calendar-plus-fill me-2"></i>Booking Baru
                </a>
                <a href="schedule.php" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-calendar-week-fill me-2"></i>Lihat Jadwal
                </a>
                <a href="admin/bookings.php" class="btn btn-warning w-100 admin-only" style="display:none" id="approvalBtn">
                    <i class="bi bi-check2-square me-2"></i>Approval Booking
                </a>
            </div>
        </div>
    </div>
</div>

<script>
async function loadDashboard() {
    try {
        const res = await API.getDashboard();
        const d = res.data;

        document.getElementById('statTotalRooms').textContent = d.total_rooms;
        document.getElementById('statActiveBookings').textContent = d.active_bookings_today;
        document.getElementById('statAvailableRooms').textContent = d.available_rooms;
        document.getElementById('statPendingBookings').textContent = d.pending_bookings;

        document.getElementById('todayDate').textContent = new Date().toLocaleDateString('id-ID', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

        const tbody = document.getElementById('todaySchedule');

        if (d.today_schedule.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Tidak ada jadwal hari ini</td></tr>';
            return;
        }

        tbody.innerHTML = d.today_schedule.map(item => `
            <tr>
                <td><strong>${item.room}</strong></td>
                <td>${item.start_time} - ${item.end_time}</td>
                <td>${item.purpose || '-'}</td>
                <td>${API.statusBadge(item.status)}</td>
            </tr>
        `).join('');
    } catch (err) {
        document.getElementById('todaySchedule').innerHTML =
            '<tr><td colspan="4" class="text-center text-danger py-4">Gagal memuat data</td></tr>';
    }
}

loadDashboard();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
