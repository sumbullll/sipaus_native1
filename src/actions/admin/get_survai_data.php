<?php
// src/actions/admin/get_survai_data.php
header('Content-Type: application/json');
try {
    // Query ini tidak lagi mengambil kolom 'nip'
    $stmt = $pdo->query("
        SELECT 
            sk.id_survei,
            u.username,
            p.nama_pegawai,
            sk.nama_barang_usulan,
            sk.jumlah_usulan,
            sk.tanggal_survei,
            sk.keterangan,
            sk.status
        FROM survei_kebutuhan sk
        JOIN pegawai p ON sk.id_pegawai = p.id_pegawai
        JOIN users u ON p.id_user = u.id_user
        ORDER BY sk.tanggal_survei DESC, sk.status ASC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
}
?>