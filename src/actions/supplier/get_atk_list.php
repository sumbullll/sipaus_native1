<?php
// src/actions/supplier/get_atk_list.php
header('Content-Type: application/json');
try {
    $stmt = $pdo->query("SELECT id_atk, nama_atk FROM atk ORDER BY nama_atk ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) { /* ... handle error ... */ }
?>