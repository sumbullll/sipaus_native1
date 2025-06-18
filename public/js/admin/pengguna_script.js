// public/js/admin/pengguna_script.js
document.addEventListener('DOMContentLoaded', () => {

    const API_BASE_URL = '/sipaustest_native/public/api/admin';
    const ADMIN_PENGGUNA_URL = '/sipaustest_native/public/admin/pengguna'; // Untuk proses simpan
    
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modal-title');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('close-modal');
    const notification = document.getElementById('notification');
    const savePenggunaBtn = document.getElementById('save-pengguna-btn');
    const tbody = document.getElementById('tabel-body');
    const form = document.getElementById('form-pengguna');
    const editUserIdInput = document.getElementById('edit-user-id');
    const passwordInput = document.getElementById('password');

    function tampilkanNotif(msg, type = 'success') {
        notification.textContent = msg;
        notification.className = 'notification';
        notification.classList.add('show', type);
        setTimeout(() => notification.classList.remove('show'), 3000);
    }

    async function loadUserData() {
        try {
            const response = await fetch(`${API_BASE_URL}/get-pengguna-data`);
            if (!response.ok) throw new Error('Gagal mengambil data pengguna.');
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.username}</td>
                        <td>${item.nip || '-'}</td>
                        <td>${item.email}</td>
                        <td>${item.role}</td>
                        <td>
                            <button class="btn-edit" data-id="${item.id}" data-username="${item.username}" data-nip="${item.nip || ''}" data-email="${item.email}" data-role="${item.role}">Edit</button>
                            <button class="btn-hapus" data-id="${item.id}">Hapus</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Belum ada data pengguna.</td></tr>';
            }
        } catch (error) {
            tampilkanNotif(error.message, 'error');
        }
    }

    btnOpenModal.addEventListener('click', () => {
        form.reset();
        modalTitle.textContent = 'Tambah Pengguna';
        editUserIdInput.value = '';
        passwordInput.placeholder = "Password (Wajib)";
        passwordInput.required = true;
        modal.style.display = 'flex';
    });

    btnCloseModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });

    savePenggunaBtn.addEventListener('click', async () => {
        const id = editUserIdInput.value;
        const isEditMode = !!id;
        const url = isEditMode ? `${ADMIN_PENGGUNA_URL}/${id}` : ADMIN_PENGGUNA_URL;
        const method = isEditMode ? 'PUT' : 'POST';

        const data = {
            username: document.getElementById('username').value,
            nip: document.getElementById('nip').value,
            email: document.getElementById('email').value,
            password: passwordInput.value,
            role: document.getElementById('role').value
        };

        if (!data.username || !data.email || !data.role) {
            return tampilkanNotif("Username, email, dan Role harus diisi.", 'error');
        }
        if (!isEditMode && !data.password) {
            return tampilkanNotif("Password harus diisi untuk pengguna baru.", 'error');
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            tampilkanNotif(result.message, response.ok ? 'success' : 'error');
            if (response.ok) {
                modal.style.display = 'none';
                loadUserData();
            }
        } catch (error) {
            tampilkanNotif("Terjadi kesalahan jaringan.", 'error');
        }
    });

    tbody.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-edit')) {
            const button = e.target;
            modalTitle.textContent = 'Edit Pengguna';
            form.reset();
            
            editUserIdInput.value = button.dataset.id;
            document.getElementById('username').value = button.dataset.username;
            document.getElementById('nip').value = button.dataset.nip;
            document.getElementById('email').value = button.dataset.email;
            document.getElementById('role').value = button.dataset.role;
            
            passwordInput.placeholder = "Kosongkan jika tidak ingin mengubah";
            passwordInput.required = false;
            
            modal.style.display = 'flex';
        }
        
        if (e.target.classList.contains('btn-hapus')) {
            if (!confirm("Apakah Anda yakin ingin menghapus pengguna ini?")) return;
            fetch(`${ADMIN_PENGGUNA_URL}/${e.target.dataset.id}`, { method: 'DELETE' })
            .then(res => res.json().then(data => ({ok: res.ok, data})))
            .then(({ok, data}) => {
                tampilkanNotif(data.message, ok ? 'success' : 'error');
                if (ok) loadUserData();
            }).catch(() => tampilkanNotif("Terjadi kesalahan jaringan.", 'error'));
        }
    });

    loadUserData();
});
