<?php
// src/actions/admin/get_dashboard_data.php
header('Content-Type: application/json');

try {
    // --- Semua query statistik di atas ini tidak berubah ---
    $totalBarang = (int) $pdo->query("SELECT COUNT(*) FROM atk")->fetchColumn();
    $totalPermintaan = (int) $pdo->query("SELECT COUNT(*) FROM request_pegawai")->fetchColumn();
    $stmtDisetujui = $pdo->prepare("SELECT COUNT(*) FROM request_pegawai WHERE status = ?");
    $stmtDisetujui->execute(['disetujui']);
    $totalDisetujui = (int) $stmtDisetujui->fetchColumn();
    $totalPengguna = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalSurvei = (int) $pdo->query("SELECT COUNT(*) FROM survei_kebutuhan")->fetchColumn();
    $totalPesan = (int) $pdo->query("SELECT COUNT(*) FROM pengadaan")->fetchColumn();

    // --- QUERY RIWAYAT PENGAMBILAN DIPERBARUI DI SINI ---
    $stmtRiwayat = $pdo->query("
        SELECT 
            dp.jumlah,
            p.tanggal_pengambilan as tanggal_ambil,
            a.nama_atk as nama_barang,
            u.username as nama_pengguna
        FROM detail_pengambilan dp
        JOIN pengambilan p ON dp.id_pengambilan = p.id_pengambilan
        JOIN atk a ON dp.id_atk = a.id_atk
        JOIN pegawai pg ON p.id_pegawai = pg.id_pegawai
        JOIN users u ON pg.id_user = u.id_user
        ORDER BY p.tanggal_pengambilan DESC
        LIMIT 5
    ");
    $riwayatPengambilan = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);

    // Menyiapkan data untuk dikirim sebagai JSON
    $data = [
        'totalBarang' => $totalBarang,
        'totalPermintaan' => $totalPermintaan,
        'totalDisetujui' => $totalDisetujui,
        'totalPengguna' => $totalPengguna,
        'totalSurvei' => $totalSurvei,
        'totalPesan' => $totalPesan,
        'riwayatPengambilan' => $riwayatPengambilan
    ];

    echo json_encode($data);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
}
?>