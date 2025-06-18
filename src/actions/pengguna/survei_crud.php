<?php
// KODE FINAL UNTUK MENANGANI AKSI SURVEI
// src/actions/pengguna/survei_crud.php

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Otentikasi dan mendapatkan id_pegawai dari pengguna yang login
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Sesi pengguna tidak valid. Silakan login kembali.");
    }
    $id_user = $_SESSION['user_id'];
    $stmtPegawai = $pdo->prepare("SELECT id_pegawai FROM pegawai WHERE id_user = ?");
    $stmtPegawai->execute([$id_user]);
    $pegawai = $stmtPegawai->fetch();
    if (!$pegawai) {
        throw new Exception("Profil pegawai tidak ditemukan untuk pengguna ini.");
    }
    $id_pegawai = $pegawai['id_pegawai'];

    // Memulai transaksi untuk menjaga integritas data
    $pdo->beginTransaction();

    switch ($method) {
        case 'POST': // Tambah Usulan Baru
            // Validasi data yang diterima dari form
            if (empty($data['nama_barang_usulan']) || empty($data['jumlah_usulan']) || empty($data['tanggal_survei'])) {
                throw new Exception("Nama Barang, Jumlah, dan Tanggal harus diisi.");
            }

            $sql = "INSERT INTO survei_kebutuhan (id_pegawai, nama_barang_usulan, jumlah_usulan, keterangan, tanggal_survei, status) VALUES (?, ?, ?, ?, ?, 'diajukan')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_pegawai,
                $data['nama_barang_usulan'],
                $data['jumlah_usulan'],
                $data['keterangan'],
                $data['tanggal_survei']
            ]);
            
            echo json_encode(['message' => 'Usulan berhasil dikirim.']);
            break;

        case 'PUT': // Edit Usulan
            $id_survei = basename($_SERVER['REQUEST_URI']);
            if (empty($id_survei) || !is_numeric($id_survei)) {
                throw new Exception("ID Survei tidak valid.");
            }

            $sql = "UPDATE survei_kebutuhan SET nama_barang_usulan = ?, jumlah_usulan = ?, keterangan = ?, tanggal_survei = ? WHERE id_survei = ? AND id_pegawai = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['nama_barang_usulan'],
                $data['jumlah_usulan'],
                $data['keterangan'],
                $data['tanggal_survei'],
                $id_survei,
                $id_pegawai // Memastikan pengguna hanya bisa mengedit surveinya sendiri
            ]);

            echo json_encode(['message' => 'Usulan berhasil diperbarui.']);
            break;

        case 'DELETE': // Hapus Usulan
            $id_survei = basename($_SERVER['REQUEST_URI']);
             if (empty($id_survei) || !is_numeric($id_survei)) {
                throw new Exception("ID Survei tidak valid.");
            }

            $sql = "DELETE FROM survei_kebutuhan WHERE id_survei = ? AND id_pegawai = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_survei, $id_pegawai]); // Memastikan pengguna hanya bisa menghapus surveinya sendiri

            echo json_encode(['message' => 'Usulan berhasil dihapus.']);
            break;
    }

    // Jika semua query berhasil, simpan perubahan secara permanen
    $pdo->commit();

} catch (Exception $e) {
    // Jika ada error di mana pun, batalkan semua perubahan yang sudah terjadi
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>