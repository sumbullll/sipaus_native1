header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Dapatkan id_supplier dari user yang login
    $stmtSupplier = $pdo->prepare("SELECT id_supplier FROM supplier WHERE id_user = ?");
    $stmtSupplier->execute([$_SESSION['user_id']]);
    $supplier = $stmtSupplier->fetch();
    if (!$supplier) {
        throw new Exception("Profil supplier tidak valid.");
    }
    $id_supplier = $supplier['id_supplier'];

    $pdo->beginTransaction();

    switch ($method) {
        case 'GET':
            // Mengambil semua data katalog milik supplier yang login
            $stmt = $pdo->prepare("
                SELECT 
                    sk.id_katalog, 
                    sk.nama_katalog_atk, 
                    sk.id_jenis_atk, 
                    sk.harga, 
                    ja.jenis_atk as kategori 
                FROM supplier_katalog sk
                JOIN jenis_atk ja ON sk.id_jenis_atk = ja.id_jenis_atk
                WHERE sk.id_supplier = ?
                ORDER BY sk.nama_katalog_atk ASC
            ");
            $stmt->execute([$id_supplier]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'POST':
            $sql = "INSERT INTO supplier_katalog (id_supplier, nama_katalog_atk, id_jenis_atk, harga) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_supplier, $data['nama_katalog_atk'], $data['id_jenis_atk'], $data['harga']]);
            echo json_encode(['message' => 'Barang berhasil ditambahkan ke katalog.']);
            break;

        case 'PUT':
            $id_katalog = basename($_SERVER['REQUEST_URI']);
            $sql = "UPDATE supplier_katalog SET nama_katalog_atk = ?, id_jenis_atk = ?, harga = ? WHERE id_katalog = ? AND id_supplier = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['nama_katalog_atk'], $data['id_jenis_atk'], $data['harga'], $id_katalog, $id_supplier]);
            echo json_encode(['message' => 'Data katalog berhasil diperbarui.']);
            break;

        case 'DELETE':
            $id_katalog = basename($_SERVER['REQUEST_URI']);
            $sql = "DELETE FROM supplier_katalog WHERE id_katalog = ? AND id_supplier = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_katalog, $id_supplier]);
            echo json_encode(['message' => 'Barang berhasil dihapus dari katalog.']);
            break;
    }

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>