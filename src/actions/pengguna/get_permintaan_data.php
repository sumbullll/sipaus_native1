<?php
// FILE INI HANYA UNTUK MENGAMBIL SEMUA DATA (GET)
// src/actions/pengguna/get_permintaan_data.php
header('Content-Type: application/json');
try {
    $id_user = $_SESSION['user_id'];
    $stmtPegawai = $pdo->prepare("SELECT id_pegawai FROM pegawai WHERE id_user = ?");
    $stmtPegawai->execute([$id_user]);
    $pegawai = $stmtPegawai->fetch();
    if (!$pegawai) {
        echo json_encode([]);
        exit();
    }

    $stmt = $pdo->prepare("
        SELECT r.id_request, r.tanggal_request, r.status, r.keterangan, dr.jumlah, ja.jenis_atk as kategori_barang, dr.id_jenis_atk
        FROM request_pegawai r
        JOIN detail_request_pegawai dr ON r.id_request = dr.id_request
        JOIN jenis_atk ja ON dr.id_jenis_atk = ja.id_jenis_atk
        WHERE r.id_pegawai = ? ORDER BY r.tanggal_request DESC
    ");
    $stmt->execute([$pegawai['id_pegawai']]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>