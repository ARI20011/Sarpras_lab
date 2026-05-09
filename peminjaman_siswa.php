<?php
/**
 * ===========================
 * PEMINJAMAN_SISWA.PHP - Student Equipment Borrowing System
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan form peminjaman barang untuk siswa
 * 2. Memvalidasi input peminjaman (jumlah, tujuan penggunaan, durasi)
 * 3. Mengecek ketersediaan stok barang
 * 4. Mengecek kondisi barang (hanya barang dalam kondisi 'Baik' yang bisa dipinjam)
 * 5. Menyimpan data peminjaman ke database
 * 6. Update stok barang setelah peminjaman
 * 7. Menampilkan riwayat peminjaman siswa
 * 
 * Validasi peminjaman:
 * - Barang harus dalam kondisi 'Baik'
 * - Jumlah peminjaman tidak boleh melebihi stok
 * - Tujuan penggunaan wajib diisi
 * - Durasi peminjaman ditentukan
 * 
 * Fitur tambahan:
 * - Helper function untuk parse jumlah
 * - Helper function untuk format jumlah dengan suffix
 * - Ambil status inventaris dari database secara dinamis
 * 
 * Akses: Hanya untuk siswa yang sudah login
 */

// Koneksi database
require_once __DIR__ . '/koneksi.php';

// Cek session siswa
if (empty($_SESSION['siswa'])) {
    echo '<script>alert("Anda harus login terlebih dahulu"); location.href="login_siswa.php";</script>';
    exit;
}

/**
 * Fungsi helper: Mengekstrak angka dari string jumlah
 * Contoh: "30 Unit" -> 30
 */
function parseJumlah($jumlahText)
{
    return (int) preg_replace('/\D/', '', (string) $jumlahText);
}

/**
 * Fungsi helper: Format jumlah dengan suffix unit
 * Contoh: jika jumlahLama = "30 Unit", maka formatJumlah(25, "30 Unit") = "25 Unit"
 */
function formatJumlah($jumlahBaru, $jumlahLama)
{
    $suffix = trim((string) preg_replace('/\d+/', '', (string) $jumlahLama));
    if ($suffix !== '') {
        return $jumlahBaru . ' ' . preg_replace('/\s+/', ' ', $suffix);
    }
    return (string) $jumlahBaru;
}

/**
 * Fungsi helper: Ambil daftar status inventaris dari database
 * Menggunakan SHOW COLUMNS untuk membaca enum values dinamis
 */
function getInventarisStatusOptions($koneksi)
{
    $default = ['Sedang di pakai', 'Belum di pakai', 'Selesai dipakai'];
    $result = mysqli_query($koneksi, "SHOW COLUMNS FROM `inventaris` LIKE 'status'");
    if (!$result) {
        return $default;
    }

    $row = mysqli_fetch_assoc($result);
    $type = (string) ($row['Type'] ?? '');
    if (!preg_match("/^enum\((.*)\)$/i", $type, $matches)) {
        return $default;
    }

    $raw = str_getcsv($matches[1], ',', "'");
    $options = array_values(array_filter(array_map('trim', $raw), static function ($v) {
        return $v !== '';
    }));

    return !empty($options) ? $options : $default;
}

// Ambil lab aktif (lab pertama)
$labAktifId = null;
$qLab = mysqli_query($koneksi, "SELECT `id_lab` FROM `lab` ORDER BY `id_lab` ASC LIMIT 1");
if ($qLab && ($dLab = mysqli_fetch_assoc($qLab))) {
    $labAktifId = (int) $dLab['id_lab'];
}

// Ambil data siswa aktif dari session/database agar sinkron dengan relasi inventaris
$nisSiswaAktif = (string) ($_SESSION['siswa']['nis'] ?? '');
$idSiswaAktif = isset($_SESSION['siswa']['id_siswa']) ? (int) $_SESSION['siswa']['id_siswa'] : 0;
$kelasSiswaAktif = trim((string) ($_SESSION['siswa']['kelas'] ?? ''));

if ($nisSiswaAktif !== '') {
    $nisSafe = mysqli_real_escape_string($koneksi, $nisSiswaAktif);
    $qSiswaAktif = mysqli_query($koneksi, "SELECT `id_siswa`, `kelas` FROM `siswa` WHERE `nis` = '$nisSafe' LIMIT 1");
    if ($qSiswaAktif && ($dSiswaAktif = mysqli_fetch_assoc($qSiswaAktif))) {
        $idSiswaAktif = (int) ($dSiswaAktif['id_siswa'] ?? $idSiswaAktif);
        $kelasSiswaAktif = trim((string) ($dSiswaAktif['kelas'] ?? $kelasSiswaAktif));
    }
}

