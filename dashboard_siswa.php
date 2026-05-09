<?php
/**
 * ===========================
 * DASHBOARD_SISWA.PHP - Student Dashboard
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan halaman utama/dashboard untuk siswa
 * 2. Menampilkan data pribadi siswa dari database
 * 3. Menampilkan menu navigasi untuk siswa
 * 4. Memverifikasi session siswa sebelum akses
 * 5. Menampilkan informasi dan statistik lab
 * 6. Menampilkan riwayat peminjaman siswa
 * 
 * Menu yang tersedia untuk siswa:
 * - Dashboard: Halaman utama + info pribadi + riwayat peminjaman
 * - Informasi: Daftar barang lab yang tersedia
 * - Peminjaman: Form peminjaman barang
 * - SOP: Panduan penggunaan lab
 * 
 * Data ditampilkan:
 * - Nama lengkap siswa
 * - NIS (Nomor Induk Siswa)
 * - Kelas
 * - Riwayat peminjaman barang
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

// Ambil NIS dari session
$nis = $_SESSION['siswa']['nis'] ?? '';

// Query untuk mengambil data siswa dari database
$querySiswa = mysqli_query($koneksi, "SELECT * FROM `siswa` WHERE `nis` = '$nis' LIMIT 1");
$dataSiswa = $querySiswa ? mysqli_fetch_assoc($querySiswa) : null;

// Jika siswa tidak ditemukan di database, redirect ke login
if (!$dataSiswa) {
    echo '<script>alert("Data siswa tidak ditemukan"); location.href="login_siswa.php";</script>';
    exit;
}

// Update session dengan data lengkap dari database
$_SESSION['siswa'] = $dataSiswa;

// Query untuk mengambil riwayat peminjaman siswa jika tabel tersedia
$riwayatPeminjaman = [];
$cekTabelPeminjaman = mysqli_query($koneksi, "SHOW TABLES LIKE 'peminjaman_siswa'");
if ($cekTabelPeminjaman && mysqli_num_rows($cekTabelPeminjaman) > 0) {
    $kolomPeminjaman = [];
    $qKolom = mysqli_query($koneksi, "SHOW COLUMNS FROM `peminjaman_siswa`");
    if ($qKolom) {
        while ($k = mysqli_fetch_assoc($qKolom)) {
            $field = (string) ($k['Field'] ?? '');
            if ($field !== '') {
                $kolomPeminjaman[] = $field;
            }
        }
    }

    $whereKlausa = "1=1";
    if (in_array('nis', $kolomPeminjaman, true)) {
        $whereKlausa = "ps.`nis` = '$nis'";
    } elseif (in_array('nama', $kolomPeminjaman, true) && in_array('kelas', $kolomPeminjaman, true)) {
        $namaSiswa = mysqli_real_escape_string($koneksi, (string) ($_SESSION['siswa']['nama_lengkap'] ?? ''));
        $kelasSiswa = mysqli_real_escape_string($koneksi, (string) ($_SESSION['siswa']['kelas'] ?? ''));
        $whereKlausa = "ps.`nama` = '$namaSiswa' AND ps.`kelas` = '$kelasSiswa'";
    }

    $queryPeminjaman = mysqli_query(
        $koneksi,
        "SELECT ps.*, b.`nama_barang`, b.`merek`
         FROM `peminjaman_siswa` ps
         LEFT JOIN `barang` b ON b.`id_barang` = ps.`id_barang`
         WHERE $whereKlausa
         ORDER BY COALESCE(ps.`tanggal_pinjam`, ps.`tgl_pinjam`) DESC
         LIMIT 5"
    );
    if ($queryPeminjaman) {
        while ($row = mysqli_fetch_assoc($queryPeminjaman)) {
            $riwayatPeminjaman[] = $row;
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
    <meta name="description" content="Dashboard Siswa - Sarpras Lab">
    <meta name="author" content="">

    <title>Dashboard Siswa - Lab Komputer</title>

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
            <li class="nav-item active">
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Selamat Datang di Lab Komputer</h1>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Card - Informasi Umum -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-primary font-weight-bold text-uppercase mb-1">
                                        Informasi Lab
                                    </div>
                                    <div class="h7 mb-0 font-weight-bold text-gray-800">
                                        Sistem Manajemen Lab Komputer
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card - Panduan -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-info font-weight-bold text-uppercase mb-1">
                                        Panduan
                                    </div>
                                    <div class="h7 mb-0 font-weight-bold text-gray-800">
                                        Baca SOP sebelum meminjam
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Menu Grid -->
                    <div class="row mt-4">

                        <!-- Card - Informasi -->
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow h-100 py-2">
                                <a href="informasi_siswa.php" style="text-decoration: none; color: inherit;">
                                    <div class="card-body text-center">
                                        <div class="text-primary mb-3">
                                            <i class="fas fa-info-circle fa-3x"></i>
                                        </div>
                                        <h5 class="card-title">Informasi</h5>
                                        <p class="card-text text-gray-600">Lihat informasi barang dan ketersediaan</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Card - Peminjaman -->
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow h-100 py-2">
                                <a href="peminjaman_siswa.php" style="text-decoration: none; color: inherit;">
                                    <div class="card-body text-center">
                                        <div class="text-success mb-3">
                                            <i class="fas fa-dolly fa-3x"></i>
                                        </div>
                                        <h5 class="card-title">Peminjaman</h5>
                                        <p class="card-text text-gray-600">Form peminjaman barang lab</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Card - SOP -->
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow h-100 py-2">
                                <a href="about_siswa.php" style="text-decoration: none; color: inherit;">
                                    <div class="card-body text-center">
                                        <div class="text-warning mb-3">
                                            <i class="fas fa-book fa-3x"></i>
                                        </div>
                                        <h5 class="card-title">SOP</h5>
                                        <p class="card-text text-gray-600">Standar Operasional Prosedur</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Card - Hubungi -->
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow h-100 py-2">
                                <a href="hubungi_siswa.php" style="text-decoration: none; color: inherit;">
                                    <div class="card-body text-center">
                                        <div class="text-danger mb-3">
                                            <i class="fas fa-phone fa-3x"></i>
                                        </div>
                                        <h5 class="card-title">Hubungi</h5>
                                        <p class="card-text text-gray-600">Kontak dan lokasi laboratorium</p>
                                    </div>
                                </a>
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
