// public/js/admin/permintaan_script.js
document.addEventListener('DOMContentLoaded', () => {
    const API_PERMINTAAN_URL = '/sipaustest_native/public/api/admin/get-permintaan-data';
    const ADMIN_PERMINTAAN_BASE_URL = '/sipaustest_native/public/api/admin/permintaan';

    const notificationPermintaan = document.getElementById('notification-permintaan');
    const tbodyPermintaan = document.getElementById('tabel-permintaan-body');

    function tampilkanNotifPermintaan(msg, type = 'success') {
        notificationPermintaan.textContent = msg;
        notificationPermintaan.className = 'notification';
        notificationPermintaan.classList.add('show', type);
        setTimeout(() => notificationPermintaan.classList.remove('show'), 2500);
    }

    async function loadPermintaanData() {
        try {
            const response = await fetch(API_PERMINTAAN_URL);
            if (!response.ok) throw new Error('Gagal memuat data permintaan.');
            const data = await response.json();
            
            tbodyPermintaan.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const row = tbodyPermintaan.insertRow();
                    const aksiButtons = item.status.toLowerCase() === 'pending' 
                        ? `<button class="btn-approve" data-id="${item.id}">Setujui</button> <button class="btn-reject" data-id="${item.id}">Tolak</button>`
                        : '<span>-</span>';
                        
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.username || 'N/A'}</td>
                        <td>${item.nip || 'N/A'}</td>
                        <td>${item.nama_barang || 'N/A'}</td>
                        <td>${item.jumlah}</td>
                        <td>${new Date(item.tanggal_permintaan).toLocaleDateString('id-ID')}</td>
                        <td>${item.tempat || 'N/A'}</td>
                        <td><span class="status status-${item.status.toLowerCase()}">${item.status}</span></td>
                        <td>${aksiButtons}</td>
                    `;
                });
            } else {
                tbodyPermintaan.innerHTML = '<tr><td colspan="9" style="text-align:center;">Belum ada data permintaan.</td></tr>';
            }
        } catch (error) {
            tampilkanNotifPermintaan(error.message, 'error');
        }
    }

    tbodyPermintaan.addEventListener('click', async (e) => {
        const target = e.target;
        if (target.matches('.btn-approve, .btn-reject')) {
            const id = target.dataset.id;
            const action = target.matches('.btn-approve') ? 'setujui' : 'tolak';
            
            if (!confirm(`Apakah Anda yakin ingin ${action} permintaan ini?`)) return;

            try {
                const response = await fetch(`${ADMIN_PERMINTAAN_BASE_URL}/${id}/${action}`, { method: 'POST' });
                const result = await response.json();
                
                tampilkanNotifPermintaan(result.message, response.ok ? 'success' : 'error');
                if (response.ok) loadPermintaanData();
            } catch (error) {
                tampilkanNotifPermintaan("Terjadi kesalahan jaringan.", 'error');
            }
        }
    });

    loadPermintaanData();
});
