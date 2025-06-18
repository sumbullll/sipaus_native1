<?php
// src/actions/admin/survai_acctions.php
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Hanya proses method PUT untuk mengubah status
if ($method === 'PUT') {
    try {
        $id_survei = basename($_SERVER['REQUEST_URI']);
        $status = $data['status'];

        if (empty($id_survei) || !is_numeric($id_survei)) {
            throw new Exception("ID Survei tidak valid.");
        }
        if (empty($status) || !in_array($status, ['diajukan', 'dipertimbangkan', 'selesai'])) {
             throw new Exception("Nilai status tidak valid.");
        }

        $sql = "UPDATE survei_kebutuhan SET status = ? WHERE id_survei = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $id_survei]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Status survei berhasil diperbarui.']);
        } else {
            throw new Exception("Tidak ada data yang diubah atau survei tidak ditemukan.");
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Method tidak diizinkan.']);
}
?>