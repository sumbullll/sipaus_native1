<?php
// src/supplier_api_handler.php
header('Content-Type: application/json');

// Otentikasi & Otorisasi untuk Supplier
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    http_response_code(401);
    echo json_encode(['message' => 'Anda harus login terlebih dahulu.']);
    exit();
}
if (strtolower($_SESSION['role']) !== 'supplier') {
    http_response_code(403);
    echo json_encode(['message' => 'Akses ditolak. Halaman ini hanya untuk supplier.']);
    exit();
}

// Dapatkan id_supplier dari session untuk digunakan di file aksi
$stmtSupplier = $pdo->prepare("SELECT id_supplier FROM supplier WHERE id_user = ?");
$stmtSupplier->execute([$_SESSION['user_id']]);
$supplierInfo = $stmtSupplier->fetch();
if (!$supplierInfo) {
    http_response_code(403);
    echo json_encode(['message' => 'Profil supplier tidak ditemukan.']);
    exit();
}
$_SESSION['id_supplier'] = $supplierInfo['id_supplier'];


// Mendapatkan path dan method request
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$apiPath = preg_replace('/^\/api/', '', $requestUri);
$method = $_SERVER['REQUEST_METHOD'];

switch (true) {
    // Rute untuk mengambil daftar ATK umum (untuk dropdown di katalog)
    case $apiPath === '/supplier/get-atk-list':
        include __DIR__ . '/actions/supplier/get_atk_list.php';
        break;

    // Rute untuk CRUD Katalog (menangani GET, POST, PUT, DELETE)
    case preg_match('/^\/supplier\/katalog(\/\d+)?$/', $apiPath):
        include __DIR__ . '/actions/supplier/katalog_crud.php';
        break;

    // --- TAMBAHKAN CASE BARU DI SINI ---
    case $apiPath === '/supplier/dashboard-data':
        include __DIR__ . '/actions/supplier/get_dashboard_data.php';
        break;

    // Rute untuk mengambil daftar permintaan pengadaan dari admin
    case $apiPath === '/supplier/permintaan-pengadaan':
        include __DIR__ . '/actions/supplier/get_permintaan_pengadaan.php';
        break;

    // Rute untuk mengirim penawaran (POST)
    case $apiPath === '/supplier/penawaran':
        include __DIR__ . '/actions/supplier/penawaran_crud.php';
        break;

    case $apiPath === '/supplier/update-status-pesanan':
        include __DIR__ . '/actions/supplier/update_status_pesanan.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint API Supplier tidak ditemukan.']);
        break;
}
?>