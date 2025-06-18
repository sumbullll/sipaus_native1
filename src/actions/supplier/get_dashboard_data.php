<?php
// File: actions/supplier/get_dashboard_data.php (MODIFIED)
header('Content-Type: application/json');

try {
    // Pastikan supplier sudah login dan kita punya ID-nya
    if (!isset($_SESSION['id_supplier'])) {
        throw new Exception("Sesi supplier tidak valid.");
    }
    $id_supplier = $_SESSION['id_supplier'];

    // 1. Menghitung total permintaan pengadaan yang masih terbuka untuk ditawar
    $stmtPermintaan = $pdo->prepare("
        SELECT COUNT(DISTINCT dp.id_detail_pengadaan) 
        FROM detail_pengadaan dp
        WHERE dp.status IN ('menunggu', 'ditawarkan') 
        AND NOT EXISTS (SELECT 1 FROM tawaran_supplier ts WHERE ts.id_detail_pengadaan = dp.id_detail_pengadaan AND ts.id_supplier = ?)
    ");
    $stmtPermintaan->execute([$id_supplier]);
    $totalPermintaanMasuk = (int) $stmtPermintaan->fetchColumn();

    // 2. Menghitung total penawaran yang telah dibuat oleh supplier ini
    $stmtPenawaran = $pdo->prepare("SELECT COUNT(*) FROM tawaran_supplier WHERE id_supplier = ?");
    $stmtPenawaran->execute([$id_supplier]);
    $totalPenawaran = (int) $stmtPenawaran->fetchColumn();

    // 3. Mengambil daftar PESANAN AKTIF (status 'dipilih', 'dipesan', 'diproses')
    //    MODIFIKASI PENTING: Menambahkan p.id_pengadaan dan JOIN ke tabel pengadaan
    $stmtPesananAktif = $pdo->prepare("
        SELECT 
            p.id_pengadaan, 
            ja.jenis_atk, 
            dp.jumlah, 
            dp.status
        FROM detail_pengadaan AS dp
        JOIN jenis_atk AS ja ON dp.id_jenis_atk = ja.id_jenis_atk
        JOIN pengadaan AS p ON dp.id_pengadaan = p.id_pengadaan
        WHERE dp.id_supplier = ? AND dp.status IN ('dipilih', 'dipesan', 'diproses')
        ORDER BY p.tanggal_pengadaan DESC
    ");
    $stmtPesananAktif->execute([$id_supplier]);
    $permintaanTerbaru = $stmtPesananAktif->fetchAll(PDO::FETCH_ASSOC);

    // Siapkan data final untuk dikirim sebagai JSON
    $data = [
        'totalPermintaanMasuk' => $totalPermintaanMasuk,
        'totalPenawaran' => $totalPenawaran,
        'permintaanTerbaru' => $permintaanTerbaru
    ];

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>