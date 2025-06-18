<?php
// views/admin/home.php
$pageTitle = 'Dashboard Admin';
$headerTitle = 'HOME';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin/home_admin.css">

<h1>Home</h1>
<div class="content">
    <div id="notification-area"></div>
    <div class="dashboard">
        <div class="stats">
            <div class="stat-card" id="card-total-barang"><div class="stat-label">Total Barang</div><div class="stat-value">...</div></div>
            <div class="stat-card" id="card-total-permintaan"><div class="stat-label">Total Permintaan</div><div class="stat-value">...</div></div>
            <div class="stat-card" id="card-total-disetujui"><div class="stat-label">Total Disetujui</div><div class="stat-value">...</div></div>
        </div>
        <div class="stats">
            <div class="stat-card" id="card-total-pengguna"><div class="stat-label">Pengguna</div><div class="stat-value">...</div></div>
            <div class="stat-card" id="card-total-survai"><div class="stat-label">Survai</div><div class="stat-value">...</div></div>
            <div class="stat-card" id="card-total-pesan"><div class="stat-label">Pesan</div><div class="stat-value">...</div></div>
        </div>
    </div>

    <div class="dashboard-box">
        <h2>Riwayat Pengambilan</h2>
        <table id="riwayat-pengambilan-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pengguna</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Tanggal Ambil</th>
                </tr>
            </thead>
            <tbody id="riwayat-pengambilan-body"></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Menggunakan BASE_URL untuk memanggil API
    const API_DASHBOARD_DATA_URL = '<?php echo BASE_URL; ?>/api/admin/dashboard-data';
    
    async function loadDashboardData() {
        try {
            const response = await fetch(`${API_DASHBOARD_DATA_URL}?t=${new Date().getTime()}`);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Gagal memuat data dashboard.' }));
                throw new Error(errorData.message);
            }
            const data = await response.json();

            // Update kartu statistik
            document.querySelector('#card-total-barang .stat-value').textContent = data.totalBarang ?? 0;
            document.querySelector('#card-total-permintaan .stat-value').textContent = data.totalPermintaan ?? 0;
            document.querySelector('#card-total-disetujui .stat-value').textContent = data.totalDisetujui ?? 0;
            document.querySelector('#card-total-pengguna .stat-value').textContent = data.totalPengguna ?? 0;
            document.querySelector('#card-total-survai .stat-value').textContent = data.totalSurvei ?? 0;
            document.querySelector('#card-total-pesan .stat-value').textContent = data.totalPesan ?? 0;

            const riwayatBody = document.getElementById('riwayat-pengambilan-body');
            riwayatBody.innerHTML = ''; 
            if (data.riwayatPengambilan && data.riwayatPengambilan.length > 0) {
                data.riwayatPengambilan.forEach((item, index) => {
                    // DIKEMBALIKAN: Menambahkan kembali kolom nomor
                    riwayatBody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.nama_pengguna || 'N/A'}</td>
                            <td>${item.nama_barang || 'N/A'}</td>
                            <td>${item.jumlah}</td>
                            <td>${new Date(item.tanggal_ambil).toLocaleDateString('id-ID')}</td>
                        </tr>
                    `;
                });
            } else {
                // DIUBAH: colspan disesuaikan menjadi 5
                riwayatBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Belum ada riwayat.</td></tr>';
            }

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            const notificationArea = document.getElementById('notification-area');
            if(notificationArea) {
                notificationArea.innerHTML = `<div class="notification error show" style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">${error.message}</div>`;
            }
        }
    }
    loadDashboardData();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>