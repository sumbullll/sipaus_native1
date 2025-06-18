<?php
$pageTitle = 'Permintaan Barang';
$headerTitle = 'Permintaan';
require_once __DIR__ . '/../layouts/header_supplier.php';
?>

<style>
    /* CSS untuk modal dan status */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
    .status { padding: 3px 10px; border-radius: 12px; color: white; font-size: 0.8rem; text-transform: capitalize; }
    .status-dipesan { background-color: #17a2b8; }
    .status-diproses { background-color: #ffc107; color: #333; }
    .status-selesai { background-color: #28a745; }
</style>

<h1>Permintaan Pengadaan Barang</h1>
<!-- <p>Berikut adalah daftar permintaan pengadaan barang dari admin. Berikan penawaran harga Anda pada permintaan yang tersedia.</p> -->
<div class="dashboard-box">
    <div id="notification" class="notification"></div>
    <table id="tabel-permintaan">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori Barang</th>
                <th>Jumlah</th>
                <!-- <th>Tanggal Pengadaan</th> -->
                <!-- <th>Keterangan</th> -->
                <!-- <th>Status Pengadaan</th> -->
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tabel-body"></tbody>
    </table>
</div>

<div id="modal-form" class="modal">
    <div class="modal-content">
        <span class="close" id="btn-close-modal">&times;</span>
        <h2 id="modal-title">Beri Penawaran Harga</h2>
        <form class="input-form" id="form-penawaran" onsubmit="return false;">
            <input  id="detail-pengadaan-id">
            <p>Anda akan memberi penawaran untuk: <br><strong><span id="nama-barang-modal"></span></strong></p>
            
            <label for="harga-input">Harga Penawaran per Satuan (Rp):</label>
            <input type="number" id="harga-input" name="harga_penawaran" placeholder="Contoh: 50000" required min="0">
            
            <button type="button" class="btn-save" id="save-btn">Kirim Penawaran</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Definisi URL API
    const API_PERMINTAAN_URL = '<?php echo BASE_URL; ?>/api/supplier/permintaan-pengadaan';
    const API_TAWARAN_URL = '<?php echo BASE_URL; ?>/api/supplier/penawaran';

    // Seleksi Elemen DOM
    const modal = document.getElementById('modal-form');
    const tbody = document.getElementById('tabel-body');
    const notification = document.getElementById('notification');

    // --- FUNGSI-FUNGSI HELPER ---
    
    function tampilkanNotif(msg, type = 'success') {
        notification.textContent = msg;
        notification.className = 'notification show ' + type;
        setTimeout(() => { notification.classList.remove('show'); }, 3000);
    }

    async function handleSave(body = null) {
        try {
            const response = await fetch(API_TAWARAN_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(body)
            });
            const result = await response.json();
            tampilkanNotif(result.message, response.ok ? 'success' : 'error');
            if (response.ok) {
                modal.style.display = 'none';
                loadPermintaanData();
            }
        } catch (error) {
            tampilkanNotif('Terjadi kesalahan jaringan.', 'error');
        }
    }

    // --- LOGIKA UTAMA & EVENT LISTENERS ---

    async function loadPermintaanData() {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Memuat data...</td></tr>`;
        try {
            const response = await fetch(`${API_PERMINTAAN_URL}?t=${new Date().getTime()}`);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: `Gagal memuat data (${response.status})` }));
                throw new Error(errorData.message);
            }
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.permintaanTerbaru && data.permintaanTerbaru.length > 0) {
                data.permintaanTerbaru.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.dataset.item = JSON.stringify(item); // item sudah benar di sini
                    
                    // FIX 2: Sesuaikan nama properti dengan yang akan dikirim PHP
                    // <td>${new Date(item.tanggal_pengadaan).toLocaleDateString('id-ID')}</td>
                    // <td>${item.keterangan || '-'}</td>
                    // <td><span class="status status-${item.status}">${item.status}</span></td>
                    tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${item.jenis_atk}</td>
                    <td>${item.jumlah}</td>
                    <td>
                    ${item.status_penawaran_supplier_ini === 'sudah_menawar' 
                    ? '<span style="color: green; font-weight: bold;">Sudah Ditawar</span>' 
                    : '<button class="btn-edit">Beri Penawaran</button>'}
                    </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                // Kode jika tidak ada data
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Tidak ada permintaan yang bisa ditawar.</td></tr>';
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:red;">${error.message}</td></tr>`;
        }
    }

    // Event Delegation untuk tombol "Beri Penawaran"
    tbody.addEventListener('click', e => {
        if (e.target.classList.contains('btn-edit')) {
            
            const row = e.target.closest('tr');
            const item = JSON.parse(row.dataset.item);
            // console.log(item);
            document.getElementById('form-penawaran').reset();
            document.getElementById('detail-pengadaan-id').value = item.id_detail_pengadaan;
            document.getElementById('nama-barang-modal').textContent = item.kategori_barang;
            modal.style.display = 'flex';
            
        }
    });

    // Event listener untuk tombol Kirim Penawaran
    document.getElementById('save-btn').addEventListener('click', () => {
        const id_detail_pengadaan = document.getElementById('detail-pengadaan-id').value;
        const harga_penawaran = document.getElementById('harga-input').value;

        

        if (!harga_penawaran || harga_penawaran <= 0) {
            tampilkanNotif('Harga penawaran harus diisi dengan angka yang valid.', 'error');
            return;
        }
        handleSave({ id_detail_pengadaan, harga_penawaran });
    });

    // Event listener untuk menutup modal
    document.getElementById('btn-close-modal').addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    // Muat data awal saat halaman dibuka
    loadPermintaanData();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>