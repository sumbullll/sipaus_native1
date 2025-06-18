<?php
// src/actions/admin/get_permintaan_data.php
header('Content-Type: application/json');
try {
    // Query ini menggabungkan semua tabel yang relevan untuk menampilkan info lengkap
    $stmt = $pdo->query("
        SELECT 
            r.id_request,
            r.tanggal_request,
            r.status,
            r.keterangan,
            dr.id_detail_request_pegawai,
            dr.jumlah,
            p.nama_pegawai,
            ja.jenis_atk as kategori_barang
        FROM request_pegawai r
        JOIN detail_request_pegawai dr ON r.id_request = dr.id_request
        JOIN pegawai p ON r.id_pegawai = p.id_pegawai
        JOIN users u ON p.id_user = u.id_user
        JOIN jenis_atk ja ON dr.id_jenis_atk = ja.id_jenis_atk
        ORDER BY FIELD(r.status, 'menunggu', 'disetujui', 'ditolak'), r.tanggal_request DESC
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
}
?>