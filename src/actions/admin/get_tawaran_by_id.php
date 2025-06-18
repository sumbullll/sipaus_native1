<?php
// actions/admin/get_tawaran_by_id.php

// Panggil file koneksi database Anda. Sesuaikan path jika perlu.
require_once __DIR__ . '/../../config/database.php';

// Validasi input: pastikan id_detail_pengadaan dikirim melalui parameter GET
$id_detail_pengadaan = $_GET['id_detail_pengadaan'] ?? null;

if (empty($id_detail_pengadaan) || !is_numeric($id_detail_pengadaan)) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'ID Detail Pengadaan tidak valid atau tidak ditemukan.']);
    exit();
}

try {
    // Kueri untuk mengambil semua tawaran untuk satu item spesifik
    // Bergabung (JOIN) dengan tabel supplier untuk mendapatkan nama supplier
    // Diurutkan berdasarkan harga termurah (ASC)
    $sql = "
        SELECT
            ts.id_tawaran,
            ts.id_supplier,
            s.nama_supplier,
            ts.harga_tawaran
        FROM
            tawaran_supplier ts
        JOIN
            supplier s ON ts.id_supplier = s.id_supplier
        WHERE
            ts.id_detail_pengadaan = ?
        ORDER BY
            ts.harga_tawaran ASC
    ";

    // Gunakan prepared statement untuk keamanan
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_detail_pengadaan]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kirim data sebagai respons JSON
    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    // Tangani error jika terjadi masalah pada database
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Gagal memuat data tawaran: ' . $e->getMessage()]);
}
?>