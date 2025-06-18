<?php
// src/actions/admin/get_atk_data.php
header('Content-Type: application/json');

// Variabel $pdo diasumsikan sudah tersedia dari file pemanggil (api_handler.php)

try {
    // Query untuk mengambil data ATK dan menggabungkannya dengan nama kategori
    $stmt = $pdo->query("
        SELECT 
            atk.id_atk as id,
            atk.nama_atk as nama_barang,
            atk.stok,
            atk.satuan,
            jenis_atk.jenis_atk as kategori, -- DIPERBAIKI DI SINI
            atk.id_atk, 
            atk.id_jenis_atk as jenis_atk_id
        FROM 
            atk
        LEFT JOIN 
            jenis_atk ON atk.id_jenis_atk = jenis_atk.id_jenis_atk
        ORDER BY 
            atk.nama_atk ASC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);

} catch (PDOException $e) {
    // Jika terjadi error database, kirim respons error 500
    http_response_code(500);
    // Tampilkan pesan error yang spesifik dari database untuk membantu debugging
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
}
?>