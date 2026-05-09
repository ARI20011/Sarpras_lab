<?php
/**
 * ===========================
 * PROFILE_SISWA.PHP - Student Profile Page
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan halaman profil siswa
 * 2. Menampilkan informasi data pribadi siswa
 * 3. Memerlukan session siswa yang valid
 * 
 * Informasi yang dapat ditampilkan:
 * - Nama lengkap siswa
 * - NIS (Nomor Induk Siswa)
 * - Kelas
 * - Email
 * - Nomor telepon
 * - Tanggal pendaftaran
 * 
 * Fungsi tambahan:
 * - Edit profil (jika diimplementasikan)
 * - Ubah password (jika diimplementasikan)
 * 
 * Akses: Hanya untuk siswa yang sudah login
 */

session_start();

// Cek session siswa
if (empty($_SESSION['siswa'])) {
    echo '<script>alert("Anda harus login terlebih dahulu"); location.href="login_siswa.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Profile Siswa - Sarpras Lab">
    <meta name="author" content="">

    <title>Profile Siswa - Lab Komputer</title>

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
                    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-user-circle"></i> Profile Siswa</h1>

                    <div class="row">

                        <div class="col-lg-6 mx-auto">

                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Informasi Pribadi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label><strong>Nama Lengkap</strong></label>
                                        <div class="border rounded p-3 bg-light">
                                            <?php echo htmlspecialchars($_SESSION['siswa']['nama_lengkap'] ?? '-'); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Nomor Induk Siswa (NIS)</strong></label>
                                        <div class="border rounded p-3 bg-light">
                                            <?php echo htmlspecialchars($_SESSION['siswa']['nis'] ?? '-'); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Kelas</strong></label>
                                        <div class="border rounded p-3 bg-light">
                                            <?php echo htmlspecialchars($_SESSION['siswa']['kelas'] ?? '-'); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Email</strong></label>
                                        <div class="border rounded p-3 bg-light">
                                            <?php echo htmlspecialchars($_SESSION['siswa']['email'] ?? '-'); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>No. Telepon</strong></label>
                                        <div class="border rounded p-3 bg-light">
                                            <?php echo htmlspecialchars($_SESSION['siswa']['no_telepon'] ?? '-'); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Status Akun</strong></label>
                                        <div class="border rounded p-3 bg-light">
                                            <span class="badge badge-success">Aktif</span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Status Peminjaman</strong></label>
                                        <div class="border rounded p-3 bg-light">
                                            <span class="badge badge-info">Tidak Ada Hutang</span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Catatan -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Informasi:</strong> Data di atas adalah data akun siswa Anda. Untuk mengubah data, hubungi petugas administrasi.
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
