<?php
// config/database.php

// Memuat variabel lingkungan dari file .env
// File ini akan disertakan oleh index.php yang sudah memuat autoloader
$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbName = $_ENV['DB_DATABASE'] ?? 'ateka';
$dbUser = $_ENV['DB_USERNAME'] ?? 'root';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';
$dbPort = $_ENV['DB_PORT'] ?? '3306';
$charset = 'utf8mb4';

// Data Source Name (DSN) untuk koneksi PDO
$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=$charset";

// Opsi untuk koneksi PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mengaktifkan mode error untuk exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mengambil data sebagai array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Menonaktifkan emulasi prepared statements untuk keamanan
];

// Membuat koneksi PDO
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (\PDOException $e) {
    // Jika koneksi gagal, hentikan aplikasi dan tampilkan pesan error
    // Di lingkungan produksi, sebaiknya log error ini dan tampilkan halaman error yang lebih ramah
    die("Koneksi database gagal: " . $e->getMessage());
}

// Variabel $pdo sekarang siap digunakan di seluruh aplikasi
// untuk melakukan query ke database.