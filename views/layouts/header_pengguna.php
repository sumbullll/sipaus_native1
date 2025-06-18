<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['is_logged_in'])) { header('Location: ' . BASE_URL . '/login'); exit(); }
$allowed_roles = ['pegawai', 'pengguna'];
if (!in_array(strtolower($_SESSION['role']), $allowed_roles)) { http_response_code(403); die('Akses Ditolak.'); }
$username = $_SESSION['username'] ?? 'Pengguna';
function isPenggunaMenuActive($uri) { return str_contains($_SERVER['REQUEST_URI'], $uri); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SiManis - <?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin/admin_common.css">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="profile" id="profile">
            <div class="profile-img"><i class="fas fa-user-circle"></i></div>
            <div><?php echo htmlspecialchars($username); ?></div>
        </div>
        <div class="menu">
            <a href="<?php echo BASE_URL; ?>/pengguna/home" class="menu-item <?php echo isPenggunaMenuActive('/pengguna/home') ? 'active' : ''; ?>"><i class="fas fa-home"></i> <span>HOME</span></a>
            <a href="<?php echo BASE_URL; ?>/pengguna/pengambilan" class="menu-item <?php echo isPenggunaMenuActive('/pengguna/pengambilan') ? 'active' : ''; ?>"><i class="fas fa-box-open"></i> <span>Pengambilan Barang</span></a>
            <a href="<?php echo BASE_URL; ?>/pengguna/survei" class="menu-item <?php echo isPenggunaMenuActive('/pengguna/survei') ? 'active' : ''; ?>"><i class="fas fa-clipboard-list"></i> <span>Isi Survei</span></a>
            <a href="#" id="logout-btn-link" class="menu-item"><i class="fas fa-sign-out-alt"></i> <span>Log out</span></a>
        </div>
    </div>
    <div id="logout-popup" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div class="popup-content" style="background: white; padding: 20px; border-radius: 5px; text-align: center;">
            <p>Apakah Anda yakin ingin logout?</p>
            <form id="logout-form" action="<?php echo BASE_URL; ?>/logout" method="POST" style="display:none;"></form>
            <button type="button" class="confirm-logout" id="confirm-logout-btn">Logout</button>
            <button type="button" class="cancel-logout" id="cancel-logout">Batal</button>
        </div>
    </div>
    <div class="main-content" id="main-content">
        <div class="header">
            <button class="toggle-btn" id="toggle-btn"><i class="fas fa-bars"></i></button>
            <div class="brand">SiManis</div>
            <span><?php echo htmlspecialchars($headerTitle ?? 'Dashboard'); ?></span> 
        </div>