// Coba sesuaikan lab aktif berdasarkan kelas siswa melalui tabel relasi id_lab_kelas (jika tabelnya ada)
if ($kelasSiswaAktif !== '') {
    $cekRelasiLabKelas = mysqli_query($koneksi, "SHOW TABLES LIKE 'id_lab_kelas'");
    $relasiLabKelasAda = $cekRelasiLabKelas && mysqli_num_rows($cekRelasiLabKelas) > 0;

    if ($relasiLabKelasAda) {
        $kelasSafe = mysqli_real_escape_string($koneksi, $kelasSiswaAktif);
        $qLabByKelas = mysqli_query(
            $koneksi,
            "SELECT lk.`id_lab`
             FROM `id_lab_kelas` lk
             INNER JOIN `kelas` k ON k.`id_kelas` = lk.`id_kelas`
             WHERE k.`nama_kelas` = '$kelasSafe'
             ORDER BY lk.`id` ASC
             LIMIT 1"
        );
        if ($qLabByKelas && ($dLabKelas = mysqli_fetch_assoc($qLabByKelas))) {
            $labAktifId = (int) ($dLabKelas['id_lab'] ?? $labAktifId);
        }
    }
}

// Ambil daftar barang yang bisa dipinjam
$barangOptions = [];
$qBarang = mysqli_query(
    $koneksi,
    "SELECT `id_barang`, `nama_barang`, `Jumlah`, `Kondisi`
     FROM `barang`
     ORDER BY `nama_barang` ASC"
);
if ($qBarang) {
    while ($row = mysqli_fetch_assoc($qBarang)) {
        $barangOptions[] = $row;
    }
}

// Inisialisasi variabel
$formError = '';
$statusInventarisOptions = getInventarisStatusOptions($koneksi);

