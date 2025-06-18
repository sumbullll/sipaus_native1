<?php
// src/actions/pengguna/get_dashboard_data.php
header('Content-Type: application/json');

try {
    // Pastikan session sudah dimulai dan ada user_id
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Sesi pengguna tidak valid atau Anda belum login.");
    }
    $id_user = $_SESSION['user_id'];

    // Dapatkan 'id_pegawai' dari 'id_user' yang sedang login.
    // Ini adalah langkah kunci untuk mengambil data yang spesifik untuk pengguna ini.
    $stmtPegawaiId = $pdo->prepare("SELECT id_pegawai FROM pegawai WHERE id_user = ?");
    $stmtPegawaiId->execute([$id_user]);
    $pegawai = $stmtPegawaiId->fetch(PDO::FETCH_ASSOC);

    // Jika tidak ada profil pegawai yang terhubung dengan akun user ini, hentikan proses.
    if (!$pegawai) {
         throw new Exception("Profil pegawai tidak ditemukan untuk pengguna ini. Hubungi admin.");
    }
    $id_pegawai = $pegawai['id_pegawai'];

    // --- Mulai Mengambil Data Statistik ---

    // 1. Total Barang (data ini bersifat umum, bukan spesifik pengguna)
    $totalBarang = (int) $pdo->query("SELECT COUNT(*) FROM atk")->fetchColumn();

    // 2. Total Permintaan yang diajukan oleh pengguna ini
    $stmtPermintaan = $pdo->prepare("SELECT COUNT(*) FROM request_pegawai WHERE id_pegawai = ?");
    $stmtPermintaan->execute([$id_pegawai]);
    $totalPermintaan = (int) $stmtPermintaan->fetchColumn();

    // 3. Total Permintaan yang statusnya 'disetujui' untuk pengguna ini
    $stmtDisetujui = $pdo->prepare("SELECT COUNT(*) FROM request_pegawai WHERE id_pegawai = ? AND status = 'disetujui'");
    $stmtDisetujui->execute([$id_pegawai]);
    $totalDisetujui = (int) $stmtDisetujui->fetchColumn();

    // 4. Riwayat 5 Pengambilan terakhir oleh pengguna ini
    $stmtRiwayat = $pdo->prepare("
        SELECT 
            dp.jumlah,
            p.tanggal_pengambilan as tanggal_ambil,
            a.nama_atk as nama_barang
        FROM detail_pengambilan dp
        JOIN pengambilan p ON dp.id_pengambilan = p.id_pengambilan
        JOIN atk a ON dp.id_atk = a.id_atk
        WHERE p.id_pegawai = ?
        ORDER BY p.tanggal_pengambilan DESC
        LIMIT 5
    ");
    $stmtRiwayat->execute([$id_pegawai]);
    $riwayatPengambilan = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);

    // Menyiapkan data final untuk dikirim sebagai respons JSON
    $data = [
        'totalBarang' => $totalBarang,
        'totalPermintaan' => $totalPermintaan,
        'totalDisetujui' => $totalDisetujui,
        'riwayatPengambilan' => $riwayatPengambilan
    ];

    echo json_encode($data);

} catch (Exception $e) {
    // Jika terjadi error di mana pun dalam blok 'try', kirim respons error 500
    http_response_code(500);
    // Tampilkan pesan error yang spesifik untuk mempermudah debugging
    echo json_encode(['message' => 'Terjadi error di server: ' . $e->getMessage()]);
}
?>