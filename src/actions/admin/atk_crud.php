<?php
// File: actions/admin/atk_crud.php
// Versi ini sudah dimodifikasi untuk Manajemen Master Barang secara penuh (CRUD).

require_once __DIR__ . '/../../../config/database.php'; // Pastikan path koneksi database ini benar

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Mengambil ID dari parameter URL (untuk PUT dan DELETE)
$id = $_GET['id'] ?? null;

try {
    switch ($method) {
        case 'GET':
            // FUNGSI BARU: Menampilkan semua data master ATK untuk tabel utama.
            $stmt = $pdo->query("
                SELECT 
                    a.id_atk, 
                    a.nama_atk, 
                    a.satuan, 
                    a.stok,
                    a.id_jenis_atk,
                    j.jenis_atk 
                FROM atk a
                JOIN jenis_atk j ON a.id_jenis_atk = j.id_jenis_atk
                ORDER BY a.nama_atk ASC
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'POST':
            // FUNGSI DIMODIFIKASI: Untuk MEMBUAT data barang baru, bukan menambah stok.
            if (empty($data['nama_atk']) || empty($data['id_jenis_atk']) || empty($data['satuan'])) {
                throw new Exception("Nama barang, kategori, dan satuan harus diisi.");
            }

            // Saat barang baru dibuat, stok awalnya selalu 0.
            $stok_awal = 0;

            $sql = "INSERT INTO atk (nama_atk, id_jenis_atk, satuan, stok) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['nama_atk'],
                $data['id_jenis_atk'],
                $data['satuan'],
                $stok_awal
            ]);

            echo json_encode(['message' => 'Barang baru berhasil ditambahkan.']);
            break;

        case 'PUT':
            // FUNGSI DIMODIFIKASI: Untuk MENGEDIT detail master barang (nama, kategori, satuan).
            if (!$id) {
                throw new Exception("ID Barang untuk di-update tidak ditemukan.");
            }

            if (empty($data['nama_atk']) || empty($data['id_jenis_atk']) || empty($data['satuan'])) {
                throw new Exception("Nama barang, kategori, dan satuan harus diisi.");
            }
            
            // Query ini tidak mengubah stok. Stok dikelola di menu lain.
            $sql = "UPDATE atk SET nama_atk = ?, id_jenis_atk = ?, satuan = ? WHERE id_atk = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['nama_atk'],
                $data['id_jenis_atk'],
                $data['satuan'],
                $id
            ]);

            echo json_encode(['message' => 'Data barang berhasil diperbarui.']);
            break;

        case 'DELETE':
            // FUNGSI DISESUAIKAN: Menggunakan $_GET['id'] untuk mengambil ID.
            if (!$id) {
                throw new Exception("ID Barang untuk dihapus tidak ditemukan.");
            }

            $sql = "DELETE FROM atk WHERE id_atk = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            echo json_encode(['message' => 'Data barang berhasil dihapus.']);
            break;
        
        default:
            http_response_code(405); // Method Not Allowed
            echo json_encode(['message' => 'Method tidak diizinkan.']);
            break;
    }
} catch (PDOException $e) {
    // Menangani error spesifik dari database
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Menangani error umum lainnya
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>