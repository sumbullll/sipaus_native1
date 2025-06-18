<?php
// src/auth/logout_process.php

// Session sudah dimulai oleh index.php, jadi kita bisa langsung gunakan

// 1. Kosongkan semua data di dalam variabel $_SESSION
$_SESSION = [];

// 2. Hapus cookie session dari browser
// Ini adalah praktik yang baik untuk memastikan sesi benar-benar hilang
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan sesi secara permanen di server
session_destroy();

// 4. Arahkan pengguna kembali ke halaman login
// Variabel BASE_URL sudah tersedia karena file ini dipanggil dari index.php
header('Location: ' . BASE_URL . '/login');
exit(); // Pastikan tidak ada kode lain yang dieksekusi setelah ini

?>