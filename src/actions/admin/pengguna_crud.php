<?php
// src/actions/admin/pengguna_crud.php
header('Content-Type: application/json');

// Asumsi koneksi $pdo sudah ada dari file yang memanggil skrip ini
// require_once __DIR__ . '/../../config/database.php'; // Misalnya

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    $pdo->beginTransaction();

    switch ($method) {
        case 'POST': // Tambah Pengguna Baru
            if (empty($data['username']) || empty($data['password']) || empty($data['role'])) {
                throw new Exception("Username, Password, dan Role wajib diisi.");
            }
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // --- PERBAIKAN DI SINI: HAPUS TANDA KOMENTAR '//' ---
            // 1. Jalankan query untuk membuat user di tabel utama 'users'
            $sqlUser = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
            $stmtUser = $pdo->prepare($sqlUser);
            $stmtUser->execute([$data['username'], $hashedPassword, $data['role']]);
            
            // 2. Ambil ID dari user yang baru saja dibuat
            $newUserId = $pdo->lastInsertId();
            // ----------------------------------------------------
            
            // 3. (Opsional) Jika user adalah pegawai atau supplier, masukkan ke tabel masing-masing
            //    Sekarang $newUserId sudah memiliki nilai yang benar.
            if ($data['role'] === 'pegawai') {
                $sqlPegawai = "INSERT INTO pegawai (id_user, nama_pegawai) VALUES (?, ?)";
                $stmtPegawai = $pdo->prepare($sqlPegawai);
                $stmtPegawai->execute([$newUserId, $data['username']]);
            } elseif ($data['role'] === 'supplier') {
                $sqlSupplier = "INSERT INTO supplier (id_user, nama_supplier) VALUES (?, ?)";
                $stmtSupplier = $pdo->prepare($sqlSupplier);
                $stmtSupplier->execute([$newUserId, $data['username']]);
            }
            
            echo json_encode(['message' => 'Pengguna berhasil ditambahkan.']);
            break;

        case 'PUT': // Edit Pengguna
            // Dapatkan ID dari URL, contoh: /api/pengguna/15
            $pathParts = explode('/', $_SERVER['REQUEST_URI']);
            $id_user = end($pathParts);

            if (empty($id_user) || !is_numeric($id_user)) {
                throw new Exception("ID Pengguna tidak valid.");
            }
            if (empty($data['username']) || empty($data['role'])) {
                throw new Exception("Username dan Role wajib diisi.");
            }

            if (!empty($data['password'])) {
                // Query UPDATE dengan password
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = ?, password = ?, role = ? WHERE id_user = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$data['username'], $hashedPassword, $data['role'], $id_user]);
            } else {
                // Query UPDATE tanpa password
                $sql = "UPDATE users SET username = ?, role = ? WHERE id_user = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$data['username'], $data['role'], $id_user]);
            }

            // Logika update nama di tabel profil
            if ($data['role'] === 'pegawai') {
                $sqlPegawai = "UPDATE pegawai SET nama_pegawai = ? WHERE id_user = ?";
                $stmtPegawai = $pdo->prepare($sqlPegawai);
                $stmtPegawai->execute([$data['username'], $id_user]);
            } elseif ($data['role'] === 'supplier') {
                $sqlSupplier = "UPDATE supplier SET nama_supplier = ? WHERE id_user = ?";
                $stmtSupplier = $pdo->prepare($sqlSupplier);
                $stmtSupplier->execute([$data['username'], $id_user]);
            }
            
            echo json_encode(['message' => 'Pengguna berhasil diperbarui.']);
            break;

        case 'DELETE': // Hapus Pengguna
            // Dapatkan ID dari URL
            $pathParts = explode('/', $_SERVER['REQUEST_URI']);
            $id_user = end($pathParts);

            if (empty($id_user) || !is_numeric($id_user)) {
                throw new Exception("ID Pengguna tidak valid.");
            }
            // Asumsi $_SESSION['user_id'] sudah di-start
            if (isset($_SESSION['user_id']) && $id_user == $_SESSION['user_id']) {
                throw new Exception("Anda tidak dapat menghapus akun Anda sendiri.");
            }

            // --- PERBAIKAN UTAMA DIMULAI DI SINI ---

            // 1. Dapatkan dulu role user yang akan dihapus dari tabel 'users'
            $stmtRole = $pdo->prepare("SELECT role FROM users WHERE id_user = ?");
            $stmtRole->execute([$id_user]);
            $user = $stmtRole->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $role = $user['role'];
                // 2. Hapus data dari tabel anak (pegawai/supplier) berdasarkan role-nya
                if ($role === 'pegawai') {
                    $stmtChild = $pdo->prepare("DELETE FROM pegawai WHERE id_user = ?");
                    $stmtChild->execute([$id_user]);
                } elseif ($role === 'supplier') {
                    $stmtChild = $pdo->prepare("DELETE FROM supplier WHERE id_user = ?");
                    $stmtChild->execute([$id_user]);
                }
                // Jika role-nya 'admin' atau lainnya, tidak ada yang perlu dihapus dari tabel anak.
            }

            // 3. Setelah data anak dihapus (jika ada), baru hapus data dari tabel induk 'users'
            $sql = "DELETE FROM users WHERE id_user = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_user]);
            
            // --- AKHIR DARI PERBAIKAN ---
            
            echo json_encode(['message' => 'Pengguna berhasil dihapus.']);
            break;
    }
    
    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    // Cek jika error karena duplikat username
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo json_encode(['message' => 'Error: Username sudah digunakan.']);
    } else {
        echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
    }
}
?>