<?php
/* Smart Office Management System - by Anggi Dwi Saputra */ $title = 'Daftar Ruangan'; require_once __DIR__ . '/includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Pilih ruangan untuk melakukan booking</p>
    <div>
        <input type="text" class="form-control form-control-sm" id="searchRoom" placeholder="Cari ruangan..." style="width:200px;">
    </div>
</div>

<div class="row g-4" id="roomList">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Memuat data ruangan...</p>
    </div>
</div>

<script>
async function loadRooms() {
    try {
        const res = await API.getRooms();
        const container = document.getElementById('roomList');
        container.innerHTML = res.data.map(room => `
            <div class="col-xl-4 col-md-6 room-item">
                <div class="room-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="room-name">${room.name}</div>
                        ${API.statusBadge(room.status)}
                    </div>
                    <div class="room-detail"><strong>Kapasitas:</strong> ${room.capacity} orang</div>
                    <div class="room-detail"><strong>Fasilitas:</strong> ${room.facilities || '-'}</div>
                    <a href="booking.php?room_id=${room.id}" class="btn btn-primary mt-3 w-100 ${room.status !== 'available' ? 'disabled' : ''}">
                        <i class="bi bi-calendar-plus me-1"></i>Booking
                    </a>
                </div>
            </div>
        `).join('');
    } catch (err) {
        document.getElementById('roomList').innerHTML =
            '<div class="col-12 text-center py-5 text-danger">Gagal memuat data ruangan</div>';
    }
}

document.getElementById('searchRoom').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.room-item').forEach(el => {
        const name = el.querySelector('.room-name').textContent.toLowerCase();
        el.style.display = name.includes(q) ? '' : 'none';
    });
});

loadRooms();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
