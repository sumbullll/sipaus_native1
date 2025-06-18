// public/js/admin/pesan_atk_script.js
document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '/sipaustest_native/public/api/admin';
    const ADMIN_PESAN_ATK_URL = '/sipaustest_native/public/admin/pesan-atk';

    const modal = document.getElementById("modal-form");
    const modalTitle = document.getElementById("modal-title");
    const btnOpen = document.getElementById("btn-open-modal");
    const btnClose = document.getElementById("btn-close-modal");
    const notification = document.getElementById('notification');
    const savePesananBtn = document.getElementById('save-pesanan-btn');
    const tbody = document.getElementById('tabel-body');
    const form = document.getElementById('form-pesanan');
    const editPesananIdInput = document.getElementById('edit-pesanan-id');
    const statusContainer = document.getElementById('status-container');
    
    function tampilkanNotif(pesan, type = 'success') {
        notification.textContent = pesan;
        notification.className = 'notification';
        notification.classList.add('show', type);
        setTimeout(() => notification.classList.remove('show'), 3000);
    }

    async function loadPesananData() {
        try {
            const response = await fetch(`${API_BASE_URL}/get-pesan-atk-data`);
            if (!response.ok) throw new Error('Gagal memuat data pesanan.');
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const row = tbody.insertRow();
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.nama_supplier}</td>
                        <td>${item.nama_barang}</td>
                        <td>${item.jumlah_pesan}</td>
                        <td>${new Date(item.tanggal_pesan).toLocaleDateString('id-ID')}</td>
                        <td><span class="status status-${item.status.toLowerCase()}">${item.status}</span></td>
                        <td>
                            <button class="btn-edit" 
                                data-id="${item.id_pesan_atk}" 
                                data-supplier="${item.nama_supplier}" 
                                data-barang="${item.nama_barang}" 
                                data-jumlah="${item.jumlah_pesan}" 
                                data-tanggal="${item.tanggal_pesan}" 
                                data-status="${item.status}">
                                Edit
                            </button>
                            <button class="btn-hapus" data-id="${item.id_pesan_atk}">Hapus</button>
                        </td>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Belum ada data pesanan.</td></tr>';
            }
        } catch (error) {
            tampilkanNotif(error.message, 'error');
        }
    }

    btnOpen.onclick = () => {
        modal.style.display = "flex";
        form.reset();
        modalTitle.textContent = "Tambah Pesanan";
        editPesananIdInput.value = '';
        statusContainer.style.display = 'none';
    };

    btnClose.onclick = () => modal.style.display = "none";
    window.onclick = (event) => {
        if (event.target == modal) modal.style.display = "none";
    };

    savePesananBtn.addEventListener('click', async () => {
        const id = editPesananIdInput.value;
        const isEditMode = !!id;
        const url = isEditMode ? `${ADMIN_PESAN_ATK_URL}/${id}` : ADMIN_PESAN_ATK_URL;
        const method = isEditMode ? 'PUT' : 'POST';

        const data = {
            nama_supplier: document.getElementById('nama-supplier-input').value,
            nama_barang: document.getElementById('nama-barang-input').value,
            jumlah_pesan: document.getElementById('jumlah-pesan-input').value,
            tanggal_pesan: document.getElementById('tanggal-pesan-input').value,
            status: isEditMode ? document.getElementById('status-input').value : 'menunggu'
        };

        if (!data.nama_supplier || !data.nama_barang || !data.jumlah_pesan || !data.tanggal_pesan) {
            return tampilkanNotif("Semua field harus diisi.", 'error');
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
                loadPesananData();
            }
        } catch (error) {
            tampilkanNotif("Terjadi kesalahan jaringan.", 'error');
        }
    });

    tbody.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-edit')) {
            const data = e.target.dataset;
            form.reset();
            modalTitle.textContent = "Edit Pesanan";
            editPesananIdInput.value = data.id;
            document.getElementById('nama-supplier-input').value = data.supplier;
            document.getElementById('nama-barang-input').value = data.barang;
            document.getElementById('jumlah-pesan-input').value = data.jumlah;
            document.getElementById('tanggal-pesan-input').value = data.tanggal.split(' ')[0];
            statusContainer.style.display = 'block';
            document.getElementById('status-input').value = data.status;
            modal.style.display = 'flex';
        }

        if (e.target.classList.contains('btn-hapus')) {
            if (!confirm("Apakah Anda yakin ingin menghapus pesanan ini?")) return;
            fetch(`${ADMIN_PESAN_ATK_URL}/${e.target.dataset.id}`, { method: 'DELETE' })
            .then(res => res.json().then(data => ({ok: res.ok, data})))
            .then(({ok, data}) => {
                tampilkanNotif(data.message, ok ? 'success' : 'error');
                if (ok) loadPesananData();
            }).catch(() => tampilkanNotif("Terjadi kesalahan jaringan.", 'error'));
        }
    });

    loadPesananData();
});
