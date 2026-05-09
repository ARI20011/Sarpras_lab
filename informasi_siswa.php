<?php
/**
 * ===========================
 * INFORMASI_SISWA.PHP - Equipment Information for Students
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan daftar semua peralatan lab yang tersedia
 * 2. Menampilkan informasi detail peralatan (nama, jenis, merek, kondisi, stok)
 * 3. Memvisualisasikan data dengan card dan icon untuk setiap peralatan
 * 4. Memerlukan session siswa yang valid
 * 
 * Informasi yang ditampilkan:
 * - Nama peralatan
 * - Jenis peralatan
 * - Merek
 * - Produsen (Made by)
 * - Harga
 * - Jumlah stok
 * - Kondisi peralatan (Baik/Rusak/Virus/Hilang dengan badge warna)
 * - Icon visual untuk masing-masing jenis peralatan
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

// Array untuk menyimpan data peralatan
$dataBarang = [];

// Query untuk mengambil semua data barang dengan join ke tabel jenis_barang
$queryBarang = mysqli_query(
    $koneksi,
    "SELECT b.`id_barang`, b.`id_jenis`, b.`nama_barang`, b.`merek`, b.`made_by`, b.`harga`, b.`Jumlah`, b.`Kondisi`, j.`nama_jenis`
     FROM `barang` b
     LEFT JOIN `jenis_barang` j ON j.`id_jenis` = b.`id_jenis`
     ORDER BY b.`id_barang` ASC"
);

// Fetch semua data barang
if ($queryBarang) {
    while ($row = mysqli_fetch_assoc($queryBarang)) {
        $dataBarang[] = $row;
    }
}

// Fungsi untuk menampilkan badge kondisi dengan label dan warna
function tampilKondisi($kondisiDb)
{
    $kondisi = strtolower(trim((string) $kondisiDb));
    if ($kondisi === 'rusak') {
        return ['label' => 'Rusak', 'badge' => 'warning'];
    }
    if ($kondisi === 'virus') {
        return ['label' => 'Virus', 'badge' => 'danger'];
    }
    if ($kondisi === 'hilang') {
        return ['label' => 'Hilang', 'badge' => 'secondary'];
    }
    return ['label' => 'Baik', 'badge' => 'success'];
}

// Fungsi untuk menampilkan icon berdasarkan jenis barang
function iconBarang($namaJenis)
{
    // Map nama jenis ke icon Font Awesome
    $map = [
        'komputer' => 'fa-desktop',
        'laptop' => 'fa-laptop',
        'monitor' => 'fa-tv',
        'keyboard' => 'fa-keyboard',
        'mouse' => 'fa-mouse',
        'printer' => 'fa-print',
    ];
    $key = strtolower((string) $namaJenis);
    foreach ($map as $keyword => $icon) {
        if (strpos($key, $keyword) !== false) {
            return $icon;
        }
    }
    return 'fa-box'; // Default icon
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Informasi - Sarpras Lab">
    <meta name="author" content="">

    <title>Informasi - Lab Komputer</title>

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
            <li class="nav-item active">
                <a class="nav-link" href="informasi_siswa.php">
                    <i class="fas fa-fw fa-info-circle"></i>
                    <span>Informasi</span>
                </a>
            </li>

            <!-- Nav Item - Peminjaman -->
            <li class="nav-item">
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
                    <h1 class="h3 mb-4 text-gray-800">Informasi Peralatan Lab Komputer</h1>

                    <!-- Info Cards -->
                    <div class="row">
                        <?php if (!empty($dataBarang)) : ?>
                            <?php foreach ($dataBarang as $barang) : ?>
                                <?php
                                $idBarang = (int) ($barang['id_barang'] ?? 0);
                                $namaJenis = $barang['nama_jenis'] ?: ('Jenis #' . (int) ($barang['id_jenis'] ?? 0));
                                $namaBarang = $barang['nama_barang'] ?? '-';
                                $merek = $barang['merek'] ?? '-';
                                $madeBy = $barang['made_by'] ?? '-';
                                $jumlah = trim((string) ($barang['Jumlah'] ?? '0'));
                                $kondisi = tampilKondisi($barang['Kondisi'] ?? 'Baik');
                                $icon = iconBarang($namaJenis);
                                $modalId = 'detailBarang' . $idBarang;
                                ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card shadow h-100">
                                        <div class="card-header py-3">
                                            <h6 class="m-0 font-weight-bold text-primary">
                                                <i class="fas <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"></i>
                                                <?php echo htmlspecialchars($namaBarang, ENT_QUOTES, 'UTF-8'); ?>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Jumlah:</strong> <?php echo htmlspecialchars($jumlah, ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p><strong>Kondisi:</strong> <span class="badge badge-<?php echo htmlspecialchars($kondisi['badge'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($kondisi['label'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                            <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($merek, ENT_QUOTES, 'UTF-8'); ?></p>
                                            <small class="text-gray-600">Klik untuk informasi lebih lanjut</small>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fas fa-search"></i> Detail
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="col-12">
                                <div class="alert alert-info mb-4">
                                    Data barang belum tersedia.
                                </div>
                            </div>
                        <?php endif; ?>
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

    <!-- Detail Modals -->
    <?php foreach ($dataBarang as $barang) : ?>
        <?php
        $idBarang = (int) ($barang['id_barang'] ?? 0);
        $namaJenis = $barang['nama_jenis'] ?: ('Jenis #' . (int) ($barang['id_jenis'] ?? 0));
        $namaBarang = $barang['nama_barang'] ?? '-';
        $merek = $barang['merek'] ?? '-';
        $madeBy = $barang['made_by'] ?? '-';
        $harga = (int) ($barang['harga'] ?? 0);
        $jumlah = trim((string) ($barang['Jumlah'] ?? '0'));
        $kondisi = tampilKondisi($barang['Kondisi'] ?? 'Baik');
        $icon = iconBarang($namaJenis);
        $modalId = 'detailBarang' . $idBarang;
        ?>
        <div class="modal fade" id="<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"></i>
                            Detail <?php echo htmlspecialchars($namaBarang, ENT_QUOTES, 'UTF-8'); ?>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Nama Barang:</strong> <?php echo htmlspecialchars($namaBarang, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Jenis:</strong> <?php echo htmlspecialchars($namaJenis, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Merek:</strong> <?php echo htmlspecialchars($merek, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Made by:</strong> <?php echo htmlspecialchars($madeBy, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Harga:</strong> Rp <?php echo number_format($harga, 0, ',', '.'); ?></p>
                        <p><strong>Jumlah Tersedia:</strong> <?php echo htmlspecialchars($jumlah, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Kondisi:</strong> <?php echo htmlspecialchars($kondisi['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
