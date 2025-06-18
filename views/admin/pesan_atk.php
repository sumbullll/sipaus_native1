<?php
$pageTitle = 'Pesan ATK';
$headerTitle = 'Pesan ATK';
require_once __DIR__ . '/../layouts/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* CSS untuk modal dan status */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
    .status { padding: 3px 10px; border-radius: 12px; color: white; font-size: 0.8rem; text-transform: capitalize; }
    .status-dipesan { background-color: #17a2b8; }
    .status-diproses { background-color: #ffc107; color: #333; }
    .status-selesai { background-color: #28a745; }
    .status-menunggu, .status-ditawarkan { background-color: #64748b; }
    .status-dipilih { background-color: #3b82f6; }

        .action-buttons-container {
        display: flex; /* Mengaktifkan Flexbox */
        justify-content: space-between; /* Mendorong item ke ujung kiri dan kanan */
        align-items: center; /* Menyejajarkan item secara vertikal di tengah */
        margin-bottom: 20px; /* Memberi jarak ke tabel di bawahnya */
    }

    /* Styling untuk tombol Tambah (jika belum ada, bisa disesuaikan) */
    .btn-add {
        background-color: #007bff; /* Contoh warna biru, sesuaikan dengan tema Anda */
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 8px; /* Jarak antara ikon dan teks */
    }
    .btn-add:hover {
        background-color: #0056b3;
    }

    /* Styling untuk tombol Cetak PDF yang baru */
    .btn-cetak {
        background-color: #28a745; /* Warna hijau dari kode lama Anda */
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        text-decoration: none; /* Menghilangkan garis bawah pada link */
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 8px; /* Jarak antara ikon dan teks */
    }
    .btn-cetak:hover {
        background-color: #218838; /* Warna hijau sedikit lebih gelap saat di-hover */
    }


</style>

<h1>Daftar Pesan ATK (Pengadaan)</h1> 

<div class="action-buttons-container">
    
    <button class="btn-add" id="btn-open-modal">
        <i class="fas fa-plus"></i> Tambah Pesanan
    </button>

    <a href="<?php echo BASE_URL; ?>/admin/laporan/pembelanjaan" target="_blank" class="btn-cetak">
        <i class="fas fa-print"></i> Cetak Laporan PDF
    </a>

</div>

<div class="dashboard-box"> 
    <div id="notification" class="notification"></div>
    <table id="tabel-pesanan">
        <thead>
            <tr>
                <th>No</th>
                <th>Supplier</th>
                <th>Kategori Barang</th>
                <th>Jumlah</th>
                <th>Tgl. Pengadaan</th>
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
        <h2 id="modal-title"></h2>
        <form class="input-form" id="form-pesanan" onsubmit="return false;">
            <input type="hidden" id="edit-id">

            <div id="add-fields-container">
                <label>Pilih Kategori Barang:</label>
                <select id="select-kategori" name="id_jenis_atk" required style="width: 100%;"></select>
                <label>Jumlah Pesan:</label>
                <input type="number" id="jumlah-pesan-input" name="jumlah" placeholder="Masukkan jumlah" required min="1" />
                <label>Tanggal Pengadaan:</label>
                <input id="tanggal-pengadaan-input" name="tanggal_pengadaan" type="date" required />
            </div>

            <div id="edit-fields-container">
                 <p>Anda mengedit pesanan: <br><strong><span id="nama-barang-modal"></span></strong></p>
                 <label for="status-input">Status Baru:</label>
                 <select id="status-input" name="status" required style="width:100%; padding: 8px;">
                    <option value="dipesan">Dipesan</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
            
            <button type="button" class="btn-save" id="save-pesanan-btn">Simpan</button>
        </form>
    </div>
</div>

<div id="modal-tawaran" class="modal">
    <div class="modal-content">
        <span class="close" id="btn-close-tawaran-modal">&times;</span>
        <h2 id="modal-tawaran-title">Daftar Penawaran</h2>
        <form id="form-pilih-supplier" onsubmit="return false;">
            <input type="hidden" id="hidden_id_detail_pengadaan">
            <p>Memilih penawaran untuk: <br><strong><span id="nama-barang-tawaran-modal"></span></strong></p>
            <table id="tabel-daftar-tawaran">
                <thead>
                    <tr>
                        <th>Pilih</th>
                        <th>Nama Supplier</th>
                        <th>Harga Penawaran</th>
                    </tr>
                </thead>
                <tbody id="tabel-tawaran-body"></tbody>
            </table>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" id="btn-simpan-pilihan" class="btn-save">Pilih & Lanjutkan Pesanan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // =================================================================
    // 1. DEFINISI URL API & SELEKSI ELEMEN DOM
    // =================================================================

    // URL sekarang menunjuk ke satu file CRUD terpusat
    const CRUD_URL = '<?php echo BASE_URL; ?>/api/admin/pesan-atk';  
    const GET_SUPPLIER_URL = '<?php echo BASE_URL; ?>/api/admin/get-suppliers';
    const GET_JENIS_ATK_URL = '<?php echo BASE_URL; ?>/api/admin/jenis-atk';

    // Elemen Global
    const tbody = document.getElementById('tabel-body');
    const notification = document.getElementById('notification');

    // Elemen Modal Tambah/Ubah Status (dari kode lama Anda)
    const modalUbahStatus = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const saveBtn = document.getElementById('save-pesanan-btn');
    const formUbahStatus = document.getElementById('form-pesanan');
    const addFieldsContainer = document.getElementById('add-fields-container');
    const editFieldsContainer = document.getElementById('edit-fields-container');
    const editIdInput = document.getElementById('edit-id');
    const namaBarangModal = document.getElementById('nama-barang-modal');
    const statusInput = document.getElementById('status-input');
    const selectSupplier = $('#select-supplier');
    const selectKategori = $('#select-kategori');

    // Elemen Modal Lihat Tawaran (Baru)
    const modalTawaran = document.getElementById('modal-tawaran');
    const modalTawaranTitle = document.getElementById('modal-tawaran-title');
    const modalTawaranBody = document.getElementById('tabel-tawaran-body');
    const btnSimpanPilihan = document.getElementById('btn-simpan-pilihan');
    const hiddenIdDetailInput = document.getElementById('hidden_id_detail_pengadaan');
    const btnCloseTawaranModal = document.getElementById('btn-close-tawaran-modal');
    const namaBarangTawaranModal = document.getElementById('nama-barang-tawaran-modal');


    // =================================================================
    // 2. FUNGSI-FUNGSI HELPER
    // =================================================================

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
            selectElement.select2({ placeholder, allowClear: true, width: '100%', dropdownParent: $('#modal-form') });
        } catch (error) { tampilkanNotif(error.message, 'error'); }
    }
    
    // Fungsi fetch generik untuk POST, PUT, DELETE
    async function handleSaveOrDelete(method, url, body = null, successCallback = null) {
        try {
            const options = { method, headers: {'Content-Type': 'application/json'} };
            if (body) options.body = JSON.stringify(body);
            
            const response = await fetch(url, options);
            const result = await response.json();
            
            tampilkanNotif(result.message, response.ok ? 'success' : 'error');

            if (response.ok) {
                if(modalUbahStatus.style.display !== 'none') modalUbahStatus.style.display = 'none';
                if(modalTawaran.style.display !== 'none') modalTawaran.style.display = 'none';
                loadDataPesanan(); // Muat ulang data utama
                if (successCallback) successCallback();
            }
        } catch (error) {
            tampilkanNotif('Terjadi kesalahan jaringan.', 'error');
        }
    }


    // =================================================================
    // 3. FUNGSI UTAMA UNTUK MEMUAT DATA
    // =================================================================

    async function loadDataPesanan() {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Memuat data...</td></tr>`;
        try {
            const response = await fetch(`${CRUD_URL}?t=${new Date().getTime()}`);
            if (!response.ok) throw new Error('Gagal memuat data dari server.');
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.dataset.item = JSON.stringify(item);

                    // Logika Dinamis untuk Kolom Supplier
                    const namaSupplier = item.nama_supplier || '<span style="color:#888;font-style:italic;">Menunggu Pemilihan</span>';
                    
                    // Logika Dinamis untuk Tombol Aksi
                    let tombolAksi = '';
                    if (item.status === 'ditawarkan' || item.status === 'menunggu') {
                        const isDisabled = item.jumlah_penawaran == 0;
                        tombolAksi = `<button class="btn-lihat-tawaran" data-id="${item.id_detail_pengadaan}" ${isDisabled ? 'disabled' : ''}>
                                        Lihat Tawaran (${item.jumlah_penawaran})
                                      </button>`;
                    } else {
                        tombolAksi =  `<button class="btn-hapus">Hapus</button>`;
                    }

                    // Render baris tabel
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${namaSupplier}</td>
                        <td>${item.kategori_barang}</td>
                        <td>${item.jumlah}</td>
                        <td>${new Date(item.tanggal_pengadaan).toLocaleDateString('id-ID')}</td>
                        <td><span class="status status-${item.status}">${item.status}</span></td>
                        <td>${tombolAksi}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Belum ada data pengadaan.</td></tr>`;
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:red;">${error.message}</td></tr>`;
        }
    }


    // =================================================================
    // 4. EVENT LISTENERS
    // =================================================================

    // Listener untuk tombol "Tambah Pesanan" (Fungsi Lama)
    btnOpenModal.addEventListener('click', () => {
        modalTitle.textContent = 'Tambah Pesanan ';
        formUbahStatus.reset();
        editIdInput.value = '';
        addFieldsContainer.style.display = 'block';
        editFieldsContainer.style.display = 'none';
        
        selectSupplier.val(null).trigger('change');
        selectKategori.val(null).trigger('change');
        loadSelectOptions(selectKategori, GET_JENIS_ATK_URL, 'Pilih Kategori', 'id_jenis_atk', 'nama_kategori');
        modalUbahStatus.style.display = 'flex';
    });
    
    // Event Delegation Terpusat untuk semua aksi di dalam tabel
    tbody.addEventListener('click', async (e) => {
        const target = e.target;
        const row = target.closest('tr');
        if (!row || !row.dataset.item) return;
        const item = JSON.parse(row.dataset.item);

        // --- Logika untuk Tombol "LIHAT TAWARAN" ---
        if (target.classList.contains('btn-lihat-tawaran')) {
            hiddenIdDetailInput.value = item.id_detail_pengadaan;
            namaBarangTawaranModal.textContent = item.kategori_barang;
            
            modalTawaranBody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Memuat...</td></tr>';
            modalTawaran.style.display = 'flex';

            const response = await fetch(`${CRUD_URL}?action=get_tawaran_by_id&id_detail_pengadaan=${item.id_detail_pengadaan}`);
            const tawaran = await response.json();
            
            modalTawaranBody.innerHTML = '';
            if (tawaran.length > 0) {
                tawaran.forEach(t => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><input type="radio" name="pilihan_supplier" required value='${JSON.stringify(t)}'></td>
                        <td>${t.nama_supplier}</td>
                        <td>Rp ${new Intl.NumberFormat('id-ID').format(t.harga_tawaran)}</td>
                    `;
                    modalTawaranBody.appendChild(tr);
});
            } else {
                modalTawaranBody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Belum ada tawaran untuk item ini.</td></tr>';
            }
        }

        // --- Logika untuk Tombol "UBAH STATUS" (Fungsi Lama) ---
        if (target.classList.contains('btn-edit')) {
            modalTitle.textContent = 'Ubah Status Pesanan';
            formUbahStatus.reset();
            editIdInput.value = item.id_pengadaan; // Menggunakan id_pengadaan untuk update
            namaBarangModal.textContent = `${item.kategori_barang} dari ${item.nama_supplier}`;
            statusInput.value = item.status;
            
            addFieldsContainer.style.display = 'none';
            editFieldsContainer.style.display = 'block';
            modalUbahStatus.style.display = 'flex';
        }
        
        // --- Logika untuk Tombol "HAPUS" (Fungsi Lama) ---
        if (target.classList.contains('btn-hapus')) {
            if (confirm(`Yakin ingin menghapus pesanan '${item.kategori_barang}'?`)) {
                // +++ PERBAIKAN: Gunakan query parameter untuk mengirim ID saat menghapus +++
                handleSaveOrDelete('DELETE', `${CRUD_URL}?id=${item.id_pengadaan}`);
            }
        }
    });

    // Listener untuk Tombol SIMPAN pada Modal Tambah/Ubah (Fungsi Lama)
    saveBtn.addEventListener('click', () => {
        const id = editIdInput.value;
        const isEditMode = !!id;
        const method = isEditMode ? 'PUT' : 'POST';
        const url = isEditMode ? `${CRUD_URL}?id=${id}` : CRUD_URL; 
        let formData;

        if (isEditMode) {
            formData = { status: statusInput.value };
        } else {
            // formData sekarang tidak lagi mengandung id_supplier
            formData = {
                id_jenis_atk: selectKategori.val(),
                jumlah: document.getElementById('jumlah-pesan-input').value,
                tanggal_pengadaan: document.getElementById('tanggal-pengadaan-input').value,
            };
            // Validasi juga tidak lagi memeriksa id_supplier
            if (!formData.id_jenis_atk || !formData.jumlah || !formData.tanggal_pengadaan) {
                tampilkanNotif('Kategori, Jumlah, dan Tanggal harus diisi.', 'error');
                return;
            }
        }
        handleSaveOrDelete(method, url, formData);
    });

    // Listener untuk tombol "Simpan Pilihan" di Modal Tawaran (Fungsi Baru)
    btnSimpanPilihan.addEventListener('click', () => {
        const pilihanRadio = document.querySelector('input[name="pilihan_supplier"]:checked');
        if (!pilihanRadio) {
            tampilkanNotif('Silakan pilih salah satu supplier pemenang.', 'error');
            return;
        }

        const pilihanData = JSON.parse(pilihanRadio.value);
        const body = {
            id_detail_pengadaan: hiddenIdDetailInput.value,
            id_tawaran_terpilih: pilihanData.id_tawaran,
            id_supplier_terpilih: pilihanData.id_supplier
        };
        
        handleSaveOrDelete('POST', `${CRUD_URL}?action=pilih_pemenang`, body);
    });

    // --- Listeners untuk Menutup Semua Modal ---
    btnCloseModal.addEventListener('click', () => { modalUbahStatus.style.display = 'none'; });
    btnCloseTawaranModal.addEventListener('click', () => { modalTawaran.style.display = 'none'; });
    window.addEventListener('click', (e) => {
        if (e.target === modalUbahStatus) modalUbahStatus.style.display = 'none';
        if (e.target === modalTawaran) modalTawaran.style.display = 'none';
    });

    // =================================================================
    // 5. MEMUAT DATA AWAL
    // =================================================================
    loadDataPesanan();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>