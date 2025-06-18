<?php
// File: actions/admin/laporan_pembelanjaan.php

// Memuat autoloader dari Composer dan koneksi database
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

try {
    // 1. Ambil data dari database
    // Query ini mengambil semua detail pesanan yang sudah selesai,
    // lengkap dengan nama barang, nama supplier, jumlah, dan harga penawaran yang disetujui.
    $sql = "
        SELECT 
            p.tanggal_pengadaan,
            ja.jenis_atk,
            s.nama_supplier,
            dp.jumlah,
            ts.harga_tawaran,
            (dp.jumlah * ts.harga_tawaran) AS sub_total
        FROM pengadaan p
        JOIN detail_pengadaan dp ON p.id_pengadaan = dp.id_pengadaan
        JOIN tawaran_supplier ts ON dp.id_detail_pengadaan = ts.id_detail_pengadaan AND ts.status_tawaran = 'disetujui'
        JOIN supplier s ON dp.id_supplier = s.id_supplier
        JOIN jenis_atk ja ON dp.id_jenis_atk = ja.id_jenis_atk
        WHERE p.status = 'selesai'
        ORDER BY p.tanggal_pengadaan DESC
    ";
    $stmt = $pdo->query($sql);
    $data_pembelanjaan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Siapkan data HTML untuk di-render menjadi PDF
    $total_keseluruhan = 0;
    $html = '
        <html>
        <head>
            <style>
                body { font-family: sans-serif; }
                h1 { text-align: center; border-bottom: 1px solid #000; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { font-weight: bold; }
                .text-right { text-align: right; }
            </style>
        </head>
        <body>
            <h1>Laporan Total Pembelanjaan ATK</h1>
            <p>Periode: Semua Waktu</p>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Barang</th>
                        <th>Supplier</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Sub Total</th>
                    </tr>
                </thead>
                <tbody>';

    if (count($data_pembelanjaan) > 0) {
        foreach ($data_pembelanjaan as $item) {
            $total_keseluruhan += $item['sub_total'];
            $html .= '
                <tr>
                    <td>' . date('d-m-Y', strtotime($item['tanggal_pengadaan'])) . '</td>
                    <td>' . htmlspecialchars($item['jenis_atk']) . '</td>
                    <td>' . htmlspecialchars($item['nama_supplier']) . '</td>
                    <td class="text-right">' . number_format($item['jumlah']) . '</td>
                    <td class="text-right">Rp ' . number_format($item['harga_tawaran']) . '</td>
                    <td class="text-right">Rp ' . number_format($item['sub_total']) . '</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="6" style="text-align:center;">Tidak ada data pembelanjaan yang sudah selesai.</td></tr>';
    }

    $html .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="total text-right">Total Keseluruhan</td>
                        <td class="total text-right">Rp ' . number_format($total_keseluruhan) . '</td>
                    </tr>
                </tfoot>
            </table>
        </body>
        </html>';

    // 3. Buat objek mpdf
    $mpdf = new \Mpdf\Mpdf();

    // 4. Tulis konten HTML ke PDF
    $mpdf->WriteHTML($html);

    // 5. Tampilkan PDF di browser
    // 'I' artinya Inline (tampil di browser), 'D' artinya Download
    $mpdf->Output('laporan-pembelanjaan.pdf', 'I');

} catch (Exception $e) {
    // Jika ada error, tampilkan pesan
    echo "Terjadi kesalahan saat membuat laporan: " . $e->getMessage();
}
?>