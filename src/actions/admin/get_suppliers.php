<?php
// src/actions/admin/get_suppliers.php
header('Content-Type: application/json');
try {
    $stmt = $pdo->query("SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
}
?>