<?php
// src/pengguna_api_handler.php
header('Content-Type: application/json');

// Otentikasi & Otorisasi
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    http_response_code(401);
    echo json_encode(['message' => 'Anda harus login terlebih dahulu.']);
    exit();
}
$allowed_roles = ['pegawai', 'pengguna'];
if (!in_array(strtolower($_SESSION['role']), $allowed_roles)) {
    http_response_code(403);
    echo json_encode(['message' => 'Akses ditolak.']);
    exit();
}

// Mendapatkan path dan method request
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$apiPath = preg_replace('/^\/api/', '', $requestUri);
$method = $_SERVER['REQUEST_METHOD'];

switch (true) {
    // Rute untuk dashboard
    case $apiPath === '/pengguna/dashboard-data':
        include __DIR__ . '/actions/pengguna/get_dashboard_data.php';
        break;

    // Rute untuk dropdown
    case $apiPath === '/pengguna/get-kategori-barang':
        include __DIR__ . '/actions/admin/get_jenis_atk.php';
        break;
    case $apiPath === '/pengguna/get-atk-items':
        include __DIR__ . '/actions/admin/get_atk_items_for_dropdown.php';
        break;

    // --- RUTE UNTUK PERMINTAAN BARANG ---
    case preg_match('/^\/pengguna\/permintaan(\/\d+)?$/', $apiPath):
        if ($method === 'GET') {
            include __DIR__ . '/actions/pengguna/get_permintaan_data.php';
        } else { // Untuk POST, PUT, DELETE
            include __DIR__ . '/actions/pengguna/permintaan_crud.php';
        }
        break;

    // --- RUTE UNTUK SURVEI KEBUTUHAN ---
    case preg_match('/^\/pengguna\/survei-kebutuhan(\/\d+)?$/', $apiPath):
        if ($method === 'GET') {
            include __DIR__ . '/actions/pengguna/get_survei_data.php';
        } else { // Untuk POST, PUT, DELETE
            include __DIR__ . '/actions/pengguna/survei_crud.php';
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint API Pengguna tidak ditemukan.']);
        break;
}
?>