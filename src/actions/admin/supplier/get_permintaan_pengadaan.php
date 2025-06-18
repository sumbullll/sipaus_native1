<?php
// src/actions/supplier/get_permintaan_pengadaan.php
header('Content-Type: application/json');
try {
    $id_supplier = $_SESSION['id_supplier']; // Asumsi id_supplier disimpan di session saat login
    
    // Ambil semua detail pengadaan dan cek apakah supplier ini sudah menawar
    $querySQL = "SELECT dp.id_detail_pengadaan, ja.jenis_atk, dp.jumlah  FROM detail_pengadaan AS dp JOIN jenis_atk AS ja ON dp.id_jenis_atk = ja.id_jenis_atk WHERE dp.status IN ('menunggu', 'ditawarkan')";
    $stmt = $pdo->query($querySQL);
    // $stmt->execute([$id_supplier]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // // Ubah nilai numerik 'sudah_menawar' menjadi string
    // foreach ($results as &$row) {
    //     $row['status_penawaran_supplier_ini'] = $row['sudah_menawar'] > 0 ? 'sudah_menawar' : 'belum_menawar';
    // }
    $data = [
        'permintaanTerbaru'=> $results
    ];
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>