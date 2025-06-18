<?php
$pageTitle = 'Dashboard Supplier';
$headerTitle = 'HOME';
require_once __DIR__ . '/../layouts/header_supplier.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin/home_admin.css">
<style>
    /* Style untuk status di tabel */
    .status { padding: 3px 10px; border-radius: 12px; color: white; font-size: 0.8rem; text-transform: capitalize; }
    .status-dipesan { background-color: #17a2b8; }
    .status-diproses { background-color: #ffc107; color: #333; }
    .status-selesai { background-color: #28a745; }
</style>

<h1>Home</h1>
<div class="content">
    <div id="notification-area"></div>
    <div class="dashboard">
        <div class="stats">
            <div class="stat-card" id="card-total-katalog"><div class="stat-label">Barang di Katalog</div><div class="stat-value">...</div></div>
            <div class="stat-card" id="card-permintaan-masuk"><div class="stat-label">Permintaan Masuk</div><div class="stat-value">...</div></div>
            <div class="stat-card" id="card-total-penawaran"><div class="stat-label">Total Penawaran</div><div class="stat-value">...</div></div>
        </div>
    </div>

    <div class="dashboard-box">
        <h2>Permintaan Terbaru</h2>
        <table id="permintaan-terbaru-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori Barang</th>
                    <th>Jumlah Diminta</th>
                    <!-- <th>Tanggal Permintaan</th> -->
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="permintaan-terbaru-body"></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // URL API untuk mengambil data dashboard khusus supplier
    const API_URL = '<?php echo BASE_URL; ?>/api/supplier/dashboard-data';
    
    async function loadDashboardData() {
        // Tampilkan placeholder loading
        document.querySelector('#card-total-katalog .stat-value').textContent = '...';
        document.querySelector('#card-permintaan-masuk .stat-value').textContent = '...';
        document.querySelector('#card-total-penawaran .stat-value').textContent = '...';
        const riwayatBody = document.getElementById('permintaan-terbaru-body');
        riwayatBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Memuat data...</td></tr>';
        
        try {
            // Gunakan cache-busting untuk memastikan data selalu baru
            const response = await fetch(`${API_URL}?t=${new Date().getTime()}`);
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Gagal memuat data dashboard.' }));
                throw new Error(errorData.message);
            }
            const data = await response.json();

            // Update kartu statistik dengan data dari API
            document.querySelector('#card-total-katalog .stat-value').textContent = data.totalKatalog ?? 0;
            document.querySelector('#card-permintaan-masuk .stat-value').textContent = data.totalPermintaanMasuk ?? 0;
            document.querySelector('#card-total-penawaran .stat-value').textContent = data.totalPenawaran ?? 0;

            // Update tabel permintaan terbaru (<td>${new Date(item.tanggal_permintaan).toLocaleDateString('id-ID')}</td>)
            riwayatBody.innerHTML = ''; 
            console.log(data.permintaanTerbaru)
            if (data.permintaanTerbaru && data.permintaanTerbaru.length > 0) {
                data.permintaanTerbaru.forEach((item, index) => {
                    riwayatBody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.jenis_atk}</td>
                        <td>${item.jumlah}</td>
                        
                        <td><span class="status status-${item.status}">${item.status}</span></td>
                        </tr>
                    `;
                });
            } else {
                riwayatBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Tidak ada permintaan terbaru.</td></tr>';
            }

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            const notificationArea = document.getElementById('notification-area');
            if(notificationArea) {
                notificationArea.innerHTML = `<div class="notification error show">${error.message}</div>`;
            }
        }
    }
    
    // Panggil fungsi untuk memuat data saat halaman dimuat
    loadDashboardData();
});
</script>

<?php 
require_once __DIR__ . '/../layouts/footer.php'; 
?>