<?php
/* Smart Office Management System - by Anggi Dwi Saputra */ $title = 'Kelola Ruangan'; require_once __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Manajemen data ruangan kantor</p>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="bi bi-plus-lg me-1"></i>Tambah Ruangan
    </button>
</div>

<div class="table-container">
    <div class="table-header">
        <h5><i class="bi bi-door-open-fill me-2"></i>Daftar Ruangan</h5>
        <span class="badge bg-light text-dark" id="roomCount">0 ruangan</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kapasitas</th>
                    <th>Fasilitas</th>
                    <th>Status</th>
                    <th style="width:120px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="roomTable">
                <tr><td colspan="5" class="text-center text-muted py-4">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Ruangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="roomForm" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="roomId">
                    <div class="mb-3">
                        <label class="form-label">Nama Ruangan</label>
                        <input type="text" class="form-control" id="roomName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kapasitas (orang)</label>
                        <input type="number" class="form-control" id="roomCapacity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fasilitas</label>
                        <textarea class="form-control" id="roomFacilities" rows="2" placeholder="Proyektor, AC, Whiteboard, ..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="roomStatus">
                            <option value="available">Tersedia</option>
                            <option value="maintenance">Perawatan</option>
                        </select>
                    </div>
                    <div id="roomFormError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="saveRoomBtn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editingId = null;
let modalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    modalInstance = new bootstrap.Modal(document.getElementById('roomModal'));
    loadRooms();
});

async function loadRooms() {
    const tbody = document.getElementById('roomTable');
    try {
        const res = await API.getRooms();
        document.getElementById('roomCount').textContent = res.data.length + ' ruangan';

        tbody.innerHTML = res.data.map(room => `
            <tr>
                <td><strong>${room.name}</strong></td>
                <td>${room.capacity} orang</td>
                <td style="max-width:250px;white-space:normal;">${room.facilities || '-'}</td>
                <td>${API.statusBadge(room.status)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary btn-sm-custom me-1" onclick="editRoom(${room.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-sm-custom" onclick="deleteRoom(${room.id}, '${room.name}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Gagal memuat data</td></tr>';
    }
}

function openModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Tambah Ruangan';
    document.getElementById('roomForm').reset();
    document.getElementById('roomId').value = '';
    document.getElementById('roomFormError').classList.add('d-none');
    modalInstance.show();
}

async function editRoom(id) {
    try {
        const res = await API.getRooms(id);
        const room = res.data;
        editingId = id;
        document.getElementById('modalTitle').textContent = 'Edit Ruangan';
        document.getElementById('roomId').value = id;
        document.getElementById('roomName').value = room.name;
        document.getElementById('roomCapacity').value = room.capacity;
        document.getElementById('roomFacilities').value = room.facilities || '';
        document.getElementById('roomStatus').value = room.status;
        document.getElementById('roomFormError').classList.add('d-none');
        modalInstance.show();
    } catch (err) {
        showError('Gagal memuat data ruangan');
    }
}

document.getElementById('roomForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorEl = document.getElementById('roomFormError');
    errorEl.classList.add('d-none');

    const data = {
        name: document.getElementById('roomName').value.trim(),
        capacity: parseInt(document.getElementById('roomCapacity').value),
        facilities: document.getElementById('roomFacilities').value.trim(),
        status: document.getElementById('roomStatus').value,
    };

    if (!data.name || !data.capacity) {
        errorEl.textContent = 'Nama dan kapasitas wajib diisi';
        errorEl.classList.remove('d-none');
        return;
    }

    const btn = document.getElementById('saveRoomBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    try {
        if (editingId) {
            await API.updateRoom(editingId, data);
            showSuccess('Ruangan berhasil diperbarui');
        } else {
            await API.createRoom(data);
            showSuccess('Ruangan berhasil ditambahkan');
        }
        modalInstance.hide();
        loadRooms();
    } catch (err) {
        if (err.data?.errors) {
            const msgs = Object.values(err.data.errors).flat().join('<br>');
            errorEl.innerHTML = msgs;
        } else {
            errorEl.textContent = err.data?.message || 'Gagal menyimpan ruangan';
        }
        errorEl.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan';
    }
});

async function deleteRoom(id, name) {
    if (!confirm(`Yakin ingin menghapus "${name}"?`)) return;
    try {
        await API.deleteRoom(id);
        showSuccess('Ruangan berhasil dihapus');
        loadRooms();
    } catch (err) {
        showError(err.data?.message || 'Gagal menghapus ruangan');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
