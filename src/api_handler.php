<?php
// src/api_handler.php

header('Content-Type: application/json');

// Otentikasi & Otorisasi Terpusat
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => 'Anda harus login terlebih dahulu.']);
    exit();
}
if (strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['message' => 'Akses ditolak. Anda bukan admin.']);
    exit();
}

$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$apiPath = preg_replace('/^\/api/', '', $requestUri);
$method = $_SERVER['REQUEST_METHOD'];

// Membersihkan base path API dari URI dengan lebih baik
$apiPath = preg_replace('/^\/api/', '', $requestUri);

// Menentukan file action mana yang akan dimuat
switch (true) {
    // API Dashboard
    case $apiPath === '/admin/dashboard-data':
        include __DIR__ . '/actions/admin/get_dashboard_data.php';
        break;
    
    // API untuk fitur "Lihat Tawaran" di halaman Pesan ATK
    case $apiPath === '/admin/get-tawaran-by-id':
        include __DIR__ . '/actions/admin/get_tawaran_by_id.php'; // Anda perlu membuat file ini
        break;
    
    // API untuk fitur "Pilih Pemenang"
    case $apiPath === '/admin/pilih-pemenang':
        include __DIR__ . '/actions/admin/pilih_pemenang.php'; // Anda perlu membuat file ini
        break;

    // API Data ATK
    case $apiPath === '/admin/get-atk-data':
        include __DIR__ . '/actions/admin/get_atk_data.php';
        break;
    case preg_match('/^\/admin\/data-atk(\/\d+)?$/', $apiPath):
        include __DIR__ . '/actions/admin/atk_crud.php';
        break;
    case preg_match('/^\/admin\/pesan-atk(\/\d+)?$/', $apiPath):
        include __DIR__ . '/actions/admin/pesan_atk_crud.php';
        break;
    case $apiPath === '/admin/jenis-atk':
        include __DIR__ . '/actions/admin/get_jenis_atk.php';
        break;
    case $apiPath === '/admin/atk-items-for-dropdown':
        include __DIR__ . '/actions/admin/get_atk_items_for_dropdown.php';
        break;


    // API Pengguna
    case $apiPath === '/admin/get-pengguna-data':
        include __DIR__ . '/actions/admin/get_pengguna_data.php';
        break;
    case preg_match('/^\/admin\/pengguna(\/\d+)?$/', $apiPath):
        include __DIR__ . '/actions/admin/pengguna_crud.php';
        break;
        
    // API Permintaan (mengarah ke request_pegawai)
    case $apiPath === '/admin/permintaan':
        include __DIR__ . '/actions/admin/get_permintaan_data.php';
        break;
    case preg_match('/^\/admin\/permintaan\/(\d+)\/(setujui|tolak)$/', $apiPath):
        include __DIR__ . '/actions/admin/permintaan_actions.php';
        break;

    // API Pesan ATK (mengarah ke pengadaan)
    // API Pesan ATK (mengarah ke pengadaan)
    case $apiPath === '/admin/get-pesan-atk-data':
        include __DIR__ . '/actions/admin/get_pesan_atk_data.php';
        break;
    case preg_match('/^\/admin\/pesan-atk(\/\d+)?$/', $apiPath):
        include __DIR__ . '/actions/admin/pesan_atk_crud.php';
        break;

    // API untuk dropdown supplier
    case $apiPath === '/admin/get-suppliers':
        include __DIR__ . '/actions/admin/get_suppliers.php';
    break;

    // API Data Survai (mengarah ke tawaran_supplier)
    case $apiPath === '/admin/get-survai-data':
        include __DIR__ . '/actions/admin/get_survai_data.php';
        break;
    case preg_match('/^\/admin\/data-survai\/(\d+)\/(setujui|tolak)$/', $apiPath):
        include __DIR__ . '/actions/admin/survai_actions.php';
        break;
        
    // API Data Survai (dari Pengguna)
    case $apiPath === '/admin/get-all-survei-data':
        // Menggunakan file Anda yang sudah ada
        include __DIR__ . '/actions/admin/get_survai_data.php';
        break;
    case preg_match('/^\/admin\/survei-kebutuhan\/\d+$/', $apiPath):
        // Menggunakan file Anda yang sudah ada (dengan typo)
        include __DIR__ . '/actions/admin/survai_acctions.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint API tidak ditemukan.']);
        break;
}