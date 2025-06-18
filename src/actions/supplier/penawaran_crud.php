<?php
// File: actions/supplier/penawaran_crud.php (Versi Final Lengkap)

// Cek dulu apakah session sudah aktif sebelum memulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);
// Ambil 'action' dari body JSON, defaultnya adalah 'buat_penawaran'
$action = $data['action'] ?? 'buat_penawaran'; 

// Keamanan: Pastikan supplier sudah login
if (!isset($_SESSION['id_supplier'])) {
    http_response_code(401); 
    echo json_encode(['message' => 'Akses ditolak.']);
    exit();
}
$id_supplier = $_SESSION['id_supplier'];

try {
    // File ini hanya menangani method POST untuk kedua aksi
    if ($method === 'POST') {
        
        // --- LOGIKA UNTUK UPDATE STATUS PESANAN (dari Halaman Home) ---
        if ($action === 'update_status_pesanan') {
            $id_pengadaan = $data['id_pengadaan'] ?? null;
            $status_baru = $data['status'] ?? null;
            $valid_statuses = ['dipesan', 'diproses', 'selesai'];

            if (!$id_pengadaan || !in_array($status_baru, $valid_statuses)) {
                http_response_code(400); 
                exit(json_encode(['message' => 'Data untuk update status tidak valid.']));
            }

            $sql = "UPDATE detail_pengadaan SET status = ? WHERE id_pengadaan = ? AND id_supplier = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status_baru, $id_pengadaan, $id_supplier]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['message' => 'Status pesanan berhasil diperbarui.']);
            } else {
                http_response_code(403);
                echo json_encode(['message' => 'Anda tidak memiliki hak untuk mengubah pesanan ini atau status tidak berubah.']);
            }

        } else { // Default action: 'buat_penawaran' (dari Halaman Permintaan)
            
            // --- LOGIKA UNTUK MEMBUAT ATAU MENGEDIT PENAWARAN (UPSERT) ---
            $id_detail_pengadaan = $data['id_detail_pengadaan'] ?? null;
            $harga_tawaran = $data['harga_penawaran'] ?? null;

            if (empty($id_detail_pengadaan) || !is_numeric($harga_tawaran) || $harga_tawaran <= 0) {
                http_response_code(400);
                exit(json_encode(['message' => 'Data penawaran tidak lengkap atau tidak valid.']));
            }

            // Cek dulu apakah supplier ini sudah pernah menawar item ini
            $stmt_check = $pdo->prepare("SELECT id_tawaran FROM tawaran_supplier WHERE id_detail_pengadaan = ? AND id_supplier = ?");
            $stmt_check->execute([$id_detail_pengadaan, $id_supplier]);
            $existing_offer = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($existing_offer) {
                // JIKA SUDAH ADA: Lakukan UPDATE pada harga tawaran yang ada
                $stmt_update = $pdo->prepare("UPDATE tawaran_supplier SET harga_tawaran = ? WHERE id_tawaran = ?");
                $stmt_update->execute([$harga_tawaran, $existing_offer['id_tawaran']]);
                echo json_encode(['message' => 'Penawaran Anda berhasil diperbarui.']);
            } else {
                // JIKA BELUM ADA: Lakukan INSERT data baru
                $stmt_insert = $pdo->prepare(
                    "INSERT INTO tawaran_supplier (id_detail_pengadaan, id_supplier, harga_tawaran, tanggal_tawaran, status_tawaran) VALUES (?, ?, ?, NOW(), 'menunggu')"
                );
                $stmt_insert->execute([$id_detail_pengadaan, $id_supplier, $harga_tawaran]);
                echo json_encode(['message' => 'Penawaran baru berhasil dikirim.']);
            }
        }
    } else {
        http_response_code(405);
        echo json_encode(['message' => 'Method tidak diizinkan.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error Server: ' . $e->getMessage()]);
}
?>