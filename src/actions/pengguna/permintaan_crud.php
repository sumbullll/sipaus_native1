<?php
// FILE INI HANYA UNTUK AKSI: TAMBAH (POST), EDIT (PUT), HAPUS (DELETE)
// src/actions/pengguna/permintaan_crud.php

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    $id_user = $_SESSION['user_id'];
    $stmtPegawai = $pdo->prepare("SELECT id_pegawai FROM pegawai WHERE id_user = ?");
    $stmtPegawai->execute([$id_user]);
    $pegawai = $stmtPegawai->fetch();
    if (!$pegawai) {
        throw new Exception("Profil pegawai tidak valid untuk melakukan aksi ini.");
    }
    $id_pegawai = $pegawai['id_pegawai'];

    $pdo->beginTransaction();

    switch ($method) {
        case 'POST':
            if (empty($data['id_jenis_atk']) || empty($data['jumlah']) || empty($data['tanggal_request'])) {
                throw new Exception("Kategori, Jumlah, dan Tanggal harus diisi.");
            }

            $sql = "INSERT INTO request_pegawai (id_pegawai, tanggal_request, keterangan, status) VALUES (?, ?, ?, 'menunggu')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_pegawai, $data['tanggal_request'], $data['keterangan']]);
            $id_request_baru = $pdo->lastInsertId();

            $sqlDetail = "INSERT INTO detail_request_pegawai (id_request, id_jenis_atk, jumlah) VALUES (?, ?, ?)";
            $stmtDetail = $pdo->prepare($sqlDetail);
            $stmtDetail->execute([$id_request_baru, $data['id_jenis_atk'], $data['jumlah']]);

            echo json_encode(['message' => 'Pengajuan berhasil dikirim.']);
            break;

        case 'PUT':
            $id_request = basename($_SERVER['REQUEST_URI']);
            $sql = "UPDATE request_pegawai SET tanggal_request = ?, keterangan = ? WHERE id_request = ? AND id_pegawai = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['tanggal_request'], $data['keterangan'], $id_request, $id_pegawai]);
            $sqlDetail = "UPDATE detail_request_pegawai SET id_jenis_atk = ?, jumlah = ? WHERE id_request = ?";
            $stmtDetail = $pdo->prepare($sqlDetail);
            $stmtDetail->execute([$data['id_jenis_atk'], $data['jumlah'], $id_request]);
            echo json_encode(['message' => 'Pengajuan berhasil diperbarui.']);
            break;

        case 'DELETE':
            $id_request = basename($_SERVER['REQUEST_URI']);
            $sql = "DELETE FROM request_pegawai WHERE id_request = ? AND id_pegawai = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_request, $id_pegawai]);
            echo json_encode(['message' => 'Pengajuan berhasil dibatalkan.']);
            break;
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>