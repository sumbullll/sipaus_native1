<?php
$pageTitle = 'Survei Kebutuhan';
$headerTitle = 'Survei Kebutuhan ATK';
require_once __DIR__ . '/../layouts/header_pengguna.php';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* CSS untuk modal dan status */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
    .status { padding: 3px 10px; border-radius: 12px; color: white; font-size: 0.8rem; text-transform: capitalize; }
    .status-diajukan { background-color: #17a2b8; }
    .status-dipertimbangkan { background-color: #ffc107; color: #333; }
    .status-selesai { background-color: #28a745; }
</style>

<h1>Survei Kebutuhan</h1>
<p>Gunakan halaman ini untuk mengusulkan kebutuhan ATK yang Anda perlukan di masa mendatang.</p>
<div><button class="btn-add" id="btn-open-modal"><i class="fas fa-plus"></i> Buat Usulan Baru</button></div>
<div class="dashboard-box">
    <div id="notification" class="notification"></div>
    <table id="tabel-survei">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang Usulan</th>
                <th>Jumlah Usulan</th>
                <th>Tanggal Survei</th>
                <th>Keterangan</th>
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
        <h2 id="modal-title">Buat Usulan Kebutuhan</h2>
        <form class="input-form" id="form-survei" onsubmit="return false;">
            <input type="hidden" id="edit-survei-id">
            
            <label>Nama Barang Usulan:</label>
            <select id="select-barang" required style="width: 100%;"></select>

            <div id="container-barang-baru" style="display:none; margin-top: 10px;">
                <label>Nama Barang Baru:</label>
                <input type="text" id="input-barang-baru" placeholder="Tulis nama barang baru di sini">
            </div>
            
            <label style="margin-top: 10px;">Jumlah Usulan:</label>
            <input type="number" id="jumlah-input" placeholder="Masukkan jumlah" required min="1">
            
            <label>Tanggal Survei:</label>
            <input type="date" id="tanggal-input" required>
            
            <label>Keterangan/Alasan:</label>
            <textarea id="keterangan-input" placeholder="Alasan kebutuhan barang ini" rows="3" required></textarea>
            
            <button type="button" class="btn-save" id="save-btn">Simpan</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Definisi URL API
    const API_URL = '<?php echo BASE_URL; ?>/api/pengguna/survei-kebutuhan';
    const GET_ATK_URL = '<?php echo BASE_URL; ?>/api/pengguna/get-atk-items';

    // Seleksi Elemen DOM
    const modal = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const saveBtn = document.getElementById('save-btn');
    const tbody = document.getElementById('tabel-body');
    const form = document.getElementById('form-survei');
    const notification = document.getElementById('notification');
    const editSurveiIdInput = document.getElementById('edit-survei-id');
    const selectBarang = $('#select-barang');
    const containerBarangBaru = document.getElementById('container-barang-baru');
    const inputBarangBaru = document.getElementById('input-barang-baru');

    // --- FUNGSI-FUNGSI HELPER ---

    function tampilkanNotif(msg, type = 'success') {
        notification.textContent = msg;
        notification.className = 'notification show ' + type;
        setTimeout(() => { notification.classList.remove('show'); }, 3000);
    }

    async function handleSaveOrDelete(method, url, body = null) {
        try {
            const options = { method: method, headers: {'Content-Type': 'application/json'} };
            if (body) options.body = JSON.stringify(body);
            
            console.log("DEBUG: Mengirim data ke server...", options);
            const response = await fetch(url, options);
            const result = await response.json();
            
            console.log("DEBUG: Menerima respons dari server:", result);
            tampilkanNotif(result.message, response.ok ? 'success' : 'error');
            
            if (response.ok) {
                modal.style.display = 'none';
                loadSurveiData();
            }
        } catch (error) {
            console.error("DEBUG: Terjadi error saat fetch:", error);
            tampilkanNotif('Terjadi kesalahan jaringan.', 'error');
        }
    }

    async function loadBarangOptions() {
        try {
            const response = await fetch(GET_ATK_URL);
            if (!response.ok) throw new Error('Gagal memuat daftar barang.');
            const data = await response.json();
            
            selectBarang.empty().append('<option value="" disabled selected>Pilih Barang...</option>');
            data.forEach(item => {
                selectBarang.append(new Option(item.nama_barang, item.nama_barang));
            });
            selectBarang.append('<option value="lainnya">-- Barang Lainnya (Ketik Baru) --</option>');
            
            selectBarang.select2({
                placeholder: "Pilih Barang atau Pilih Lainnya",
                width: '100%',
                dropdownParent: $('#modal-form')
            });
        } catch (error) {
            tampilkanNotif(error.message, 'error');
        }
    }

    // --- LOGIKA UTAMA & EVENT LISTENERS ---

    async function loadSurveiData() {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Memuat data...</td></tr>`;
        try {
            const urlWithCacheBust = `${API_URL}?t=${new Date().getTime()}`;
            const response = await fetch(urlWithCacheBust);
            if (!response.ok) throw new Error(`Gagal memuat data (${response.status})`);
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.dataset.item = JSON.stringify(item);
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.nama_barang_usulan}</td>
                        <td>${item.jumlah_usulan}</td>
                        <td>${new Date(item.tanggal_survei).toLocaleDateString('id-ID')}</td>
                        <td>${item.keterangan || '-'}</td>
                        <td><span class="status status-${item.status}">${item.status}</span></td>
                        <td>
                            ${item.status === 'diajukan' ? '<button class="btn-edit">Edit</button> <button class="btn-hapus">Hapus</button>' : '-'}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Belum ada usulan yang dibuat.</td></tr>`;
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:red;">${error.message}</td></tr>`;
        }
    }

    btnOpenModal.addEventListener('click', () => {
        form.reset();
        modalTitle.textContent = 'Buat Usulan Kebutuhan';
        editSurveiIdInput.value = '';
        containerBarangBaru.style.display = 'none';
        inputBarangBaru.required = false;
        selectBarang.val(null).trigger('change');
        loadBarangOptions();
        modal.style.display = 'flex';
    });

    selectBarang.on('change', function() {
        if ($(this).val() === 'lainnya') {
            containerBarangBaru.style.display = 'block';
            inputBarangBaru.required = true;
        } else {
            containerBarangBaru.style.display = 'none';
            inputBarangBaru.required = false;
        }
    });

    btnCloseModal.addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    tbody.addEventListener('click', e => {
        const row = e.target.closest('tr');
        if (!row || !row.dataset.item) return;
        const item = JSON.parse(row.dataset.item);

        if (e.target.classList.contains('btn-edit')) {
            modalTitle.textContent = 'Edit Usulan Kebutuhan';
            form.reset();
            containerBarangBaru.style.display = 'none';
            inputBarangBaru.required = false;

            editSurveiIdInput.value = item.id_survei;
            document.getElementById('jumlah-input').value = item.jumlah_usulan;
            document.getElementById('tanggal-input').value = item.tanggal_survei;
            document.getElementById('keterangan-input').value = item.keterangan;
            
            loadBarangOptions().then(() => {
                // Set dropdown ke nama barang yang ada, jika tidak ada, pilih 'lainnya' dan isi input teks
                if (selectBarang.find(`option[value="${item.nama_barang_usulan}"]`).length) {
                    selectBarang.val(item.nama_barang_usulan).trigger('change');
                } else {
                    selectBarang.val('lainnya').trigger('change');
                    inputBarangBaru.value = item.nama_barang_usulan;
                }
            });
            modal.style.display = 'flex';
        }
        if (e.target.classList.contains('btn-hapus')) {
            if (confirm(`Yakin ingin menghapus usulan untuk ${item.nama_barang_usulan}?`)) {
                handleSaveOrDelete('DELETE', `${API_URL}/${item.id_survei}`);
            }
        }
    });

    saveBtn.addEventListener('click', () => {
        console.log("--- DEBUG: Tombol 'Simpan Survei' DIKLIK ---");
        const id = editSurveiIdInput.value;
        const isEditMode = !!id;
        console.log(`DEBUG: Mode terdeteksi: ${isEditMode ? 'EDIT' : 'TAMBAH'}`);

        let namaBarangUsulan = selectBarang.val();
        if (namaBarangUsulan === 'lainnya') {
            namaBarangUsulan = inputBarangBaru.value.trim();
        }
        
        const formData = {
            nama_barang_usulan: namaBarangUsulan,
            jumlah_usulan: document.getElementById('jumlah-input').value,
            tanggal_survei: document.getElementById('tanggal-input').value,
            keterangan: document.getElementById('keterangan-input').value
        };
        console.log("DEBUG: Data form yang terkumpul:", formData);

        if (!formData.nama_barang_usulan || !formData.jumlah_usulan || !formData.tanggal_survei || !formData.keterangan) {
            console.error("DEBUG: Proses berhenti karena validasi gagal.");
            tampilkanNotif('Semua field harus diisi.', 'error');
            return;
        }
        console.log("DEBUG: Validasi berhasil.");

        const method = isEditMode ? 'PUT' : 'POST';
        const url = isEditMode ? `${API_URL}/${id}` : API_URL;
        
        console.log(`DEBUG: Siap mengirim data. Method: ${method}, URL: ${url}`);
        handleSaveOrDelete(method, url, formData);
        console.log("DEBUG: Panggilan ke handleSaveOrDelete selesai.");
    });

    loadSurveiData();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>