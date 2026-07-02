<?php
/* Smart Office Management System - by Anggi Dwi Saputra */ $title = 'Jadwal Ruangan'; require_once __DIR__ . '/includes/header.php'; ?>

<div class="row g-3 mb-4 align-items-end">
    <div class="col-md-3">
        <label class="form-label fw-semibold">Tanggal</label>
        <input type="date" class="form-control" id="scheduleDate" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Ruangan</label>
        <select class="form-select" id="roomFilter">
            <option value="">Semua Ruangan</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label fw-semibold">Tampilan</label>
        <select class="form-select" id="viewMode">
            <option value="day">Harian</option>
            <option value="week">Mingguan</option>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100" onclick="loadSchedule()">
            <i class="bi bi-search me-1"></i>Tampilkan
        </button>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h5><i class="bi bi-calendar-week me-2"></i><span id="scheduleTitle">Jadwal Hari Ini</span></h5>
    </div>
    <div class="table-responsive">
        <table class="table schedule-timeline" id="scheduleTable">
            <tbody>
                <tr><td class="text-center text-muted py-5">Pilih tanggal untuk menampilkan jadwal</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
async function loadRoomsFilter() {
    try {
        const res = await API.getRooms();
        const select = document.getElementById('roomFilter');
        res.data.forEach(room => {
            const opt = document.createElement('option');
            opt.value = room.id;
            opt.textContent = room.name;
            select.appendChild(opt);
        });
    } catch (err) {}
}

async function loadSchedule() {
    const date = document.getElementById('scheduleDate').value;
    const roomId = document.getElementById('roomFilter').value;
    const view = document.getElementById('viewMode').value;

    if (!date) { showError('Pilih tanggal terlebih dahulu'); return; }

    const params = { date, view };
    if (roomId) params.room_id = roomId;

    try {
        const res = await API.getSchedule(params);
        if (view === 'week') {
            renderWeekView(res.data);
        } else {
            renderDayView(res.data);
        }
    } catch (err) {
        document.getElementById('scheduleTable').innerHTML =
            '<tr><td class="text-center text-danger py-5">Gagal memuat jadwal</td></tr>';
    }
}

function renderDayView(data) {
    const schedule = data.schedule || [];
    const date = data.date;
    const title = document.getElementById('scheduleTitle');
    title.textContent = 'Jadwal - ' + new Date(date + 'T00:00:00').toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    const hours = [];
    for (let h = 7; h <= 18; h++) {
        hours.push(String(h).padStart(2, '0') + ':00');
    }

    let html = '<thead><tr><th style="width:80px;">Waktu</th>';
    schedule.forEach(room => {
        html += `<th>${room.room.name}</th>`;
    });
    html += '</tr></thead><tbody>';

    hours.forEach(hour => {
        html += `<tr><td class="fw-bold" style="font-size:.8rem;">${hour}</td>`;
        schedule.forEach(room => {
            const booking = room.bookings.find(b => b.start_time <= hour && b.end_time > hour);
            if (booking) {
                const cls = booking.status === 'approved' ? 'approved' : 'pending';
                html += `<td>
                    <div class="booking-cell ${cls}">
                        <strong>${booking.purpose || '-'}</strong><br>
                        <small>${booking.start_time}-${booking.end_time}</small>
                        <small class="d-block">👤 ${booking.user}</small>
                        ${API.statusBadge(booking.status)}
                    </div>
                </td>`;
            } else {
                html += `<td class="text-muted" style="font-size:.8rem;">—</td>`;
            }
        });
        html += '</tr>';
    });

    html += '</tbody>';
    document.getElementById('scheduleTable').innerHTML = html;
}

function renderWeekView(data) {
    const schedule = data.schedule || {};
    const title = document.getElementById('scheduleTitle');
    title.textContent = `Jadwal Pekan ${data.week_start} - ${data.week_end}`;

    const days = Object.keys(schedule).sort();

    if (days.length === 0) {
        document.getElementById('scheduleTable').innerHTML =
            '<tr><td class="text-center text-muted py-5">Tidak ada jadwal pekan ini</td></tr>';
        return;
    }

    let html = '<thead><tr><th style="width:80px;">Waktu</th>';
    days.forEach(day => {
        const d = new Date(day + 'T00:00:00');
        const label = d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' });
        html += `<th>${label}</th>`;
    });
    html += '</tr></thead><tbody>';

    const hours = [];
    for (let h = 7; h <= 18; h++) {
        hours.push(String(h).padStart(2, '0') + ':00');
    }

    hours.forEach(hour => {
        html += `<tr><td class="fw-bold" style="font-size:.8rem;">${hour}</td>`;
        days.forEach(day => {
            const dayBookings = schedule[day] || [];
            const booking = dayBookings.find(b => b.start_time <= hour && b.end_time > hour);
            if (booking) {
                const cls = booking.status === 'approved' ? 'approved' : 'pending';
                html += `<td>
                    <div class="booking-cell ${cls}">
                        <strong>${booking.room_name || booking.purpose || '-'}</strong><br>
                        <small>${booking.start_time}-${booking.end_time}</small>
                        ${API.statusBadge(booking.status)}
                    </div>
                </td>`;
            } else {
                html += `<td class="text-muted" style="font-size:.8rem;">—</td>`;
            }
        });
        html += '</tr>';
    });

    html += '</tbody>';
    document.getElementById('scheduleTable').innerHTML = html;
}

loadRoomsFilter();
loadSchedule();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
