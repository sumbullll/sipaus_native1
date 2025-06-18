<?php
// views/admin/data_atk.php

// Menetapkan judul halaman dan header untuk layout
$pageTitle = 'Data Master Barang';
$headerTitle = 'Manajemen Data ATK';

// Memuat bagian header layout
require_once __DIR__ . '/../layouts/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* CSS Kustom di sini */
    .modal {
        display: none; position: fixed; z-index: 1000; left: 0; top: 0;
        width: 100%; height: 100%; overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center; justify-content: center;
    }
    .modal-content {
        background-color: #fefefe; margin: auto; padding: 20px;
        border: 1px solid #888; width: 90%; max-width: 500px;
        border-radius: 8px; position: relative;
    }
    .close {
        color: #aaa; float: right; font-size: 28px; font-weight: bold;
        position: absolute; top: 10px; right: 20px; cursor: pointer;
    }
</style>

<h1>Manajemen Data ATK</h1>
<p>Tambah, lihat, ubah, dan hapus data master barang ATK di sini.</p>

<div><button class="btn-add" id="btn-open-modal"><i class="fas fa-plus"></i> Tambah Barang Baru</button></div>

<div class="dashboard-box">
    <div id="notification" class="notification"></div>
    <table id="tabel-atk">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Stok Saat Ini</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tabel-body"></tbody>
    </table>
</div>

<div id="modal-form" class="modal">
    <div class="modal-content">
        <span class="close" id="btn-close-modal">&times;</span>
        <h2 id="modal-title"></h2>
        <form class="input-form" id="form-atk" onsubmit="return false;">
            <input type="hidden" id="edit-id">

            <label for="nama-atk-input">Nama Barang:</label>
            <input type="text" id="nama-atk-input" name="nama_atk" placeholder="Contoh: Pulpen Pilot G2 Merah" required />

            <label for="select-satuan">Pilih Satuan:</label>
            <select id="select-satuan" name="satuan" required style="width: 100%; padding: 8px; margin-bottom: 15px;">
                <option value="" disabled selected>Pilih Satuan</option>
                <option value="Pcs">Pcs</option>
                <option value="Box">Box</option>
                <option value="Rim">Rim</option>
                <option value="Unit">Unit</option>
                <option value="Set">Set</option>
                <option value="Pack">Pack</option>
            </select>

            <label for="select-kategori">Pilih Kategori:</label>
            <select id="select-kategori" name="id_jenis_atk" required style="width: 100%;"></select>
            
            <button type="button" class="btn-save" id="save-btn">Simpan</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // URL API (disesuaikan dengan router Anda)
    const CRUD_ATK_URL = '<?php echo BASE_URL; ?>/api/admin/data-atk'; 
    const GET_JENIS_ATK_URL = '<?php echo BASE_URL; ?>/api/admin/jenis-atk'; 

    // Seleksi Elemen DOM
    const tbody = document.getElementById('tabel-body');
    const modal = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const saveBtn = document.getElementById('save-btn');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const form = document.getElementById('form-atk');
    const editIdInput = document.getElementById('edit-id');
    const namaAtkInput = document.getElementById('nama-atk-input');
    const selectSatuan = document.getElementById('select-satuan');
    const selectKategori = $('#select-kategori');

    // Helper untuk notifikasi
    function tampilkanNotif(msg, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = msg;
        notification.className = 'notification show ' + type;
        setTimeout(() => { notification.classList.remove('show'); }, 3000);
    }

    // Helper untuk memuat data ke dropdown Select2
    async function loadKategoriOptions() {
        try {
            const response = await fetch(GET_JENIS_ATK_URL);
            if (!response.ok) throw new Error('Gagal memuat kategori.');
            const data = await response.json();
            
            selectKategori.empty().append('<option value="" disabled selected>Pilih Kategori</option>');
            data.forEach(item => {
                // Pastikan menggunakan nama properti yang benar dari API Anda
                selectKategori.append(new Option(item.jenis_atk || item.nama_kategori, item.id_jenis_atk));
            });
            selectKategori.select2({ placeholder: "Pilih Kategori", dropdownParent: $('#modal-form') });
        } catch (error) {
            tampilkanNotif(error.message, 'error');
        }
    }

    // Fungsi utama untuk memuat dan menampilkan data ATK
    async function loadAtkData() {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Memuat data...</td></tr>`;
        try {
            const response = await fetch(`${CRUD_ATK_URL}?t=${new Date().getTime()}`);
            if(!response.ok) throw new Error('Gagal memuat data barang.');
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.dataset.item = JSON.stringify(item);
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.nama_atk}</td>
                        <td>${item.jenis_atk}</td>
                        <td>${item.satuan}</td>
                        <td>${item.stok}</td>
                        <td>
                            <button class="btn-edit">Edit</button>
                            <button class="btn-hapus">Hapus</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Belum ada data master barang.</td></tr>';
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:red;">${error.message}</td></tr>`;
        }
    }

    // Event Listener untuk Tombol "+ Tambah Barang Baru"
    btnOpenModal.addEventListener('click', () => {
        form.reset();
        editIdInput.value = '';
        modalTitle.textContent = 'Tambah Barang Baru';
        selectKategori.val(null).trigger('change');
        loadKategoriOptions();
        modal.style.display = 'flex';
    });

    // Event Delegation untuk tombol Edit dan Hapus
    tbody.addEventListener('click', (e) => {
        const row = e.target.closest('tr');
        if (!row) return;
        const item = JSON.parse(row.dataset.item);

        if (e.target.classList.contains('btn-edit')) {
            editIdInput.value = item.id_atk;
            modalTitle.textContent = `Edit Barang: ${item.nama_atk}`;
            namaAtkInput.value = item.nama_atk;
            selectSatuan.value = item.satuan;
            
            // Muat opsi, lalu set nilainya setelah selesai memuat
            loadKategoriOptions().then(() => {
                selectKategori.val(item.id_jenis_atk).trigger('change');
            });
            
            modal.style.display = 'flex';
        }

        if (e.target.classList.contains('btn-hapus')) {
            if (confirm(`Yakin ingin menghapus barang '${item.nama_atk}'?`)) {
                fetch(`${CRUD_ATK_URL}?id=${item.id_atk}`, { method: 'DELETE' })
                    .then(res => res.json())
                    .then(result => {
                        tampilkanNotif(result.message, result.success ? 'success' : 'error');
                        loadAtkData();
                    })
                    .catch(err => tampilkanNotif('Gagal menghapus data.', 'error'));
            }
        }
    });

    // Event Listener untuk tombol Simpan di Modal
    saveBtn.addEventListener('click', async () => {
        const id = editIdInput.value;
        const isEditMode = !!id;

        const formData = {
            nama_atk: namaAtkInput.value.trim(),
            satuan: selectSatuan.value,
            id_jenis_atk: selectKategori.val()
        };

        if (!formData.nama_atk || !formData.satuan || !formData.id_jenis_atk) {
            tampilkanNotif('Semua field harus diisi.', 'error');
            return;
        }

        const method = isEditMode ? 'PUT' : 'POST';
        const url = isEditMode ? `${CRUD_ATK_URL}?id=${id}` : CRUD_ATK_URL;

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const result = await response.json();
            tampilkanNotif(result.message, response.ok ? 'success' : 'error');
            if (response.ok) {
                modal.style.display = 'none';
                loadAtkData();
            }
        } catch (error) {
            tampilkanNotif('Terjadi kesalahan jaringan.', 'error');
        }
    });
    
    // Event listener untuk menutup modal
    btnCloseModal.addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    // Muat data awal saat halaman dibuka
    loadAtkData();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>