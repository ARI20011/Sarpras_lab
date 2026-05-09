<?php
/**
 * ===========================
 * RIPORT.PHP - Equipment Report/Laporan
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan laporan lengkap data barang lab
 * 2. Menampilkan statistik ringkas (total jenis, total unit)
 * 3. Menampilkan tanggal laporan
 * 4. Menyediakan fitur print untuk mencetak laporan
 * 
 * Informasi laporan:
 * - Total jenis barang di lab
 * - Total unit barang secara keseluruhan
 * - Daftar lengkap barang dengan detail:
 *   * ID Barang
 *   * Nama barang
 *   * Jenis barang
 *   * Merek
 *   * Produsen
 *   * Harga
 *   * Jumlah/Stok
 *   * Kondisi
 * 
 * Fitur:
 * - Print report (menggunakan window.print())
 * - Tanggal dan jam laporan
 * 
 * Akses: Hanya untuk user yang sudah login
 */

// Koneksi database
if (!isset($koneksi)) {
    include __DIR__ . '/koneksi.php';
}

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('location:login.php');
    exit;
}

// Array untuk menyimpan data barang
$dataBarang = [];

// Query untuk mengambil semua data barang dengan join ke tabel jenis_barang
$queryBarang = mysqli_query(
    $koneksi,
    "SELECT b.id_barang, b.nama_barang, j.nama_jenis, b.merek, b.made_by, b.harga, b.Jumlah, b.Kondisi
     FROM barang b
     LEFT JOIN jenis_barang j ON j.id_jenis = b.id_jenis
     ORDER BY b.id_barang ASC"
);

// Fetch semua data barang
if ($queryBarang) {
    while ($row = mysqli_fetch_assoc($queryBarang)) {
        $dataBarang[] = $row;
    }
}

// Hitung total jenis barang (jumlah baris)
$totalBarang = count($dataBarang);

// Hitung total unit barang (jumlah semua stok)
$totalUnit = 0;
foreach ($dataBarang as $barang) {
    $totalUnit += (int) ($barang['Jumlah'] ?? 0);
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Laporan Data Barang Lab</h6>
        <!-- Tombol Print untuk mencetak laporan -->
        <button class="btn btn-sm btn-success" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
    <div class="card-body">
        <!-- Informasi ringkas laporan -->
        <div class="mb-3">
            <p class="mb-1"><strong>Tanggal Laporan:</strong> <?php echo date('d-m-Y H:i'); ?></p>
            <p class="mb-1"><strong>Total Jenis Barang:</strong> <?php echo $totalBarang; ?></p>
            <p class="mb-0"><strong>Total Unit:</strong> <?php echo $totalUnit; ?></p>
        </div>

        <!-- Tabel laporan detail barang -->
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Jenis</th>
                        <th>Merek</th>
                        <th>Made By</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dataBarang)) : ?>
                        <?php $no = 1; ?>
                        <?php foreach ($dataBarang as $barang) : ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($barang['nama_barang'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($barang['nama_jenis'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($barang['merek'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($barang['made_by'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>Rp <?php echo number_format((int) ($barang['harga'] ?? 0), 0, ',', '.'); ?></td>
                                <td><?php echo (int) ($barang['Jumlah'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($barang['Kondisi'] ?? 'Baik', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center">Data barang belum tersedia.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
