<?php
/**
 * ===========================
 * DATA_TAMBAH.PHP - Add New Equipment/Item
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan form untuk menambah data barang baru
 * 2. Memvalidasi input dari form
 * 3. Mengecek duplikasi data barang
 * 4. Menyimpan data barang ke database
 * 5. Menampilkan pesan error jika ada masalah
 * 
 * Persyaratan:
 * - User harus sudah login dan memiliki role 'kepala lab'
 * - Form harus diisi dengan data yang valid
 * - Tidak boleh ada duplikasi nama barang dengan merek yang sama
 * 
 * Field yang disimpan:
 * - id_jenis: ID kategori barang
 * - nama_barang: Nama barang (wajib diisi)
 * - merek: Merek/model barang
 * - made_by: Nama produsen/pabrik
 * - harga: Harga dalam rupiah
 * - Jumlah: Jumlah stok barang
 */

// Koneksi database
require_once __DIR__ . '/koneksi.php';

// Cek apakah user sudah login dan memiliki role 'kepala lab'
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'kepala lab') {
    header('Location: login.php');
    exit;
}

// Inisialisasi variabel error dan flag
$err = '';
$showDuplicateAlert = false;

// Ambil daftar jenis barang dari database untuk dropdown
$jenis = mysqli_query($koneksi, 'SELECT `id_jenis`, `nama_jenis` FROM `jenis_barang` ORDER BY `nama_jenis` ASC');

// Proses penyimpanan data ketika form di-submit
if (isset($_POST['simpan'])) {
    // Ambil dan validasi input dari form
    $id_jenis   = (int) ($_POST['id_jenis'] ?? 0);
    $nama       = trim((string) ($_POST['nama_barang'] ?? ''));
    $merek      = trim((string) ($_POST['merek'] ?? ''));
    $made_by    = trim((string) ($_POST['made_by'] ?? ''));
    $harga      = (int) preg_replace('/\D/', '', (string) ($_POST['harga'] ?? 0)); // Ekstrak hanya angka
    $jumlah     = trim((string) ($_POST['Jumlah'] ?? ''));

    // Validasi: jenis barang dan nama barang wajib diisi
    if ($id_jenis < 1 || $nama === '') {
        $err = 'Jenis barang dan nama barang wajib diisi.';
    } else {
        // Escape string untuk mencegah SQL injection
        $n = mysqli_real_escape_string($koneksi, $nama);
        $m = mysqli_real_escape_string($koneksi, $merek);
        $d = mysqli_real_escape_string($koneksi, $made_by);
        $j = mysqli_real_escape_string($koneksi, $jumlah);

        // Cek duplikasi: apakah barang dengan nama dan merek yang sama sudah ada?
        $cekDuplikat = mysqli_query(
            $koneksi,
            "SELECT `id_barang` FROM `barang`
             WHERE LOWER(TRIM(`nama_barang`)) = LOWER(TRIM('$n'))
             AND LOWER(TRIM(`merek`)) = LOWER(TRIM('$m'))
             LIMIT 1"
        );

        // Jika data sudah ada, tampilkan error
        if ($cekDuplikat && mysqli_num_rows($cekDuplikat) > 0) {
            $err = 'Data sudah ada.';
            $showDuplicateAlert = true;
        } else {
            // Insert data barang baru ke database
            $ok = mysqli_query(
                $koneksi,
                "INSERT INTO `barang` (`id_jenis`, `nama_barang`, `merek`, `made_by`, `harga`, `Jumlah`) VALUES ($id_jenis, '$n', '$m', '$d', $harga, '$j')"
            );
            
            // Jika insert berhasil, arahkan ke halaman data
            if ($ok) {
                header('Location: index.php?page=data&ok=1');
                exit;
            }
            // Jika insert gagal, tampilkan error dari database
            $err = 'Gagal menyimpan: ' . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tambah Data Barang — Sarpras Lab</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Tombol Kembali -->
        <a href="index.php?page=data" class="btn btn-link mb-3"><i class="fas fa-arrow-left"></i> Kembali ke Data Barang</a>

        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tambah Data Barang</h6>
            </div>
            <div class="card-body">
                <!-- Tampilkan error jika ada -->
                <?php if ($err) : ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                
                <!-- Form tambah barang -->
                <form method="post" class="user">
                    <!-- Nama Barang dan Jenis Barang -->
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" required
                                placeholder="Masukkan nama barang" value="<?php echo isset($_POST['nama_barang']) ? htmlspecialchars((string) $_POST['nama_barang'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="id_jenis">Jenis</label>
                            <select class="form-control" id="id_jenis" name="id_jenis" required>
                                <option value="">Pilih Jenis</option>
                                <?php
                                // Populate jenis barang dropdown
                                if ($jenis) {
                                    mysqli_data_seek($jenis, 0);
                                    $sel = (int) ($_POST['id_jenis'] ?? 0);
                                    while ($j = mysqli_fetch_assoc($jenis)) {
                                        $s = (int) $j['id_jenis'] === $sel ? ' selected' : '';
                                        echo '<option value="' . (int) $j['id_jenis'] . '"' . $s . '>' . htmlspecialchars($j['nama_jenis'], ENT_QUOTES, 'UTF-8') . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Merek dan Produsen -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="merek">Merek / Model</label>
                            <input type="text" class="form-control" id="merek" name="merek" placeholder="Merek / model"
                                value="<?php echo isset($_POST['merek']) ? htmlspecialchars((string) $_POST['merek'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="made_by">Made by (Produsen)</label>
                            <input type="text" class="form-control" id="made_by" name="made_by" placeholder="Nama pabrik / pencipta"
                                value="<?php echo isset($_POST['made_by']) ? htmlspecialchars((string) $_POST['made_by'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                    </div>
                    
                    <!-- Harga dan Jumlah -->
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="harga">Harga (Rp)</label>
                            <input type="text" class="form-control" id="harga" name="harga" inputmode="numeric"
                                placeholder="0" value="<?php echo isset($_POST['harga']) ? htmlspecialchars((string) $_POST['harga'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="Jumlah">Jumlah (stok)</label>
                            <input type="text" class="form-control" id="Jumlah" name="Jumlah" placeholder="Contoh: 30 Unit"
                                value="<?php echo isset($_POST['Jumlah']) ? htmlspecialchars((string) $_POST['Jumlah'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                    </div>
                    
                    <!-- Tombol Submit dan Reset -->
                    <div class="mt-3">
                        <button type="submit" name="simpan" class="btn btn-primary">Simpan Data</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                        <a href="index.php?page=data" class="btn btn-danger">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php if ($showDuplicateAlert) : ?>
    <script>alert('data sudah ada');</script>
    <?php endif; ?>
</body>
</html>
