<?php
/**
 * ===========================
 * INFO.PHP - Equipment Condition Management
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan daftar semua barang dalam sistem
 * 2. Memungkinkan update status kondisi barang (Baik/Rusak/Virus/Hilang)
 * 3. Menampilkan informasi detail setiap barang
 * 4. Mengupdate kondisi barang di database
 * 
 * Akses:
 * - Hanya untuk user dengan role 'kepala lab'
 * - Memerlukan session login yang valid
 * 
 * Kondisi Barang:
 * - Baik: Barang dalam kondisi normal dan siap digunakan
 * - Rusak: Barang rusak namun mungkin bisa diperbaiki
 * - Virus: Barang terinfeksi virus (khusus untuk komputer)
 * - Hilang: Barang hilang dari sistem
 */

// Koneksi database
if (!isset($koneksi)) {
    require_once __DIR__ . '/koneksi.php';
}

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Cek apakah user memiliki role 'kepala lab'
if (($_SESSION['user']['role'] ?? '') !== 'kepala lab') {
    header('Location: index.php');
    exit;
}

// Inisialisasi variabel
$alert = '';
$validKondisi = ['Baik', 'Rusak', 'Virus', 'Hilang']; // Kondisi yang valid

// Proses update kondisi barang ketika form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_kondisi']) && isset($_POST['kondisi'])) {
    // Loop melalui setiap barang yang dikondisikan
    foreach ((array) $_POST['kondisi'] as $idBarang => $kondisi) {
        $id = (int) $idBarang;
        $kondisi = trim((string) $kondisi);
        
        // Validasi: ID harus valid dan kondisi harus ada dalam list yang valid
        if ($id > 0 && in_array($kondisi, $validKondisi, true)) {
            $k = mysqli_real_escape_string($koneksi, $kondisi);
            // Query UPDATE untuk mengupdate kondisi barang
            mysqli_query($koneksi, "UPDATE `barang` SET `Kondisi` = '$k' WHERE `id_barang` = $id");
        }
    }
    $alert = 'Kondisi barang berhasil diperbarui.';
}

// Query untuk mengambil semua data barang beserta jenis barangnya
$sql = "SELECT b.`id_barang`, b.`nama_barang`, b.`merek`, b.`made_by`, b.`harga`, b.`Jumlah`, b.`Kondisi`, j.`nama_jenis`
        FROM `barang` b
        LEFT JOIN `jenis_barang` j ON j.`id_jenis` = b.`id_jenis`
        ORDER BY b.`id_barang` ASC";
$result = mysqli_query($koneksi, $sql);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Info Barang</h1>
</div>

<!-- Tampilkan pesan sukses jika ada -->
<?php if ($alert !== '') : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($alert, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informasi Barang Lab</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <!-- Form untuk mengupdate kondisi barang -->
            <form method="post" action="index.php?page=info">
            <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                <thead class="thead-light">
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
                    <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_barang'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_jenis'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['merek'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['made_by'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>Rp <?php echo number_format((int) ($row['harga'] ?? 0), 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['Jumlah'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <!-- Dropdown untuk memilih kondisi barang -->
                                    <?php $kondisiSekarang = (string) ($row['Kondisi'] ?? 'Baik'); ?>
                                    <select class="form-control form-control-sm" name="kondisi[<?php echo (int) $row['id_barang']; ?>]" style="min-width: 110px;">
                                        <option value="Baik" <?php echo $kondisiSekarang === 'Baik' ? 'selected' : ''; ?>>Baik</option>
                                        <option value="Rusak" <?php echo $kondisiSekarang === 'Rusak' ? 'selected' : ''; ?>>Rusak</option>
                                        <option value="Virus" <?php echo $kondisiSekarang === 'Virus' ? 'selected' : ''; ?>>Virus</option>
                                        <option value="Hilang" <?php echo $kondisiSekarang === 'Hilang' ? 'selected' : ''; ?>>Hilang</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data barang.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="mt-3">
                <!-- Tombol Simpan Kondisi -->
                <button type="submit" name="simpan_kondisi" class="btn btn-primary">Simpan Kondisi</button>
            </div>
            </form>
        </div>
    </div>
</div>