// Proses peminjaman ketika form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $idBarang = (int) ($_POST['id_barang'] ?? 0);
    $jumlahPinjam = (int) ($_POST['jumlah'] ?? 0);
    $tujuan = trim((string) ($_POST['tujuan'] ?? ''));
    $durasi = trim((string) ($_POST['durasi'] ?? ''));
    $catatanInput = trim((string) ($_POST['catatan'] ?? ''));

    // Validasi input form
    if ($idBarang < 1 || $jumlahPinjam < 1 || $tujuan === '') {
        $formError = 'Lengkapi data peminjaman dengan benar.';
    } else {
        // Query untuk mengambil data barang
        $qData = mysqli_query(
            $koneksi,
            "SELECT `id_barang`, `Jumlah`, `Kondisi` FROM `barang` WHERE `id_barang` = $idBarang LIMIT 1"
        );
        $barang = $qData ? mysqli_fetch_assoc($qData) : null;

        // Cek apakah barang ditemukan
        if (!$barang) {
            $formError = 'Barang tidak ditemukan.';
        } else {
            // Parse jumlah stok saat ini
            $stokSaatIni = parseJumlah($barang['Jumlah'] ?? '0');
            $kondisi = strtolower(trim((string) ($barang['Kondisi'] ?? 'baik')));

            // Validasi kondisi barang (hanya 'baik' yang bisa dipinjam)
            if ($kondisi !== 'baik') {
                $formError = 'Barang tidak bisa dipinjam karena kondisinya bukan Baik.';
            } elseif ($jumlahPinjam > $stokSaatIni) {
                // Validasi stok tersedia
                $formError = 'Jumlah pinjam melebihi stok yang tersedia.';
            } else {
                // Hitung stok baru setelah peminjaman
                $stokBaru = $stokSaatIni - $jumlahPinjam;
                $jumlahBaruText = mysqli_real_escape_string($koneksi, formatJumlah($stokBaru, $barang['Jumlah'] ?? '0'));
                
                // Update stok barang di database
                $okUpdateBarang = mysqli_query(
                    $koneksi,
                    "UPDATE `barang` SET `Jumlah` = '$jumlahBaruText' WHERE `id_barang` = $idBarang"
                );

                if ($okUpdateBarang) {
                    $statusDefault = in_array('Belum di pakai', $statusInventarisOptions, true)
                        ? 'Belum di pakai'
                        : $statusInventarisOptions[0];

                    try {
                        $inventarisColsResult = mysqli_query($koneksi, "SHOW COLUMNS FROM `inventaris`");
                        $inventarisCols = [];
                        if ($inventarisColsResult) {
                            while ($col = mysqli_fetch_assoc($inventarisColsResult)) {
                                $fieldName = (string) ($col['Field'] ?? '');
                                if ($fieldName !== '') {
                                    $inventarisCols[] = $fieldName;
                                }
                            }
                        }

                        $insertInventarisCols = [];
                        $insertInventarisVals = [];
                        $pushInventarisField = static function ($kolom, $nilai, $numeric = false) use (&$insertInventarisCols, &$insertInventarisVals, $inventarisCols, $koneksi) {
                            if (!in_array($kolom, $inventarisCols, true)) {
                                return;
                            }
                            $insertInventarisCols[] = "`$kolom`";
                            if ($numeric) {
                                $insertInventarisVals[] = (string) ((int) $nilai);
                            } elseif ($nilai === null) {
                                $insertInventarisVals[] = "NULL";
                            } else {
                                $insertInventarisVals[] = "'" . mysqli_real_escape_string($koneksi, (string) $nilai) . "'";
                            }
                        };

                        $pushInventarisField('id_lab', $labAktifId, true);
                        $pushInventarisField('id_barang', $idBarang, true);
                        $pushInventarisField('id_siswa', $idSiswaAktif, true);
                        $pushInventarisField('jumlah', $jumlahPinjam, true);
                        $pushInventarisField('status', $statusDefault, false);
                        $pushInventarisField('catatan', $catatanInput !== '' ? $catatanInput : '-', false);

                        if (empty($insertInventarisCols)) {
                            throw new RuntimeException('Kolom tabel inventaris tidak sesuai.');
                        }

                        mysqli_query(
                            $koneksi,
                            "INSERT INTO `inventaris` (" . implode(', ', $insertInventarisCols) . ")
                             VALUES (" . implode(', ', $insertInventarisVals) . ")"
                        );
                    } catch (Throwable $e) {
                        $formError = 'Gagal menyimpan ke inventaris: ' . $e->getMessage();
                    }

                    if ($formError !== '') {
                        // Kembalikan stok jika gagal insert inventaris
                        mysqli_query(
                            $koneksi,
                            "UPDATE `barang` SET `Jumlah` = '" . mysqli_real_escape_string($koneksi, $barang['Jumlah'] ?? '0') . "' WHERE `id_barang` = $idBarang"
                        );
                        // Hentikan proses agar user melihat pesan error
                    } else {

                        // Simpan juga ke tabel peminjaman_siswa untuk kebutuhan dashboard/grafik
                        $cekTabelPeminjaman = mysqli_query($koneksi, "SHOW TABLES LIKE 'peminjaman_siswa'");
                        if ($cekTabelPeminjaman && mysqli_num_rows($cekTabelPeminjaman) > 0) {
                            $kolomResult = mysqli_query($koneksi, "SHOW COLUMNS FROM `peminjaman_siswa`");
                            $kolomTersedia = [];
                            if ($kolomResult) {
                                while ($kolom = mysqli_fetch_assoc($kolomResult)) {
                                    $namaKolom = (string) ($kolom['Field'] ?? '');
                                    if ($namaKolom !== '') {
                                        $kolomTersedia[] = $namaKolom;
                                    }
                                }
                            }

                            $dataInsert = [
                                'id_barang' => (string) $idBarang,
                                'jumlah' => (string) $jumlahPinjam,
                                'tujuan' => $tujuan,
                                'durasi' => $durasi,
                                'catatan' => $catatanInput !== '' ? $catatanInput : '-',
                                'kelas' => (string) ($_SESSION['siswa']['kelas'] ?? ''),
                                'nama' => (string) ($_SESSION['siswa']['nama_lengkap'] ?? $_SESSION['siswa']['nis'] ?? ''),
                                'nis' => (string) ($_SESSION['siswa']['nis'] ?? ''),
                                'tgl_pinjam' => date('Y-m-d'),
                                'tanggal_pinjam' => date('Y-m-d H:i:s'),
                                'status' => 'Menunggu',
                            ];

                            $insertCols = [];
                            $insertVals = [];
                            foreach ($dataInsert as $kol => $val) {
                                if (in_array($kol, $kolomTersedia, true)) {
                                    $insertCols[] = "`$kol`";
                                    if (is_numeric($val) && in_array($kol, ['id_barang', 'jumlah'], true)) {
                                        $insertVals[] = (string) ((int) $val);
                                    } else {
                                        $insertVals[] = "'" . mysqli_real_escape_string($koneksi, $val) . "'";
                                    }
                                }
                            }

                            if (!empty($insertCols)) {
                                $sqlInsertPeminjaman = "INSERT INTO `peminjaman_siswa` (" . implode(', ', $insertCols) . ") VALUES (" . implode(', ', $insertVals) . ")";
                                mysqli_query($koneksi, $sqlInsertPeminjaman);
                            }
                        }

                        // Simpan ke variabel session untuk menampilkan pesan berhasil
                        $_SESSION['peminjaman_berhasil'] = true;
                        header('Location: peminjaman_siswa.php');
                        exit;
                    }
                } else {
                    $formError = 'Gagal menyimpan peminjaman.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Peminjaman - Sarpras Lab">
    <meta name="author" content="">

    <title>Peminjaman - Lab Komputer</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard_siswa.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Lab Komputer</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="dashboard_siswa.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Menu Utama
            </div>

            <!-- Nav Item - Informasi -->
            <li class="nav-item">
                <a class="nav-link" href="informasi_siswa.php">
                    <i class="fas fa-fw fa-info-circle"></i>
                    <span>Informasi</span>
                </a>
            </li>

            <!-- Nav Item - Peminjaman -->
            <li class="nav-item active">
                <a class="nav-link" href="peminjaman_siswa.php">
                    <i class="fas fa-fw fa-dolly"></i>
                    <span>Peminjaman</span>
                </a>
            </li>

            <!-- Nav Item - SOP -->
            <li class="nav-item">
                <a class="nav-link" href="about_siswa.php">
                    <i class="fas fa-fw fa-book"></i>
                    <span>SOP</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($_SESSION['siswa']['nama_lengkap'] ?? $_SESSION['siswa']['nis'] ?? 'Siswa'); ?></span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile_siswa.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout_siswa.php" onclick="return confirm('Yakin ingin logout?')">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-dolly"></i> Form Peminjaman Peralatan</h1>

                    <?php if (isset($_SESSION['peminjaman_berhasil'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="fas fa-check-circle"></i> Peminjaman Anda telah berhasil dikumpulkan! Silahkan menunggu konfirmasi dari petugas lab komputer.
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['peminjaman_berhasil']); ?>
                    <?php endif; ?>
                    <?php if ($formError !== ''): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="row">

                        <div class="col-lg-8 mx-auto">

                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Lengkapi Data Peminjaman</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <!-- Nama Barang -->
                                        <div class="form-group">
                                            <label for="id_barang"><strong>Pilih Peralatan</strong> <span class="text-danger">*</span></label>
                                            <select class="form-control" id="id_barang" name="id_barang" required>
                                                <option value="">-- Pilih Peralatan --</option>
                                                <?php
                                                $selectedBarang = (int) ($_POST['id_barang'] ?? 0);
                                                foreach ($barangOptions as $opt) {
                                                    $idOpt = (int) $opt['id_barang'];
                                                    $namaOpt = (string) ($opt['nama_barang'] ?? '-');
                                                    $jumlahOpt = (string) ($opt['Jumlah'] ?? '0');
                                                    $kondisiOpt = (string) ($opt['Kondisi'] ?? 'Baik');
                                                    $selected = $selectedBarang === $idOpt ? ' selected' : '';
                                                    echo '<option value="' . $idOpt . '"' . $selected . '>'
                                                        . htmlspecialchars($namaOpt . ' (' . $jumlahOpt . ' tersedia, kondisi: ' . $kondisiOpt . ')', ENT_QUOTES, 'UTF-8')
                                                        . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Jumlah -->
                                        <div class="form-group">
                                            <label for="jumlah"><strong>Jumlah Peminjaman</strong> <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required placeholder="Contoh: 1"
                                                value="<?php echo htmlspecialchars((string) ($_POST['jumlah'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>

                                        <!-- Tujuan Penggunaan -->
                                        <div class="form-group">
                                            <label for="tujuan"><strong>Tujuan Penggunaan</strong> <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="tujuan" name="tujuan" rows="4" required placeholder="Jelaskan untuk apa barang akan digunakan..."><?php echo htmlspecialchars((string) ($_POST['tujuan'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>

                                        <!-- Tanggal Peminjaman -->
                                        <div class="form-group">
                                            <label for="tgl_pinjam"><strong>Tanggal Peminjaman</strong></label>
                                            <input type="text" class="form-control" id="tgl_pinjam" value="<?php echo date('d-m-Y'); ?>" readonly>
                                        </div>

                                        <!-- Durasi -->
                                        <div class="form-group">
                                            <label for="durasi"><strong>Durasi Peminjaman</strong> <span class="text-danger">*</span></label>
                                            <select class="form-control" id="durasi" name="durasi" required>
                                                <option value="">-- Pilih Durasi --</option>
                                                <option value="1_hari">1 Hari</option>
                                                <option value="2_hari">2 Hari</option>
                                                <option value="3_hari">3 Hari</option>
                                                <option value="1_minggu">1 Minggu</option>
                                            </select>
                                        </div>

                                        <!-- Catatan -->
                                        <div class="form-group">
                                            <label for="catatan"><strong>Catatan Tambahan</strong></label>
                                            <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Catatan opsional..."></textarea>
                                        </div>

                                        <!-- Info Alert -->
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Penting:</strong> Pastikan Anda sudah membaca SOP sebelum mengajukan peminjaman. Barang yang rusak atau hilang akan dikenakan denda sesuai ketentuan.
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane"></i> Kirim Peminjaman
                                            </button>
                                            <button type="reset" class="btn btn-secondary">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Lab Komputer 2026</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
