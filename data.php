<?php
/**
 * ===========================
 * DATA.PHP - Equipment/Item Management List
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan daftar semua barang yang ada di lab
 * 2. Menyediakan search/filter untuk mencari barang
 * 3. Menyediakan tombol CRUD (Create, Read, Update, Delete)
 * 4. Menampilkan data dalam bentuk tabel yang responsif
 * 
 * Fitur:
 * - Search berdasarkan nama barang
 * - Filter berdasarkan merek barang
 * - Filter berdasarkan produsen (made_by)
 * - Tombol tambah barang baru
 * - Link edit untuk mengubah barang
 * - Link delete untuk menghapus barang
 * - Link detail untuk melihat detail barang
 * 
 * Akses:
 * - Hanya untuk user dengan role 'kepala lab'
 * - Memerlukan session login yang valid
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

// Ambil parameter search dari URL
$cari_nama  = isset($_GET['cari_nama']) ? trim((string) $_GET['cari_nama']) : '';
$cari_merek = isset($_GET['cari_merek']) ? trim((string) $_GET['cari_merek']) : '';
$cari_made  = isset($_GET['cari_made']) ? trim((string) $_GET['cari_made']) : '';

// Bangun WHERE clause untuk query berdasarkan search parameter
$where = ['1=1']; // Base condition
if ($cari_nama !== '') {
    $where[] = "`nama_barang` LIKE '%" . mysqli_real_escape_string($koneksi, $cari_nama) . "%'";
}
if ($cari_merek !== '') {
    $where[] = "`merek` LIKE '%" . mysqli_real_escape_string($koneksi, $cari_merek) . "%'";
}
if ($cari_made !== '') {
    $where[] = "`made_by` LIKE '%" . mysqli_real_escape_string($koneksi, $cari_made) . "%'";
}

// Query untuk mengambil data barang dengan filter
$sql = 'SELECT `id_barang`, `id_jenis`, `nama_barang`, `merek`, `made_by`, `harga`, `Jumlah` FROM `barang` WHERE ' . implode(' AND ', $where) . ' ORDER BY `id_barang` ASC';
$result = mysqli_query($koneksi, $sql);
?>

<?php if (!empty($_GET['ok'])) : ?>
    <!-- Tampilkan alert sukses jika ada parameter ok di URL -->
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Data berhasil disimpan.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
<?php endif; ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Data Barang</h1>
    <!-- Tombol Tambah Data (hanya di desktop) -->
    <a href="data_tambah.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Data
    </a>
</div>

<!-- Card untuk Search/Filter -->
<div class="card shadow mb-3">
    <div class="card-body py-3">
        <form method="get" action="index.php" class="w-100">
            <input type="hidden" name="page" value="data">
            <div class="form-row align-items-end">
                <!-- Input Search Nama Barang -->
                <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                    <label for="cari_nama" class="small text-gray-600 mb-1">Nama Barang</label>
                    <input type="text" class="form-control rounded" name="cari_nama" id="cari_nama"
                        value="<?php echo htmlspecialchars($cari_nama, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Nama barang">
                </div>
                <!-- Input Filter Merek -->
                <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                    <label for="cari_merek" class="small text-gray-600 mb-1">Merek</label>
                    <input type="text" class="form-control rounded" name="cari_merek" id="cari_merek"
                        value="<?php echo htmlspecialchars($cari_merek, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Merek / model">
                </div>
                <!-- Input Filter Produsen -->
                <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                    <label for="cari_made" class="small text-gray-600 mb-1">Made by</label>
                    <input type="text" class="form-control rounded" name="cari_made" id="cari_made"
                        value="<?php echo htmlspecialchars($cari_made, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Produsen / pabrik">
                </div>
                <!-- Tombol Cari -->
                <div class="form-group col-md-3 col-sm-6 mb-0 text-md-right">
                    <button type="submit" class="btn btn-primary btn-block rounded">
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tombol Tambah Data (hanya di mobile) -->
<div class="d-sm-none mb-3">
    <a href="data_tambah.php" class="btn btn-sm btn-primary btn-block"><i class="fas fa-plus"></i> Tambah Data</a>
</div>

<style>
@media (max-width: 576px) {
    #dataTableBarang .btn {
        width: 100%;
        margin-bottom: 4px;
    }
}
</style>

<!-- Tabel Data Barang -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Barang (tabel barang)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="dataTableBarang" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>No.</th>
                        <th>id_barang</th>
                        <th>id_jenis</th>
                        <th>nama_barang</th>
                        <th>merek</th>
                        <th>made_by</th>
                        <th>harga</th>
                        <th>Jumlah</th>
                        <th style="min-width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $harga = isset($row['harga']) ? (int) $row['harga'] : 0;
                            $id    = (int) $row['id_barang'];
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $id; ?></td>
                                <td><?php echo (int) $row['id_jenis']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_barang'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['merek'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['made_by'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo number_format($harga, 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['Jumlah'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="data_detail.php?id=<?php echo $id; ?>" class="btn btn-sm btn-primary" title="Detail">Detail</a>
                                    <a href="data_edit.php?id=<?php echo $id; ?>" class="btn btn-sm btn-info text-white" title="Ubah">Ubah</a>
                                    <a href="data_hapus.php?id=<?php echo $id; ?>"
                                        class="btn btn-sm btn-danger"
                                        title="Hapus"
                                        onclick="return confirm('Hapus barang ini?');">Hapus</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <?php echo $result ? 'Tidak ada data yang cocok / belum ada data barang.' : 'Gagal memuat data: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
