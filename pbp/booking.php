<?php
/* Smart Office Management System - by Anggi Dwi Saputra */ $title = 'Booking Ruangan'; require_once __DIR__ . '/includes/header.php'; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="form-container">
            <h5 class="mb-3"><i class="bi bi-calendar-plus-fill me-2"></i>Form Booking</h5>
            <form id="bookingForm" novalidate>
                <div class="mb-3">
                    <label class="form-label">Ruangan</label>
                    <select class="form-select" id="room_id" required>
                        <option value="">Pilih ruangan...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="booking_date" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control" id="start_time" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control" id="end_time" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keperluan</label>
                    <textarea class="form-control" id="purpose" rows="2" placeholder="Contoh: Rapat tim pengembangan"></textarea>
                </div>
                <div id="bookingError" class="alert alert-danger d-none"></div>
                <div id="bookingConflict" class="alert alert-warning d-none"></div>
                <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn">
                    <i class="bi bi-send-fill me-1"></i> Ajukan Booking
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="table-container">
            <div class="table-header">
                <h5><i class="bi bi-clock-history me-2"></i>Riwayat Booking Saya</h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" onclick="loadHistory()">Semua</button>
                    <button class="btn btn-outline-warning" onclick="loadHistory('pending')">Pending</button>
                    <button class="btn btn-outline-success" onclick="loadHistory('approved')">Disetujui</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ruangan</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="bookingHistory">
                        <tr><td colspan="5" class="text-center text-muted py-4">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let selectedRoomId = new URLSearchParams(window.location.search).get('room_id');

async function loadRooms() {
    try {
        const res = await API.getRooms();
        const select = document.getElementById('room_id');
        select.innerHTML = '<option value="">Pilih ruangan...</option>';
        res.data.forEach(room => {
            const opt = document.createElement('option');
            opt.value = room.id;
            opt.textContent = `${room.name} (kap. ${room.capacity})`;
            if (room.status !== 'available') opt.disabled = true;
            select.appendChild(opt);
        });
        if (selectedRoomId) select.value = selectedRoomId;
    } catch (err) {
        showError('Gagal memuat daftar ruangan');
    }
}

async function loadHistory(status = '') {
    const tbody = document.getElementById('bookingHistory');
    try {
        const params = {};
        if (status) params.status = status;
        const res = await API.getBookings(params);

        if (res.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada booking</td></tr>';
            return;
        }

        tbody.innerHTML = res.data.map(b => `
            <tr>
                <td><strong>${b.room_name}</strong></td>
                <td>${API.formatDate(b.booking_date)}</td>
                <td>${b.start_time} - ${b.end_time}</td>
                <td>${API.statusBadge(b.status)}</td>
                <td>
                    ${b.status === 'pending' ? `<button class="btn btn-sm btn-outline-danger btn-sm-custom" onclick="cancelBooking(${b.id})">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>` : '-'}
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Gagal memuat data</td></tr>';
    }
}

async function cancelBooking(id) {
    if (!confirm('Yakin ingin membatalkan booking ini?')) return;
    try {
        await API.cancelBooking(id);
        showSuccess('Booking berhasil dibatalkan');
        loadHistory();
    } catch (err) {
        showError(err.data?.message || 'Gagal membatalkan booking');
    }
}

document.getElementById('bookingForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();

    const data = {
        room_id: parseInt(document.getElementById('room_id').value),
        booking_date: document.getElementById('booking_date').value,
        start_time: document.getElementById('start_time').value,
        end_time: document.getElementById('end_time').value,
        purpose: document.getElementById('purpose').value.trim(),
    };

    if (!data.room_id) { showFieldError('room_id', 'Pilih ruangan'); return; }
    if (!data.booking_date) { showFieldError('booking_date', 'Pilih tanggal'); return; }
    if (!data.start_time) { showFieldError('start_time', 'Pilih jam mulai'); return; }
    if (!data.end_time) { showFieldError('end_time', 'Pilih jam selesai'); return; }
    if (data.end_time <= data.start_time) { showFieldError('end_time', 'Jam selesai harus setelah jam mulai'); return; }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

    document.getElementById('bookingError').classList.add('d-none');
    document.getElementById('bookingConflict').classList.add('d-none');

    try {
        const res = await API.createBooking(data);
        showSuccess(res.message);
        document.getElementById('bookingForm').reset();
        document.getElementById('booking_date').min = new Date().toISOString().split('T')[0];
        if (selectedRoomId) document.getElementById('room_id').value = selectedRoomId;
        loadHistory();
    } catch (err) {
        if (err.status === 409) {
            const conflict = err.data?.data?.conflicts;
            let msg = '<strong>' + (err.data?.message || 'Ruangan sudah dibooking') + '</strong>';
            if (conflict && conflict.length > 0) {
                msg += '<hr class="my-2"><small>Booking yang bentrok:</small><ul class="mb-0 mt-1" style="font-size:.85rem;">';
                conflict.forEach(c => {
                    msg += `<li>${c.start_time} - ${c.end_time} ${API.statusBadge(c.status)}</li>`;
                });
                msg += '</ul>';
            }
            document.getElementById('bookingConflict').innerHTML = msg;
            document.getElementById('bookingConflict').classList.remove('d-none');
        } else if (err.status === 422 && err.data?.errors) {
            Object.entries(err.data.errors).forEach(([field, msgs]) => {
                showFieldError(field, msgs[0]);
            });
        } else {
            document.getElementById('bookingError').textContent = err.data?.message || 'Gagal membuat booking';
            document.getElementById('bookingError').classList.remove('d-none');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Ajukan Booking';
    }
});

document.getElementById('booking_date').valueAsDate = new Date();
loadRooms();
loadHistory();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
