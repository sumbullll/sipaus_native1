<?php
$pageTitle = 'Manajemen Pengguna';
$headerTitle = 'Daftar Pengguna';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin/admin_common.css" />

<style>
    /* CSS untuk modal, sama seperti sebelumnya */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
</style>

<h1>Pengguna</h1>
<div><button class="btn-add" id="btn-open-modal"><i class="fas fa-plus"></i> Tambah Pengguna</button></div>
<div class="dashboard-box">
    <div id="notification" class="notification"></div>
    <table id="tabel-pengguna">
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tabel-body"></tbody>
    </table>
</div>

<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" id="close-modal">&times;</span>
        <h2 id="modal-title">Tambah Pengguna</h2>
        <form id="form-pengguna" class="input-form" onsubmit="return false;">
            <input type="hidden" id="edit-user-id" name="id">
            
            <label>Username:</label>
            <input type="text" id="username" name="username" placeholder="Username" required />
            
            <label>Password:</label>
            <input type="password" id="password" name="password" placeholder="Isi untuk user baru / ganti password" />
            <small>Kosongkan jika tidak ingin mengubah password saat edit.</small>
            
            <label>Pilih Role:</label>
            <select id="role" name="role" required>
                <option value="" disabled selected>Pilih Role</option>
                <option value="admin">Admin</option>
                <option value="pegawai">Pegawai</option>
                <option value="supplier">Supplier</option>
            </select>
            
            <button type="button" class="btn-save" id="save-pengguna-btn">Simpan</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Definisi URL
    const API_URL_GET = '<?php echo BASE_URL; ?>/api/admin/get-pengguna-data';
    const API_URL_CRUD = '<?php echo BASE_URL; ?>/api/admin/pengguna';

    // Seleksi Elemen
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modal-title');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('close-modal');
    const saveBtn = document.getElementById('save-pengguna-btn');
    const tbody = document.getElementById('tabel-body');
    const form = document.getElementById('form-pengguna');
    const notification = document.getElementById('notification');

    // ===================================================================
    // --- FUNGSI-FUNGSI UTAMA (LENGKAP) ---
    // ===================================================================

    // Fungsi untuk menampilkan notifikasi
    function tampilkanNotif(msg, type = 'success') {
        notification.textContent = msg;
        notification.className = 'notification show ' + type;
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }

    // Fungsi untuk memuat data pengguna ke tabel
    async function loadPenggunaData() {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Memuat data...</td></tr>`;
        try {
            const response = await fetch(API_URL_GET);
            if (!response.ok) throw new Error(`Gagal memuat data pengguna (${response.status})`);
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.dataset.item = JSON.stringify(item);
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.username}</td>
                        <td style="text-transform: capitalize;">${item.role}</td>
                        <td>
                            <button class="btn-edit">Edit</button>
                            <button class="btn-hapus">Hapus</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Belum ada pengguna.</td></tr>`;
            }
        } catch (error) {
            console.error("Error saat memuat data:", error);
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">${error.message}</td></tr>`;
        }
    }

    // Fungsi terpusat untuk menyimpan, mengedit, dan menghapus data
    async function handleSaveOrDelete(method, url, body = null) {
        try {
            const options = {
                method: method,
                headers: { 'Content-Type': 'application/json' }
            };
            if (body) {
                options.body = JSON.stringify(body);
            }

            const response = await fetch(url, options);
            const result = await response.json();

            tampilkanNotif(result.message, response.ok ? 'success' : 'error');

            if (response.ok) {
                modal.style.display = 'none';
                loadPenggunaData();
            }
        } catch (error) {
            console.error("Error saat menyimpan/menghapus:", error);
            tampilkanNotif('Terjadi kesalahan jaringan.', 'error');
        }
    }

    // ===================================================================
    // --- EVENT LISTENERS (YANG MENGAKTIFKAN TOMBOL) ---
    // ===================================================================

    // Event listener untuk membuka modal (Tambah Pengguna)
    btnOpenModal.addEventListener('click', () => {
        form.reset();
        modalTitle.textContent = 'Tambah Pengguna';
        document.getElementById('edit-user-id').value = '';
        document.getElementById('password').placeholder = 'Password (wajib diisi)';
        document.getElementById('password').required = true;
        modal.style.display = 'flex';
    });

    // Event listener untuk menutup modal
    btnCloseModal.addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    // Event delegation untuk tombol Edit & Hapus di dalam tabel
    tbody.addEventListener('click', e => {
        const row = e.target.closest('tr');
        if (!row || !row.dataset.item) return;
        const item = JSON.parse(row.dataset.item);

        if (e.target.classList.contains('btn-edit')) {
            form.reset();
            modalTitle.textContent = 'Edit Pengguna';
            document.getElementById('edit-user-id').value = item.id_user;
            document.getElementById('username').value = item.username;
            document.getElementById('role').value = item.role;
            document.getElementById('password').placeholder = 'Isi untuk ganti password (opsional)';
            document.getElementById('password').required = false;
            modal.style.display = 'flex';
        }

        if (e.target.classList.contains('btn-hapus')) {
            if (item.id_user == <?php echo $_SESSION['user_id']; ?>) {
                tampilkanNotif('Anda tidak dapat menghapus akun Anda sendiri.', 'error');
                return;
            }
            if (confirm(`Yakin ingin menghapus pengguna ${item.username}?`)) {
                handleSaveOrDelete('DELETE', `${API_URL_CRUD}/${item.id_user}`);
            }
        }
    });

    // Event listener untuk tombol Simpan di dalam modal
    saveBtn.addEventListener('click', () => {
        const id = document.getElementById('edit-user-id').value;
        const isEditMode = !!id;
        
        // Validasi form dasar sebelum lanjut
        if (!form.checkValidity()) {
            tampilkanNotif('Harap isi semua field yang wajib diisi.', 'error');
            // Memicu validasi bawaan browser untuk menunjukkan field mana yang kosong
            form.reportValidity(); 
            return;
        }

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (!isEditMode && !data.password) {
            tampilkanNotif('Password wajib diisi untuk pengguna baru.', 'error');
            return;
        }

        // --- PERBAIKAN UTAMA DI SINI ---
        // Jika ini adalah mode tambah (bukan edit), hapus properti 'id' dari data yang akan dikirim.
        if (!isEditMode) {
            delete data.id;
        }
        // Hapus properti password jika kosong (berguna saat edit tidak ingin ganti password)
        if (!data.password) {
            delete data.password;
        }
        // --------------------------------

        const method = isEditMode ? 'PUT' : 'POST';
        const url = isEditMode ? `${API_URL_CRUD}/${id}` : API_URL_CRUD;

        handleSaveOrDelete(method, url, data);
    });

    // Memuat data pengguna saat halaman pertama kali dibuka
    loadPenggunaData();
});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>