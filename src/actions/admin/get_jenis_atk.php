<?php
// src/actions/admin/get_jenis_atk.php
header('Content-Type: application/json');

// $pdo sudah tersedia dari file pemanggil

try {
    // Mengambil data dari tabel jenis_atk
    $stmt = $pdo->query("
        SELECT 
            id_jenis_atk, 
            jenis_atk AS nama_kategori 
        FROM 
            jenis_atk 
        ORDER BY 
            jenis_atk ASC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
}
?>