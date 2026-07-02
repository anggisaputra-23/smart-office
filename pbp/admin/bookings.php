<?php
/* Smart Office Management System - by Anggi Dwi Saputra */ $title = 'Approval Booking'; require_once __DIR__ . '/../includes/header.php'; ?>

<div class="mb-3">
    <div class="btn-group" role="group">
        <button class="btn btn-outline-secondary active" onclick="filterBookings('')">Semua</button>
        <button class="btn btn-outline-warning" onclick="filterBookings('pending')">⏳ Pending</button>
        <button class="btn btn-outline-success" onclick="filterBookings('approved')">✅ Disetujui</button>
        <button class="btn btn-outline-danger" onclick="filterBookings('rejected')">❌ Ditolak</button>
        <button class="btn btn-outline-secondary" onclick="filterBookings('completed')">Selesai</button>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h5><i class="bi bi-check2-square me-2"></i>Daftar Booking</h5>
        <span class="badge bg-light text-dark" id="bookingCount">0 booking</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Ruangan</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Keperluan</th>
                    <th>Status</th>
                    <th style="width:140px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="bookingTable">
                <tr><td colspan="7" class="text-center text-muted py-4">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectBookingId">
                <div class="mb-3">
                    <label class="form-label">Alasan Penolakan</label>
                    <textarea class="form-control" id="rejectReason" rows="3" placeholder="Jelaskan mengapa booking ditolak..."></textarea>
                    <div class="invalid-feedback">Alasan penolakan wajib diisi</div>
                </div>
                <div id="rejectError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="bi bi-x-circle me-1"></i>Tolak Booking
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentFilter = '';
let rejectModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    rejectModalInstance = new bootstrap.Modal(document.getElementById('rejectModal'));
    loadBookings();
});

function filterBookings(status) {
    currentFilter = status;
    document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    loadBookings();
}

async function loadBookings() {
    const tbody = document.getElementById('bookingTable');
    try {
        const params = {};
        if (currentFilter) params.status = currentFilter;
        const res = await API.getBookings(params);

        document.getElementById('bookingCount').textContent = res.data.length + ' booking';

        if (res.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data booking</td></tr>';
            return;
        }

        tbody.innerHTML = res.data.map(b => {
            let actions = '-';
            if (b.status === 'pending') {
                actions = `
                    <button class="btn btn-sm btn-success btn-sm-custom me-1" onclick="approveBooking(${b.id})">
                        <i class="bi bi-check-lg"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-sm-custom" onclick="openReject(${b.id})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                `;
            } else if (b.status === 'approved') {
                actions = `<span class="text-success" style="font-size:.8rem;"><i class="bi bi-check-circle"></i> Disetujui</span>`;
            } else if (b.status === 'rejected') {
                actions = `<span class="text-danger" style="font-size:.8rem;" title="${b.admin_notes || ''}"><i class="bi bi-x-circle"></i> Ditolak</span>`;
            } else {
                actions = `<span class="text-muted" style="font-size:.8rem;"><i class="bi bi-check-all"></i> Selesai</span>`;
            }

            return `
                <tr>
                    <td><strong>${b.user_name}</strong></td>
                    <td>${b.room_name}</td>
                    <td>${API.formatDate(b.booking_date)}</td>
                    <td>${b.start_time} - ${b.end_time}</td>
                    <td style="max-width:200px;white-space:normal;">${b.purpose || '-'}</td>
                    <td>${API.statusBadge(b.status)}</td>
                    <td>${actions}</td>
                </tr>
            `;
        }).join('');
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data</td></tr>';
    }
}

async function approveBooking(id) {
    if (!confirm('Setujui booking ini?')) return;
    try {
        await API.updateBooking(id, { status: 'approved' });
        showSuccess('Booking telah disetujui');
        loadBookings();
    } catch (err) {
        showError(err.data?.message || 'Gagal menyetujui booking');
    }
}

function openReject(id) {
    document.getElementById('rejectBookingId').value = id;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectReason').classList.remove('is-invalid');
    document.getElementById('rejectError').classList.add('d-none');
    rejectModalInstance.show();
}

async function confirmReject() {
    const id = document.getElementById('rejectBookingId').value;
    const reason = document.getElementById('rejectReason').value.trim();

    if (!reason) {
        document.getElementById('rejectReason').classList.add('is-invalid');
        return;
    }

    try {
        await API.updateBooking(id, { status: 'rejected', admin_notes: reason });
        showSuccess('Booking telah ditolak');
        rejectModalInstance.hide();
        loadBookings();
    } catch (err) {
        document.getElementById('rejectError').textContent = err.data?.message || 'Gagal menolak booking';
        document.getElementById('rejectError').classList.remove('d-none');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
