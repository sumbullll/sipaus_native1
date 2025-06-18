<?php
// src/actions/admin/get_pengguna_data.php
header('Content-Type: application/json');
try {
    // DISESUAIKAN: Hanya mengambil kolom yang ada di tabel users
    $stmt = $pdo->query("SELECT id_user, username, role FROM users ORDER BY username ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
}
?>