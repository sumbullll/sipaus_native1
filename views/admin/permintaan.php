<?php
$pageTitle = 'Daftar Permintaan';
$headerTitle = 'Permintaan Barang';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    /* CSS untuk status */
    .status { padding: 3px 10px; border-radius: 12px; color: white; font-size: 0.8rem; text-transform: capitalize; }
    .status-menunggu { background-color: #ffc107; color: #333; }
    .status-disetujui { background-color: #28a745; }
    .status-ditolak { background-color: #dc3545; }
</style>

<h1>Permintaan Barang dari Pengguna</h1>
<div class="dashboard-box">
    <div id="notification" class="notification"></div>
    <table id="tabel-permintaan">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pegawai</th>
                <th>Kategori Barang</th>
                <th>Jumlah</th>
                <th>Tanggal Permintaan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tabel-body"></tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const API_URL = '<?php echo BASE_URL; ?>/api/admin/permintaan';
    const tbody = document.getElementById('tabel-body');
    const notification = document.getElementById('notification');

    function tampilkanNotif(msg, type = 'success') {
        notification.textContent = msg;
        notification.className = 'notification show ' + type;
        setTimeout(() => { notification.classList.remove('show'); }, 3000);
    }

    async function loadData() {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;">Memuat data...</td></tr>`;
        try {
            const response = await fetch(`${API_URL}?t=${new Date().getTime()}`);
            if (!response.ok) throw new Error(`Gagal memuat data (${response.status})`);
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.dataset.id = item.id_request;
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.nama_pegawai}</td>
                        <td>${item.kategori_barang}</td>
                        <td>${item.jumlah}</td>
                        <td>${new Date(item.tanggal_request).toLocaleDateString('id-ID')}</td>
                        <td><span class="status status-${item.status}">${item.status}</span></td>
                        <td>
                            ${item.status === 'menunggu' ? 
                                `<button class="btn-edit btn-setujui">Setujui</button>
                                 <button class="btn-hapus btn-tolak">Tolak</button>` 
                                : '-'}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;">Belum ada permintaan.</td></tr>`;
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:red;">${error.message}</td></tr>`;
        }
    }

    tbody.addEventListener('click', async e => {
        const target = e.target;
        if (!target.matches('.btn-setujui') && !target.matches('.btn-tolak')) return;
        
        const row = target.closest('tr');
        const id = row.dataset.id;
        const aksi = target.classList.contains('btn-setujui') ? 'setujui' : 'tolak';

        if (confirm(`Anda yakin ingin ${aksi} permintaan ini?`)) {
            try {
                const response = await fetch(`${API_URL}/${id}/${aksi}`, { method: 'POST' });
                const result = await response.json();
                tampilkanNotif(result.message, response.ok ? 'success' : 'error');
                if (response.ok) loadData();
            } catch (error) {
                tampilkanNotif('Terjadi kesalahan jaringan.', 'error');
            }
        }
    });

    loadData();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>