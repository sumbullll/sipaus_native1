<?php
// File: actions/supplier/update_status_pesanan.php (VERSI FINAL YANG BENAR)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

// Keamanan: Hanya method POST dan supplier yang login yang diizinkan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Method harus POST.']);
    exit();
}
if (!isset($_SESSION['id_supplier'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => 'Akses ditolak.']);
    exit();
}

$id_supplier = $_SESSION['id_supplier'];
$data = json_decode(file_get_contents('php://input'), true);
$id_pengadaan = $data['id_pengadaan'] ?? null;
$status_baru = $data['status'] ?? null;
$valid_statuses = ['dipesan', 'diproses', 'selesai'];

if (!$id_pengadaan || !in_array($status_baru, $valid_statuses)) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Data yang dikirim tidak valid.']);
    exit();
}

try {
    // === PERBAIKAN UTAMA ADA DI QUERY INI ===
    // Kita hanya mengupdate tabel 'detail_pengadaan'.
    // Trigger di database akan secara otomatis mengupdate tabel 'pengadaan' jika perlu.
    $sql = "UPDATE detail_pengadaan 
            SET status = ?
            WHERE id_pengadaan = ? AND id_supplier = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status_baru, $id_pengadaan, $id_supplier]);

    // rowCount() akan > 0 jika update berhasil
    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Status pesanan berhasil diperbarui.']);
    } else {
        http_response_code(403);
        echo json_encode(['message' => 'Anda tidak memiliki hak untuk mengubah pesanan ini atau status tidak berubah.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error Server: ' . $e->getMessage()]);
}
?>