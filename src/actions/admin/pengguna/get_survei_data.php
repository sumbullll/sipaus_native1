<?php
// src/actions/pengguna/get_survei_data.php
header('Content-Type: application/json');
try {
    $id_user = $_SESSION['user_id'];
    $stmtPegawai = $pdo->prepare("SELECT id_pegawai FROM pegawai WHERE id_user = ?");
    $stmtPegawai->execute([$id_user]);
    $pegawai = $stmtPegawai->fetch();
    if (!$pegawai) throw new Exception("Profil pegawai tidak ditemukan.");

    $stmt = $pdo->prepare("SELECT * FROM survei_kebutuhan WHERE id_pegawai = ? ORDER BY tanggal_survei DESC");
    $stmt->execute([$pegawai['id_pegawai']]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (Exception $e) { /* ... (handle error) ... */ }
?>