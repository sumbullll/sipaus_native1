<?php
// actions/admin/pesan_atk_crud.php (Versi Final yang Disempurnakan)

// Pastikan path ini benar menuju file koneksi database Anda
require_once __DIR__ . '/../../../config/database.php'; 

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            // --- Logika untuk mengambil daftar tawaran untuk satu item (Tidak Diubah) ---
            if ($action === 'get_tawaran_by_id') {
                $id_detail_pengadaan = $_GET['id_detail_pengadaan'] ?? null;
                if (!$id_detail_pengadaan || !is_numeric($id_detail_pengadaan)) {
                    throw new Exception("ID Detail Pengadaan tidak valid.");
                }

                $sql = "
                    SELECT
                        ts.id_tawaran, ts.id_supplier, s.nama_supplier, ts.harga_tawaran
                    FROM tawaran_supplier ts
                    JOIN supplier s ON ts.id_supplier = s.id_supplier
                    WHERE ts.id_detail_pengadaan = ?
                    ORDER BY ts.harga_tawaran ASC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id_detail_pengadaan]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                break; 
            }
            
            // --- Kueri GET utama untuk menampilkan SEMUA item yang relevan (Tidak Diubah) ---
            $stmt = $pdo->query("
                SELECT 
                    dp.id_detail_pengadaan, p.id_pengadaan, p.tanggal_pengadaan, dp.status, dp.jumlah,
                    ja.jenis_atk AS kategori_barang,
                    s.nama_supplier,
                    (SELECT COUNT(*) FROM tawaran_supplier ts WHERE ts.id_detail_pengadaan = dp.id_detail_pengadaan) AS jumlah_penawaran
                FROM detail_pengadaan dp
                JOIN pengadaan p ON dp.id_pengadaan = p.id_pengadaan
                JOIN jenis_atk ja ON dp.id_jenis_atk = ja.id_jenis_atk
                LEFT JOIN supplier s ON dp.id_supplier = s.id_supplier
                WHERE dp.status != 'selesai'
                ORDER BY p.tanggal_pengadaan DESC, dp.id_detail_pengadaan DESC
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'POST':
            $pdo->beginTransaction();

            // --- Logika untuk memilih pemenang (Disempurnakan) ---
            if ($action === 'pilih_pemenang') {
                $id_detail = $data['id_detail_pengadaan'] ?? null;
                $id_tawaran = $data['id_tawaran_terpilih'] ?? null;
                $id_supplier = $data['id_supplier_terpilih'] ?? null;

                if (!$id_detail || !$id_tawaran || !$id_supplier) throw new Exception("Data untuk memilih pemenang tidak lengkap.");

                // Ambil id_jenis_atk dari detail_pengadaan
                $stmt_jenis = $pdo->prepare("SELECT id_jenis_atk FROM detail_pengadaan WHERE id_detail_pengadaan = ?");
                $stmt_jenis->execute([$id_detail]);
                $jenis_atk_info = $stmt_jenis->fetch();
                if (!$jenis_atk_info) throw new Exception("Detail pengadaan tidak ditemukan.");
                $id_jenis_atk = $jenis_atk_info['id_jenis_atk'];

                // Cari id_atk pertama yang cocok di tabel ATK
                $stmt_atk = $pdo->prepare("SELECT id_atk FROM atk WHERE id_jenis_atk = ? LIMIT 1");
                $stmt_atk->execute([$id_jenis_atk]);
                $atk_info = $stmt_atk->fetch();
                $id_atk_terpilih = $atk_info['id_atk'] ?? null;

                // 1. Update detail_pengadaan: set pemenang, status, dan id_atk
                $stmt1 = $pdo->prepare("UPDATE detail_pengadaan SET id_supplier = ?, status = 'dipilih', id_atk = ? WHERE id_detail_pengadaan = ?");
                $stmt1->execute([$id_supplier, $id_atk_terpilih, $id_detail]);

                // 2. Update tawaran pemenang menjadi 'disetujui'
                $stmt2 = $pdo->prepare("UPDATE tawaran_supplier SET status_tawaran = 'disetujui' WHERE id_tawaran = ?");
                $stmt2->execute([$id_tawaran]);

                // 3. Update tawaran lainnya menjadi 'ditolak'
                $stmt3 = $pdo->prepare("UPDATE tawaran_supplier SET status_tawaran = 'ditolak' WHERE id_detail_pengadaan = ? AND id_tawaran != ?");
                $stmt3->execute([$id_detail, $id_tawaran]);
                
                $pdo->commit();
                echo json_encode(['message' => 'Supplier pemenang berhasil dipilih.']);
                break;
            }
            
            // --- Logika untuk membuat PERMINTAAN PENGADAAN BARU (tanpa supplier) ---
            if (empty($data['id_jenis_atk']) || empty($data['jumlah']) || empty($data['tanggal_pengadaan'])) {
                throw new Exception("Kategori Barang, Jumlah, dan Tanggal Pengadaan harus diisi.");
            }
            
            // 1. Buat record di 'pengadaan'. Status awal 'diproses'.
            $stmtPengadaan = $pdo->prepare("INSERT INTO pengadaan (tanggal_pengadaan, status) VALUES (?, 'diproses')");
            $stmtPengadaan->execute([$data['tanggal_pengadaan']]);
            $id_pengadaan_baru = $pdo->lastInsertId();

            // 2. Buat record di 'detail_pengadaan'. id_supplier dan id_atk dibuat NULL, status 'menunggu'.
            $sqlDetail = "INSERT INTO detail_pengadaan (id_pengadaan, id_jenis_atk, id_atk, id_supplier, jumlah, status) VALUES (?, ?, NULL, NULL, ?, 'menunggu')";
            $stmtDetail = $pdo->prepare($sqlDetail);
            $stmtDetail->execute([$id_pengadaan_baru, $data['id_jenis_atk'], $data['jumlah']]);
                
            $pdo->commit();
            echo json_encode(['message' => 'Permintaan pengadaan baru berhasil dibuat.']);
            break;

        case 'PUT':
            // --- PERBAIKAN: Logika Ubah Status Pesanan ---
            // Hanya mengubah tabel detail_pengadaan, biarkan trigger menangani sisanya
            $id_pengadaan = $_GET['id'] ?? null;
            if (!$id_pengadaan) {
                throw new Exception("ID Pengadaan untuk update tidak ditemukan.");
            }
            if (empty($data['status'])) {
                throw new Exception("Status baru harus dipilih.");
            }
            
            $sql = "UPDATE detail_pengadaan SET status = ? WHERE id_pengadaan = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['status'], $id_pengadaan]);
            
            echo json_encode(['message' => 'Status pesanan berhasil diperbarui.']);
            break;

        case 'DELETE':
            // --- FUNGSI HAPUS PESANAN (Tidak Diubah) ---
            $id_pengadaan = $_GET['id'] ?? null;
            if (!$id_pengadaan) {
                throw new Exception("ID Pengadaan untuk dihapus tidak ditemukan.");
            }

            $sql = "DELETE FROM pengadaan WHERE id_pengadaan = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_pengadaan]);
            
            echo json_encode(['message' => 'Pesanan berhasil dihapus.']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method tidak diizinkan.']);
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>