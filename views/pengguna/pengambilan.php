<?php
$pageTitle = 'Pengambilan Barang';
$headerTitle = 'Pengambilan Barang';
require_once __DIR__ . '/../layouts/header_pengguna.php';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* CSS untuk modal dan status */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
    .status { padding: 3px 10px; border-radius: 12px; color: white; font-size: 0.8rem; text-transform: capitalize; }
    .status-menunggu { background-color: #ffc107; color: #333; }
    .status-disetujui { background-color: #28a745; }
    .status-ditolak { background-color: #dc3545; }
</style>

<h1>Pengambilan Barang</h1>
<div><button class="btn-add" id="btn-open-modal"><i class="fas fa-plus"></i> Ajukan Pengambilan</button></div>
<div class="dashboard-box">
    <div id="notification" class="notification"></div>
    <table id="tabel-pengambilan">
        <thead>
            <tr>
                <th>No</th>
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

<div id="modal-form" class="modal">
    <div class="modal-content">
        <span class="close" id="btn-close-modal">&times;</span>
        <h2 id="modal-title">Ajukan Pengambilan Barang</h2>
        <form class="input-form" id="form-pengambilan" onsubmit="return false;">
            <input type="hidden" id="edit-request-id" name="id_request">
            
            <label>Kategori Barang:</label>
            <select id="select-kategori" name="id_jenis_atk" required style="width: 100%;"></select>
            
            <label>Jumlah:</label>
            <input type="number" id="jumlah-input" name="jumlah" placeholder="Masukkan jumlah barang" required min="1" />
            
            <label>Tanggal Permintaan:</label>
            <input id="tanggal-input" name="tanggal_request" type="date" required />

            <label>Keterangan:</label>
            <textarea id="keterangan-input" name="keterangan" placeholder="Tuliskan keterangan jika perlu (misal: untuk keperluan rapat)" rows="3"></textarea>
            
            <button type="button" class="btn-save" id="save-btn">Ajukan</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Definisi URL API
    const API_URL = '<?php echo BASE_URL; ?>/api/pengguna/permintaan';
    const GET_JENIS_ATK_URL = '<?php echo BASE_URL; ?>/api/pengguna/get-kategori-barang';

    // Seleksi Elemen DOM
    const modal = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const saveBtn = document.getElementById('save-btn');
    const tbody = document.getElementById('tabel-body');
    const form = document.getElementById('form-pengambilan');
    const notification = document.getElementById('notification');
    const selectKategori = $('#select-kategori');

    // --- FUNGSI-FUNGSI HELPER ---

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
            console.error(error);
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
                loadPermintaanData();
            }
        } catch (error) {
            tampilkanNotif('Terjadi kesalahan jaringan.', 'error');
        }
    }

    // --- LOGIKA UTAMA & EVENT LISTENERS ---

    async function loadPermintaanData() {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Memuat data...</td></tr>`;
        try {
            const urlWithCacheBust = `${API_URL}?t=${new Date().getTime()}`;
            const response = await fetch(urlWithCacheBust);
            
            if (!response.ok) {
                 const errorData = await response.json().catch(() => ({ message: `Gagal mengambil data (${response.status})` }));
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
                        <td>${item.kategori_barang}</td>
                        <td>${item.jumlah}</td>
                        <td>${new Date(item.tanggal_request).toLocaleDateString('id-ID')}</td>
                        <td><span class="status status-${item.status}">${item.status}</span></td>
                        <td>
                            ${item.status === 'menunggu' ? '<button class="btn-edit">Edit</button> <button class="btn-hapus">Batalkan</button>' : '-'}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Belum ada pengajuan.</td></tr>`;
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:red;">${error.message}</td></tr>`;
        }
    }

    btnOpenModal.addEventListener('click', () => {
        form.reset();
        modalTitle.textContent = 'Ajukan Pengambilan Barang';
        document.getElementById('edit-request-id').value = '';
        selectKategori.val(null).trigger('change');
        loadSelectOptions(selectKategori, GET_JENIS_ATK_URL, 'Pilih Kategori Barang', 'id_jenis_atk', 'nama_kategori');
        modal.style.display = 'flex';
    });

    btnCloseModal.addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    saveBtn.addEventListener('click', () => {
        const id = document.getElementById('edit-request-id').value;
        const isEditMode = !!id;
        const method = isEditMode ? 'PUT' : 'POST';
        const url = isEditMode ? `${API_URL}/${id}` : API_URL;

        const formData = {
            id_jenis_atk: selectKategori.val(),
            jumlah: document.getElementById('jumlah-input').value,
            tanggal_request: document.getElementById('tanggal-input').value,
            keterangan: document.getElementById('keterangan-input').value
        };

        if (!formData.id_jenis_atk || !formData.jumlah || !formData.tanggal_request) {
            tampilkanNotif('Kategori, Jumlah, dan Tanggal harus diisi.', 'error');
            return;
        }
        handleSaveOrDelete(method, url, formData);
    });
    
    tbody.addEventListener('click', e => {
        const row = e.target.closest('tr');
        if (!row || !row.dataset.item) return;
        const item = JSON.parse(row.dataset.item);

        if (e.target.classList.contains('btn-edit')) {
            modalTitle.textContent = 'Edit Pengajuan Barang';
            form.reset();
            document.getElementById('edit-request-id').value = item.id_request;
            document.getElementById('jumlah-input').value = item.jumlah;
            document.getElementById('tanggal-input').value = item.tanggal_request;
            document.getElementById('keterangan-input').value = item.keterangan;
            
            loadSelectOptions(selectKategori, GET_JENIS_ATK_URL, 'Pilih Kategori Barang', 'id_jenis_atk', 'nama_kategori')
                .then(() => {
                    selectKategori.val(item.id_jenis_atk).trigger('change');
                });
            modal.style.display = 'flex';
        }

        if (e.target.classList.contains('btn-hapus')) {
            if (confirm('Yakin ingin membatalkan pengajuan ini?')) {
                handleSaveOrDelete('DELETE', `${API_URL}/${item.id_request}`);
            }
        }
    });

    // Muat data awal
    loadPermintaanData();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>