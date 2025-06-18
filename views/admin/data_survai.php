<?php
$pageTitle = 'Data Survei';
$headerTitle = 'Data Survei Kebutuhan';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin/admin_common.css" />
<style>
    /* CSS untuk modal dan status */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
    .status { padding: 3px 10px; border-radius: 12px; color: white; font-size: 0.8rem; text-transform: capitalize; }
    .status-diajukan { background-color: #17a2b8; }
    .status-dipertimbangkan { background-color: #ffc107; color: #333; }
    .status-selesai { background-color: #28a745; }
</style>

<h1>Data Survei Kebutuhan Pengguna</h1>
<div class="dashboard-box">
    <div id="notification" class="notification"></div>
    <table id="tabel-survei">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pegawai</th>
                <th>Barang Usulan</th>
                <th>Jumlah</th>
                <th>Tanggal Usulan</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tabel-survei-body"></tbody>
    </table>
</div>

<div id="modal-status" class="modal">
    <div class="modal-content">
        <span class="close" id="btn-close-modal">&times;</span>
        <h2 id="modal-title">Ubah Status Survei</h2>
        <form class="input-form" id="form-status" onsubmit="return false;">
            <input type="hidden" id="edit-survei-id">
            <p>Ubah status untuk usulan: <br><strong><span id="nama-barang-modal"></span></strong></p>
            <label for="status-input">Status Baru:</label>
            <select id="status-input" name="status" required style="width:100%; padding: 8px;">
                <option value="diajukan">Diajukan</option>
                <option value="dipertimbangkan">Dipertimbangkan</option>
                <option value="selesai">Selesai</option>
            </select>
            <button type="button" class="btn-save" id="save-status-btn" style="margin-top: 15px;">Simpan Status</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Definisi URL
    const API_GET_URL = '<?php echo BASE_URL; ?>/api/admin/get-all-survei-data';
    const API_CRUD_URL = '<?php echo BASE_URL; ?>/api/admin/survei-kebutuhan';

    // Seleksi Elemen
    const tbody = document.getElementById('tabel-survei-body');
    const modal = document.getElementById('modal-status');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const saveBtn = document.getElementById('save-status-btn');
    const notification = document.getElementById('notification');

    // --- FUNGSI-FUNGSI ---

    function tampilkanNotif(msg, type = 'success') {
        notification.textContent = msg;
        notification.className = 'notification show ' + type;
        setTimeout(() => { notification.classList.remove('show'); }, 3000);
    }

    async function loadSurveiData() {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;">Memuat data...</td></tr>`;
        try {
            const response = await fetch(`${API_GET_URL}?t=${new Date().getTime()}`);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: `Gagal memuat data (${response.status})` }));
                throw new Error(errorData.message);
            }
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.dataset.item = JSON.stringify(item);
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.nama_pegawai}</td>
                        <td>${item.nama_barang_usulan}</td>
                        <td>${item.jumlah_usulan}</td>
                        <td>${new Date(item.tanggal_survei).toLocaleDateString('id-ID')}</td>
                        <td>${item.keterangan || '-'}</td>
                        <td><span class="status status-${item.status}">${item.status}</span></td>
                        <td><button class="btn-edit">Ubah Status</button></td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;">Belum ada data survei dari pengguna.</td></tr>`;
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:red;">${error.message}</td></tr>`;
        }
    }

    // --- EVENT LISTENERS ---

    btnCloseModal.addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    tbody.addEventListener('click', e => {
        if (e.target.classList.contains('btn-edit')) {
            const row = e.target.closest('tr');
            const item = JSON.parse(row.dataset.item);
            
            document.getElementById('edit-survei-id').value = item.id_survei;
            document.getElementById('nama-barang-modal').textContent = item.nama_barang_usulan;
            document.getElementById('status-input').value = item.status;
            modal.style.display = 'flex';
        }
    });

    saveBtn.addEventListener('click', async () => {
        const id = document.getElementById('edit-survei-id').value;
        const status = document.getElementById('status-input').value;
        
        try {
            const response = await fetch(`${API_CRUD_URL}/${id}`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ status: status })
            });
            const result = await response.json();
            tampilkanNotif(result.message, response.ok ? 'success' : 'error');
            if (response.ok) {
                modal.style.display = 'none';
                loadSurveiData();
            }
        } catch (error) {
            tampilkanNotif('Terjadi kesalahan jaringan.', 'error');
        }
    });

    // Muat data awal saat halaman dibuka
    loadSurveiData();
});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>