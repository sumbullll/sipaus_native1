<?php
// actions/supplier/get_pesanan_saya.php
session_start();
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['id_supplier'])) {
    http_response_code(401); exit('Akses ditolak.');
}
$id_supplier = $_SESSION['id_supplier'];

try {
    $sql = "
        SELECT 
            p.id_pengadaan,
            p.tanggal_pengadaan,
            dp.status,
            dp.jumlah,
            ja.jenis_atk AS kategori_barang
        FROM detail_pengadaan dp
        JOIN pengadaan p ON dp.id_pengadaan = p.id_pengadaan
        JOIN jenis_atk ja ON dp.id_jenis_atk = ja.id_jenis_atk
        WHERE dp.id_supplier = ? AND dp.status != 'selesai'
        ORDER BY p.tanggal_pengadaan DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_supplier]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
?>