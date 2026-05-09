<?php
/**
 * ===========================
 * DATA_DETAIL.PHP - View Item/Equipment Details
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan detail lengkap data barang dalam format tabel
 * 2. Memberikan link untuk mengedit data barang
 * 3. Memberikan link untuk kembali ke daftar barang
 * 
 * Persyaratan:
 * - User harus sudah login dan memiliki role 'kepala lab'
 * - ID barang harus valid dan ada di URL parameter (?id=X)
 * - Barang dengan ID tersebut harus ada di database
 * 
 * Informasi yang ditampilkan:
 * - ID Barang
 * - Jenis Barang
 * - Nama Barang
 * - Merek
 * - Produsen (Made by)
 * - Harga (dalam format Rupiah)
 * - Jumlah/Stok
 */

// Koneksi database
require_once __DIR__ . '/koneksi.php';

// Cek apakah user sudah login dan memiliki role 'kepala lab'
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'kepala lab') {
    header('Location: login.php');
    exit;
}

// Ambil ID barang dari URL parameter
$id = (int) ($_GET['id'] ?? 0);

// Validasi ID barang
if ($id < 1) {
    header('Location: index.php?page=data');
    exit;
}

// Ambil data barang dari database beserta jenis barangnya
$q = mysqli_query(
    $koneksi,
    "SELECT b.*, j.`nama_jenis` FROM `barang` b
     LEFT JOIN `jenis_barang` j ON j.`id_jenis` = b.`id_jenis`
     WHERE b.`id_barang` = $id LIMIT 1"
);
$row = $q ? mysqli_fetch_assoc($q) : null;

// Jika barang tidak ditemukan, arahkan kembali
if (!$row) {
    header('Location: index.php?page=data');
    exit;
}

// Format harga untuk ditampilkan
$harga = (int) ($row['harga'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Detail Barang — Sarpras Lab</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Tombol Kembali -->
        <a href="index.php?page=data" class="btn btn-link mb-3"><i class="fas fa-arrow-left"></i> Kembali ke Data Barang</a>
        
        <div class="card shadow">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Detail Barang</h6>
                <!-- Tombol Ubah untuk mengedit data barang -->
                <a href="data_edit.php?id=<?php echo (int) $id; ?>" class="btn btn-sm btn-info text-white">Ubah</a>
            </div>
            <div class="card-body">
                <!-- Tabel detail barang -->
                <table class="table table-bordered table-sm w-auto">
                    <!-- ID Barang -->
                    <tr><th class="bg-light" style="min-width: 160px;">ID Barang</th><td><?php echo (int) $row['id_barang']; ?></td></tr>
                    
                    <!-- Jenis Barang -->
                    <tr><th class="bg-light">Jenis</th><td><?php echo htmlspecialchars($row['nama_jenis'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    
                    <!-- Nama Barang -->
                    <tr><th class="bg-light">Nama barang</th><td><?php echo htmlspecialchars($row['nama_barang'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    
                    <!-- Merek -->
                    <tr><th class="bg-light">Merek</th><td><?php echo htmlspecialchars($row['merek'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    
                    <!-- Produsen -->
                    <tr><th class="bg-light">Made by</th><td><?php echo htmlspecialchars($row['made_by'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    
                    <!-- Harga -->
                    <tr><th class="bg-light">Harga</th><td>Rp <?php echo number_format($harga, 0, ',', '.'); ?></td></tr>
                    
                    <!-- Jumlah/Stok -->
                    <tr><th class="bg-light">Jumlah</th><td><?php echo htmlspecialchars($row['Jumlah'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
