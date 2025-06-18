<?php
// src/actions/admin/get_pesan_atk_data.php
header('Content-Type: application/json');
try {
    $stmt = $pdo->query("
        SELECT 
            p.id_pengadaan,
            p.tanggal_pengadaan,
            p.status,
            dp.jumlah,
            s.nama_supplier,
            ja.jenis_atk AS kategori_barang
        FROM pengadaan p
        JOIN detail_pengadaan dp ON p.id_pengadaan = dp.id_pengadaan
        JOIN supplier s ON dp.id_supplier = s.id_supplier
        JOIN jenis_atk ja ON dp.id_jenis_atk = ja.id_jenis_atk
        ORDER BY p.tanggal_pengadaan DESC
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) { /* ... handle error ... */ }
?>