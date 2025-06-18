<?php
$pageTitle = 'Katalog Barang';
$headerTitle = 'Katalog Saya';
require_once __DIR__ . '/../layouts/header_supplier.php';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* CSS untuk modal agar tersembunyi dan rapi */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
</style>

<h1>Katalog Barang Saya</h1>
<p>Kelola daftar barang beserta harga yang Anda tawarkan melalui halaman ini.</p>
<div><button class="btn-add" id="btn-open-modal"><i class="fas fa-plus"></i> Tambah Barang ke Katalog</button></div>
<div class="dashboard-box">
    <div id="notification" class="notification"></div>
    <table id="tabel-katalog">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Katalog</th>
                <th>Kategori</th>
                <th>Harga Penawaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tabel-body"></tbody>
    </table>
</div>

<div id="modal-form" class="modal">
    <div class="modal-content">
        <span class="close" id="btn-close-modal">&times;</span>
        <h2 id="modal-title">Tambah Barang ke Katalog</h2>
        <form class="input-form" id="form-katalog" onsubmit="return false;">
            <input type="hidden" id="edit-katalog-id" name="id_katalog">
            
            <label>Nama Barang (Katalog):</label>
            <input type="text" id="nama-katalog-input" name="nama_katalog" placeholder="Contoh: Kertas A4 Merk Sinar Dunia" required />

            <label>Pilih Kategori Barang:</label>
            <select id="select-jenis-atk" name="id_jenis_atk" required style="width: 100%;"></select>

            <label>Harga Penawaran (Rp):</label>
            <input type="number" id="harga-input" name="harga" placeholder="Contoh: 50000" required min="0" />
            
            <button type="button" class="btn-save" id="save-btn">Simpan</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const API_URL = '<?php echo BASE_URL; ?>/api/supplier/katalog';
    const GET_JENIS_ATK_URL = '<?php echo BASE_URL; ?>/api/pengguna/get-kategori-barang'; // Boleh pakai API pengguna

    const modal = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const saveBtn = document.getElementById('save-btn');
    const tbody = document.getElementById('tabel-body');
    const form = document.getElementById('form-katalog');
    const notification = document.getElementById('notification');
    const editKatalogIdInput = document.getElementById('edit-katalog-id');
    const selectJenisAtk = $('#select-jenis-atk');

    function tampilkanNotif(msg, type = 'success') {
        notification.textContent = msg;
        notification.className = 'notification show ' + type;
        setTimeout(() => { notification.classList.remove('show'); }, 3000);
    }

    async function loadSelectOptions(selectElement, url, placeholder, valueField, textField) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`Gagal memuat opsi.`);
            const data = await response.json();
            
            selectElement.empty().append(`<option value="" disabled selected>${placeholder}</option>`);
            data.forEach(item => {
                selectElement.append(new Option(item[textField], item[valueField]));
            });
            
            selectElement.select2({
                placeholder: placeholder,
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modal-form')
            });
        } catch (error) {
            tampilkanNotif(error.message, 'error');
        }
    }

    async function handleSaveOrDelete(method, url, body = null) {
        try {
            const options = { method: method, headers: {'Content-Type': 'application/json'} };
            if (body) options.body = JSON.stringify(body);
            const response = await fetch(url, options);
            const result = await response.json();
            tampilkanNotif(result.message, response.ok ? 'success' : 'error');
            if (response.ok) {
                modal.style.display = 'none';
                loadKatalogData();
            }
        } catch (error) {
            tampilkanNotif('Terjadi kesalahan jaringan.', 'error');
        }
    }

    async function loadKatalogData() {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Memuat data...</td></tr>`;
        try {
            const response = await fetch(`${API_URL}?t=${new Date().getTime()}`);
            if (!response.ok) throw new Error(`Gagal memuat data katalog (${response.status})`);
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.dataset.item = JSON.stringify(item);
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.nama_katalog}</td>
                        <td>${item.kategori}</td>
                        <td>Rp ${parseInt(item.harga).toLocaleString('id-ID')}</td>
                        <td>
                            <button class="btn-edit">Edit</button>
                            <button class="btn-hapus">Hapus</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Anda belum menambahkan barang ke katalog.</td></tr>`;
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:red;">${error.message}</td></tr>`;
        }
    }

    btnOpenModal.addEventListener('click', () => {
        form.reset();
        modalTitle.textContent = 'Tambah Barang ke Katalog';
        editKatalogIdInput.value = '';
        selectJenisAtk.val(null).trigger('change');
        loadSelectOptions(selectJenisAtk, GET_JENIS_ATK_URL, 'Pilih Kategori', 'id_jenis_atk', 'nama_kategori');
        modal.style.display = 'flex';
    });

    btnCloseModal.addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    tbody.addEventListener('click', e => {
        const row = e.target.closest('tr');
        if (!row || !row.dataset.item) return;
        const item = JSON.parse(row.dataset.item);

        if (e.target.classList.contains('btn-edit')) {
            modalTitle.textContent = 'Edit Barang di Katalog';
            form.reset();
            editKatalogIdInput.value = item.id_katalog;
            document.getElementById('nama-katalog-input').value = item.nama_katalog;
            document.getElementById('harga-input').value = item.harga;
            
            loadSelectOptions(selectJenisAtk, GET_JENIS_ATK_URL, 'Pilih Kategori', 'id_jenis_atk', 'nama_kategori')
                .then(() => {
                    selectJenisAtk.val(item.id_jenis_atk).trigger('change');
                });
            modal.style.display = 'flex';
        }

        if (e.target.classList.contains('btn-hapus')) {
            if (confirm(`Yakin ingin menghapus ${item.nama_katalog} dari katalog Anda?`)) {
                handleSaveOrDelete('DELETE', `${API_URL}/${item.id_katalog}`);
            }
        }
    });

    saveBtn.addEventListener('click', () => {
        const id = editKatalogIdInput.value;
        const isEditMode = !!id;
        const method = isEditMode ? 'PUT' : 'POST';
        const url = isEditMode ? `${API_URL}/${id}` : API_URL;

        const formData = {
            nama_katalog: document.getElementById('nama-katalog-input').value,
            id_jenis_atk: selectJenisAtk.val(),
            harga: document.getElementById('harga-input').value
        };

        if (!formData.nama_katalog || !formData.id_jenis_atk || !formData.harga) {
            tampilkanNotif('Semua field harus diisi.', 'error');
            return;
        }
        handleSaveOrDelete(method, url, formData);
    });

    loadKatalogData();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>