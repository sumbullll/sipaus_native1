<?php
// src/actions/admin/get_atk_items_for_dropdown.php
header('Content-Type: application/json');

// $pdo sudah tersedia dari file pemanggil

try {
    // Mengambil data dari tabel atk untuk dropdown
    $stmt = $pdo->query("
        SELECT 
            id_atk, 
            nama_atk AS nama_barang 
        FROM 
            atk 
        ORDER BY 
            nama_atk ASC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
}
?>