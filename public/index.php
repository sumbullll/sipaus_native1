<?php
// public/index.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
define('BASE_URL', '');
require_once __DIR__ . '/../config/database.php';
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
if (empty($requestUri) || $requestUri === '/') { $requestUri = '/login'; }

switch (true) {
    // HALAMAN VIEWS
    case $requestUri === '/login': include __DIR__ . '/../views/auth/login.php'; break;
    // RUTE ADMIN
    case $requestUri === '/admin/home': include __DIR__ . '/../views/admin/home.php'; break;
    case $requestUri === '/admin/data-atk': include __DIR__ . '/../views/admin/data_atk.php'; break;
    case $requestUri === '/admin/pengguna': include __DIR__ . '/../views/admin/pengguna.php'; break;
    case $requestUri === '/admin/permintaan': include __DIR__ . '/../views/admin/permintaan.php'; break;
    case $requestUri === '/admin/pesan-atk': include __DIR__ . '/../views/admin/pesan_atk.php'; break;
    case $requestUri === '/admin/data-survai': include __DIR__ . '/../views/admin/data_survai.php'; break;
    case $requestUri === '/admin/laporan/pembelanjaan': include __DIR__ . '/../src/actions/admin/laporan_pembelanjaan.php';break;
    // RUTE PENGGUNA
    case $requestUri === '/pengguna/home': include __DIR__ . '/../views/pengguna/home.php'; break;
    case $requestUri === '/pengguna/pengambilan': include __DIR__ . '/../views/pengguna/pengambilan.php'; break;
    case $requestUri === '/pengguna/survei': include __DIR__ . '/../views/pengguna/survei.php'; break;
    // RUTE SUPPLIER
    case $requestUri === '/supplier/home': include __DIR__ . '/../views/supplier/home.php'; break;
    case $requestUri === '/supplier/permintaan': include __DIR__ . '/../views/supplier/permintaan.php'; break;
    case $requestUri === '/supplier/katalog': include __DIR__ . '/../views/supplier/katalog.php'; break;

    // ACTIONS & API
    case $requestUri === '/login-process': include __DIR__ . '/../src/auth/login_process.php'; break;
    case $requestUri === '/logout': include __DIR__ . '/../src/auth/logout_process.php'; break;
    // API HANDLERS
    case preg_match('/^\/api\/supplier\//', $requestUri): include __DIR__ . '/../src/supplier_api_handler.php'; break;
    case preg_match('/^\/api\/pengguna\//', $requestUri): include __DIR__ . '/../src/pengguna_api_handler.php'; break;
    case preg_match('/^\/api\/admin\//', $requestUri): include __DIR__ . '/../src/api_handler.php'; break;

    default:
        http_response_code(404);
        echo "<h1>404 - Halaman Tidak Ditemukan</h1><p>Path: " . htmlspecialchars($requestUri) . "</p>";
        break;
}
?>