<?php
// src/auth/login_process.php

/**
 * File ini hanya untuk memproses data dari form login.
 * Session, koneksi database ($pdo), dan BASE_URL sudah dimuat oleh
 * file utama kita, yaitu /public/index.php.
 * Kita tidak perlu memuat ulang atau mendefinisikan ulang apa pun di sini.
 */

// Hanya proses jika request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DIPERBAIKI: Mengambil 'username' dari form, bukan 'email'
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $errors = [];

    // Validasi dasar
    if (empty($username)) {
        $errors[] = "Username harus diisi.";
    }
    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    }

    // Jika tidak ada error validasi, lanjutkan ke database
    if (empty($errors)) {
        try {
            // DIPERBAIKI: Mencari pengguna berdasarkan 'username'
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // Cek jika pengguna ditemukan DAN password cocok
            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil!
                
                // Regenerasi ID session untuk keamanan
                session_regenerate_id(true);
                
                // DIPERBAIKI: Simpan informasi pengguna ke dalam session sesuai kolom tabel baru
                $_SESSION['user_id'] = $user['id_user']; // Menggunakan 'id_user'
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['is_logged_in'] = true;

                // Arahkan pengguna ke halaman yang sesuai dengan rolenya
                $role = strtolower($user['role']);
                $redirectUrl = BASE_URL . '/login'; // Default redirect jika role tidak dikenali
                
                if ($role === 'admin') {
                    $redirectUrl = BASE_URL . '/admin/home';
                } elseif ($role === 'pegawai') { // Menyesuaikan dengan role 'pegawai' di database
                    $redirectUrl = BASE_URL . '/pengguna/home';  
                } elseif ($role === 'supplier') {
                    $redirectUrl = BASE_URL . '/supplier/home';  
                }
                
                header('Location: ' . $redirectUrl);
                exit();

            } else {
                // Jika login gagal (username atau password salah)
                $errors[] = "Username atau password yang Anda masukkan salah.";
            }

        } catch (\PDOException $e) {
            $errors[] = "Terjadi masalah koneksi ke server.";
            // Untuk debugging, Anda bisa melihat log server
            // error_log("Login PDOException: " . $e->getMessage());
        }
    }

    // Jika ada error (baik dari validasi atau login gagal),
    // simpan pesan error ke session dan kembalikan ke halaman login.
    $_SESSION['errors'] = $errors;
    header('Location: ' . BASE_URL . '/login');
    exit();

} else {
    // Jika file ini diakses langsung (bukan via POST), tendang ke halaman login
    header('Location: ' . BASE_URL . '/login');
    exit();
}