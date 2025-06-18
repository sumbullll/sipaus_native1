<?php
// src/actions/admin/permintaan_actions.php
header('Content-Type: application/json');

// Mengambil ID dan Aksi dari URL: .../permintaan/{id}/{aksi}
$uri_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$id_request = $uri_parts[3] ?? null;
$aksi = $uri_parts[4] ?? null;

try {
    if (!is_numeric($id_request) || !in_array($aksi, ['setujui', 'tolak'])) {
        throw new Exception("Permintaan tidak valid.");
    }

    // Tentukan status baru berdasarkan aksi
    $new_status = ($aksi === 'setujui') ? 'disetujui' : 'ditolak';

    $pdo->beginTransaction();

    // 1. Ubah status di tabel master 'request_pegawai'
    $sql = "UPDATE request_pegawai SET status = ? WHERE id_request = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_status, $id_request]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Permintaan tidak ditemukan atau status sudah diubah.");
    }

    // 2. Jika disetujui, buat record baru di tabel 'pengambilan' & kurangi stok
    if ($new_status === 'disetujui') {
        // Ambil detail permintaan untuk mengetahui barang dan jumlahnya
        $stmtDetail = $pdo->prepare(
            "SELECT dr.id_jenis_atk, dr.jumlah, r.id_pegawai 
             FROM detail_request_pegawai dr 
             JOIN request_pegawai r ON dr.id_request = r.id_request
             WHERE dr.id_request = ?"
        );
        $stmtDetail->execute([$id_request]);
        $detail = $stmtDetail->fetch();

        if ($detail) {
            // Tambahkan ke tabel 'pengambilan' sebagai bukti barang sudah diambil
            $stmtPengambilan = $pdo->prepare("INSERT INTO pengambilan (id_pegawai, tanggal_pengambilan, status) VALUES (?, ?, 'diambil')");
            $stmtPengambilan->execute([$detail['id_pegawai'], date('Y-m-d')]);
            $id_pengambilan_baru = $pdo->lastInsertId();

            // Logika untuk menentukan ATK spesifik dan mengurangi stok
            // Karena permintaan berdasarkan jenis ATK, kita perlu memilih satu ATK dari jenis tersebut
            $stmtAtk = $pdo->prepare("SELECT id_atk FROM atk WHERE id_jenis_atk = ? AND stok >= ? LIMIT 1");
            $stmtAtk->execute([$detail['id_jenis_atk'], $detail['jumlah']]);
            $atk_to_take = $stmtAtk->fetch();

            if (!$atk_to_take) {
                throw new Exception("Stok untuk kategori barang yang diminta tidak mencukupi.");
            }

            // Masukkan ke 'detail_pengambilan'
            $stmtDetailPengambilan = $pdo->prepare("INSERT INTO detail_pengambilan (id_pengambilan, id_atk, jumlah) VALUES (?, ?, ?)");
            $stmtDetailPengambilan->execute([$id_pengambilan_baru, $atk_to_take['id_atk'], $detail['jumlah']]);

            // Kurangi stok di tabel 'atk'
            $stmtUpdateStok = $pdo->prepare("UPDATE atk SET stok = stok - ? WHERE id_atk = ?");
            $stmtUpdateStok->execute([$detail['jumlah'], $atk_to_take['id_atk']]);
        }
    }

    $pdo->commit();
    echo json_encode(['message' => "Permintaan berhasil di-" . $aksi . "."]);

} catch (Exception $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>