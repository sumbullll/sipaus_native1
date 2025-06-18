<?php
// actions/admin/pilih_pemenang.php

// Panggil file koneksi database Anda. Sesuaikan path jika perlu.
require_once __DIR__ . '/../../config/database.php';

// Ambil data JSON yang dikirim dari frontend
$data = json_decode(file_get_contents('php://input'), true);

// Validasi data input
$id_detail_pengadaan = $data['id_detail_pengadaan'] ?? null;
$id_tawaran_terpilih = $data['id_tawaran_terpilih'] ?? null;
$id_supplier_terpilih = $data['id_supplier_terpilih'] ?? null;

if (!$id_detail_pengadaan || !$id_tawaran_terpilih || !$id_supplier_terpilih) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Data untuk memilih pemenang tidak lengkap.']);
    exit();
}

// Gunakan transaction untuk memastikan semua query berhasil atau semua dibatalkan
$pdo->beginTransaction();

try {
    // === AKSI 1: Update tabel detail_pengadaan ===
    // Mengisi kolom id_supplier dan mengubah status menjadi 'dipesan'
    $stmt1 = $pdo->prepare(
        "UPDATE detail_pengadaan SET id_supplier = ?, status = 'dipilih' WHERE id_detail_pengadaan = ?"
    );
    $stmt1->execute([$id_supplier_terpilih, $id_detail_pengadaan]);

    // === AKSI 2: Update status tawaran pemenang menjadi 'disetujui' ===
    $stmt2 = $pdo->prepare(
        "UPDATE tawaran_supplier SET status_tawaran = 'disetujui' WHERE id_tawaran = ?"
    );
    $stmt2->execute([$id_tawaran_terpilih]);

    // === AKSI 3: Update status semua tawaran lain untuk item yang sama menjadi 'ditolak' ===
    $stmt3 = $pdo->prepare(
        "UPDATE tawaran_supplier SET status_tawaran = 'ditolak' WHERE id_detail_pengadaan = ? AND id_tawaran != ?"
    );
    $stmt3->execute([$id_detail_pengadaan, $id_tawaran_terpilih]);

    // Jika semua aksi di atas berhasil, simpan perubahan secara permanen
    $pdo->commit();

    // Kirim respons sukses ke frontend
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Supplier pemenang berhasil dipilih dan pesanan dilanjutkan.']);

} catch (Exception $e) {
    // Jika ada satu saja yang gagal, batalkan semua perubahan yang sudah terjadi
    $pdo->rollBack();
    
    // Kirim respons error
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Gagal memproses pemilihan pemenang: ' . $e->getMessage()]);
}
?>