<?php
/**
 * ===========================
 * INVENTARIS.PHP - Inventory Management
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan riwayat penambahan inventaris lab
 * 2. Menampilkan status penggunaan barang di lab
 * 3. Memungkinkan update status penggunaan barang
 * 4. Memungkinkan menghapus record inventaris
 * 
 * Status Inventaris:
 * - Sedang di pakai: Barang sedang digunakan di lab
 * - Belum di pakai: Barang masih baru/belum digunakan
 * - Selesai dipakai: Barang sudah selesai digunakan
 * 
 * Akses:
 * - Memerlukan session login yang valid
 * - Semua user yang sudah login bisa melihat
 * - Update/delete memerlukan role 'kepala lab' (bisa ditambahkan validasi)
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

// Fungsi untuk mengambil status opsi dari database (enum values)
function getInventarisStatusOptions($koneksi)
{
    // Default status yang akan digunakan jika database error
    $default = ['Sedang di pakai', 'Belum di pakai', 'Selesai dipakai'];
    
    // Query untuk mengecek tipe kolom status
    $result = mysqli_query($koneksi, "SHOW COLUMNS FROM `inventaris` LIKE 'status'");
    if (!$result) {
        return $default;
    }

    // Ambil info kolom
    $row = mysqli_fetch_assoc($result);
    $type = (string) ($row['Type'] ?? '');
    
    // Parse enum values dari tipe kolom
    if (!preg_match("/^enum\((.*)\)$/i", $type, $matches)) {
        return $default;
    }

    // Extract nilai-nilai enum
    $raw = str_getcsv($matches[1], ',', "'");
    $options = array_values(array_filter(array_map('trim', $raw), static function ($v) {
        return $v !== '';
    }));

    return !empty($options) ? $options : $default;
}

// Ambil daftar status yang valid
$statusOptions = getInventarisStatusOptions($koneksi);

// Cek apakah tabel peminjaman_siswa ada (query inventaris & ringkasan hanya join jika ada)
$tabelPeminjamanSiswaAda = false;
$cekTabelPeminjaman = mysqli_query($koneksi, "SHOW TABLES LIKE 'peminjaman_siswa'");
if ($cekTabelPeminjaman && mysqli_num_rows($cekTabelPeminjaman) > 0) {
    $tabelPeminjamanSiswaAda = true;
}

// Ambil data peminjaman siswa untuk summary aktivitas per kelas
$peminjamanByKelas = [];
$totalPeminjaman = 0;
if ($tabelPeminjamanSiswaAda) {
    $queryPeminjamanKelas = mysqli_query(
        $koneksi,
        "SELECT `kelas`, COUNT(*) AS total FROM `peminjaman_siswa` GROUP BY `kelas` ORDER BY total DESC"
    );
    if ($queryPeminjamanKelas) {
        while ($row = mysqli_fetch_assoc($queryPeminjamanKelas)) {
            $kelas = trim($row['kelas'] ?? 'Tidak Diketahui');
            if ($kelas === '') $kelas = 'Tidak Diketahui';
            $peminjamanByKelas[$kelas] = (int) ($row['total'] ?? 0);
            $totalPeminjaman += $peminjamanByKelas[$kelas];
        }
    }
}

// Query inventaris: join peminjaman_siswa hanya jika tabelnya ada (hindari fatal error)
if ($tabelPeminjamanSiswaAda) {
    $resultInventaris = mysqli_query(
        $koneksi,
        "SELECT i.`id_inventaris`, i.`id_barang`, i.`jumlah`, i.`status`, i.`catatan`, b.`nama_barang`,
                COALESCE(s.`nama_lengkap`, ps.`nama`, '-') AS nama_siswa,
                COALESCE(s.`kelas`, ps.`kelas`, '-') AS kelas
         FROM `inventaris` i
         LEFT JOIN `barang` b ON b.`id_barang` = i.`id_barang`
         LEFT JOIN `siswa` s ON s.`id_siswa` = i.`id_siswa`
         LEFT JOIN `peminjaman_siswa` ps ON ps.`id_barang` = i.`id_barang` AND ps.`nama` = s.`nama_lengkap`
         ORDER BY i.`id_inventaris` DESC"
    );
} else {
    $resultInventaris = mysqli_query(
        $koneksi,
        "SELECT i.`id_inventaris`, i.`id_barang`, i.`jumlah`, i.`status`, i.`catatan`, b.`nama_barang`,
                COALESCE(s.`nama_lengkap`, '-') AS nama_siswa, COALESCE(s.`kelas`, '-') AS kelas
         FROM `inventaris` i
         LEFT JOIN `barang` b ON b.`id_barang` = i.`id_barang`
         LEFT JOIN `siswa` s ON s.`id_siswa` = i.`id_siswa`
         ORDER BY i.`id_inventaris` DESC"
    );
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Inventaris Lab</h1>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="text-info font-weight-bold text-uppercase mb-1">
                    Total Peminjaman
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?php echo number_format($totalPeminjaman); ?> peminjaman
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-success font-weight-bold text-uppercase mb-1">
                    Kelas yang Bergabung
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?php echo count($peminjamanByKelas); ?> kelas
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Aktivitas Peminjaman per Kelas</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($peminjamanByKelas)) : ?>
                    <div class="table-responsive" style="max-height: 250px;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th class="text-right">Total Peminjaman</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($peminjamanByKelas as $kelas => $total) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($kelas, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-right font-weight-bold"><?php echo number_format($total); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <p class="text-muted text-center">Belum ada data peminjaman</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Riwayat Inventaris</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Penambahan Inventaris</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>ID Inventaris</th>
                        <th>Nama Barang</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultInventaris && mysqli_num_rows($resultInventaris) > 0) : ?>
                        <?php while ($row = mysqli_fetch_assoc($resultInventaris)) : ?>
                            <?php $idInventaris = (int) ($row['id_inventaris'] ?? 0); ?>
                            <tr>
                                <td><?php echo $idInventaris; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_barang'] ?? ('Barang #' . (int) ($row['id_barang'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_siswa'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['kelas'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) ($row['jumlah'] ?? 0); ?></td>
                                <td>
                                    <form method="POST" action="inventaris_hapus.php" class="form-inline d-inline">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="id_inventaris" value="<?php echo $idInventaris; ?>">
                                        <select name="status" class="form-control form-control-sm mr-2" required>
                                            <?php foreach ($statusOptions as $statusOption) : ?>
                                                <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?php echo (($row['status'] ?? '') === $statusOption) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Ubah</button>
                                    </form>
                                </td>
                                <td><?php echo htmlspecialchars($row['catatan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <form method="POST" action="inventaris_hapus.php" class="d-inline" style="display: inline;">
                                        <input type="hidden" name="delete_id" value="<?php echo $idInventaris; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?');">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data inventaris.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
