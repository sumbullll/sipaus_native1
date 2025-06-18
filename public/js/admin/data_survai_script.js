// public/js/admin/data_survai_script.js
document.addEventListener('DOMContentLoaded', () => {
    const API_SURVEI_URL = '/sipaustest_native/public/api/admin/get-survai-data';
    const ADMIN_SURVEI_BASE_URL = '/sipaustest_native/public/api/admin/data-survai';

    const notificationSurvei = document.getElementById('notification-survei');
    const tbodySurvei = document.getElementById('tabel-survei-body');

    function tampilkanNotifSurvei(msg, type = 'success') {
        notificationSurvei.textContent = msg;
        notificationSurvei.className = 'notification';
        notificationSurvei.classList.add('show', type);
        setTimeout(() => notificationSurvei.classList.remove('show'), 2500);
    }

    async function loadSurveyData() {
        try {
            const response = await fetch(API_SURVEI_URL);
            if (!response.ok) throw new Error('Gagal memuat data survei.');
            const data = await response.json();
            
            tbodySurvei.innerHTML = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    const row = tbodySurvei.insertRow();
                     const aksiButtons = item.status.toLowerCase() === 'pending' 
                        ? `<button class="btn-approve" data-id="${item.id}">Setujui</button> <button class="btn-reject" data-id="${item.id}">Tolak</button>`
                        : '<span>-</span>';
                        
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.username || 'N/A'}</td>
                        <td>${item.nip || 'N/A'}</td>
                        <td>${item.nama_barang || 'N/A'}</td>
                        <td>${item.jumlah || 'N/A'}</td>
                        <td>${new Date(item.tanggal_survei).toLocaleDateString('id-ID')}</td>
                        <td>${item.feedback}</td>
                        <td><span class="status status-${item.status.toLowerCase()}">${item.status}</span></td>
                        <td>${aksiButtons}</td>
                    `;
                });
            } else {
                tbodySurvei.innerHTML = '<tr><td colspan="9" style="text-align:center;">Belum ada data survei.</td></tr>';
            }
        } catch (error) {
            tampilkanNotifSurvei(error.message, 'error');
        }
    }

    tbodySurvei.addEventListener('click', async (e) => {
        const target = e.target;
        if (target.matches('.btn-approve, .btn-reject')) {
            const id = target.dataset.id;
            const action = target.matches('.btn-approve') ? 'setujui' : 'tolak';

            if (!confirm(`Apakah Anda yakin ingin ${action} survei ini?`)) return;

            try {
                const response = await fetch(`${ADMIN_SURVEI_BASE_URL}/${id}/${action}`, { method: 'POST' });
                const result = await response.json();
                
                tampilkanNotifSurvei(result.message, response.ok ? 'success' : 'error');
                if (response.ok) loadSurveyData();
            } catch (error) {
                tampilkanNotifSurvei("Terjadi kesalahan jaringan.", 'error');
            }
        }
    });

    loadSurveyData();
});
