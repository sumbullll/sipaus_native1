// public/js/admin/data_atk_script.js
document.addEventListener('DOMContentLoaded', () => {
    
    // BASE_URL sudah didefinisikan di PHP, kita gunakan path absolut
    const API_BASE_URL = '/api/admin';
    
    // Seleksi elemen
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modal-title');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('close-modal');
    const notification = document.getElementById('notification');
    const saveItemBtn = document.getElementById('save-item-btn');
    const tbody = document.getElementById('tabel-body');
    const form = document.getElementById('form-barang');
    const editItemIdInput = document.getElementById('edit-item-id');
    const selectKategori = $('#Kategori');
    const selectNamaBarang = $('#NamaBarangSelect');
    const stokInput = document.getElementById('Stok');
    const satuanSelect = document.getElementById('Satuan');

    function tampilkanNotif(msg, type = 'success') {
        notification.textContent = msg;
        notification.className = 'notification';
        notification.classList.add('show', type);
        setTimeout(() => notification.classList.remove('show'), 3000);
    }

    async function loadSelectOptions(selectElement, url, placeholder, valueField, textField) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Gagal memuat data opsi.');
            const data = await response.json();
            
            selectElement.empty().append(`<option value="" disabled selected>${placeholder}</option>`);
            data.forEach(item => {
                selectElement.append(new Option(item[textField], item[valueField]));
            });
            selectElement.select2({ placeholder, allowClear: true, width: '100%', dropdownParent: $('#modal') });
        } catch (error) {
            tampilkanNotif(error.message, 'error');
        }
    }
    
    async function loadAtkData() {
        try {
            const response = await fetch(`${API_BASE_URL}/get-atk-data`);
            if (!response.ok) {
                const err = await response.json();
                throw new Error(err.message || 'Gagal mengambil data ATK.');
            }
            const data = await response.json();
            
            tbody.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.nama_barang}</td>
                        <td>${item.stok}</td>
                        <td>${item.satuan}</td>
                        <td>${item.kategori || 'N/A'}</td>
                        <td>
                            <button class="btn-edit" 
                                data-id="${item.id}" 
                                data-stok="${item.stok}" 
                                data-satuan="${item.satuan}" 
                                data-atk-id="${item.id_atk}" 
                                data-jenis-atk-id="${item.id_jenis_atk}">
                                Edit
                            </button>
                            <button class="btn-hapus" data-id="${item.id}">Hapus</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Belum ada data barang.</td></tr>';
            }
        } catch (error) {
            tampilkanNotif(error.message, 'error');
        }
    }

    // Event listeners
    btnOpenModal.addEventListener('click', () => {
        modalTitle.textContent = 'Tambah Stok Barang';
        form.reset();
        editItemIdInput.value = '';
        selectKategori.val(null).trigger('change').prop('disabled', false);
        selectNamaBarang.val(null).trigger('change').prop('disabled', false);
        loadSelectOptions(selectKategori, `${API_BASE_URL}/get-jenis-atk`, 'Pilih Kategori', 'id_jenis_atk', 'nama_atk');
        loadSelectOptions(selectNamaBarang, `${API_BASE_URL}/get-atk-items-for-dropdown`, 'Pilih Barang', 'id_atk', 'nama_atk');
        modal.style.display = 'flex';
    });
    
    btnCloseModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });

    saveItemBtn.addEventListener('click', async () => {
        const id = editItemIdInput.value;
        const isEditMode = !!id;
        const url = isEditMode ? `${API_BASE_URL}/data-atk/${id}` : `${API_BASE_URL}/data-atk`;
        const method = isEditMode ? 'PUT' : 'POST';

        const formData = {
            id_atk: selectNamaBarang.val(),
            stok: stokInput.value.trim(),
            satuan: satuanSelect.value,
            id_jenis_atk: selectKategori.val(),
        };
        
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
            tampilkanNotif("Terjadi kesalahan jaringan.", 'error');
        }
    });

    tbody.addEventListener('click', (e) => {
        const target = e.target;
        
        if (target.classList.contains('btn-edit')) {
            modalTitle.textContent = 'Edit Barang';
            form.reset();
            const data = target.dataset;
            
            editItemIdInput.value = data.id;
            stokInput.value = data.stok;
            satuanSelect.value = data.satuan;
            
            Promise.all([
                loadSelectOptions(selectKategori, `${API_BASE_URL}/get-jenis-atk`, 'Pilih Kategori', 'id_jenis_atk', 'nama_kategori'),
                loadSelectOptions(selectNamaBarang, `${API_BASE_URL}/get-atk-items-for-dropdown`, 'Pilih Barang', 'id_atk', 'nama_barang')
            ]).then(() => {
                selectKategori.val(data.jenisAtkId).trigger('change').prop('disabled', true);
                selectNamaBarang.val(data.atkId).trigger('change').prop('disabled', true);
            });
            modal.style.display = 'flex';
        }
        
        if (target.classList.contains('btn-hapus')) {
            if (!confirm("Apakah Anda yakin ingin menghapus data ini?")) return;
            fetch(`${API_BASE_URL}/data-atk/${target.dataset.id}`, { method: 'DELETE' })
            .then(res => res.json().then(data => ({ok: res.ok, data})))
            .then(({ok, data}) => {
                tampilkanNotif(data.message, ok ? 'success' : 'error');
                if (ok) loadAtkData();
            }).catch(() => tampilkanNotif("Terjadi kesalahan jaringan.", 'error'));
        }
    });

    loadAtkData();
});
