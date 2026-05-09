<?php
/**
 * ===========================
 * ABOUT_SISWA.PHP - SOP (Standard Operating Procedure) Page
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan halaman SOP (Standar Operasional Prosedur) lab
 * 2. Memberikan informasi panduan penggunaan lab kepada siswa
 * 3. Memerlukan session siswa yang valid
 * 
 * Konten yang dapat ditampilkan:
 * - Tata cara menggunakan peralatan lab
 * - Aturan dan ketentuan lab
 * - Prosedur peminjaman barang
 * - Tanggung jawab siswa saat menggunakan lab
 * - Sanksi pelanggaran
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
    <meta name="description" content="SOP - Sarpras Lab">
    <meta name="author" content="">

    <title>SOP - Lab Komputer</title>

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
            <li class="nav-item active">
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
                    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-book"></i> Standar Operasional Prosedur (SOP)</h1>

                    <!-- SOP Content -->
                    <div class="row">

                        <div class="col-lg-8 mx-auto">

                            <!-- Card - SOP Umum -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-clipboard-list"></i> Prosedur Peminjaman Barang
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ol>
                                        <li><strong>Periksa Ketersediaan</strong> - Lihat daftar barang di menu Informasi</li>
                                        <li><strong>Isi Form Peminjaman</strong> - Lengkapi semua data yang diperlukan</li>
                                        <li><strong>Serahkan Form</strong> - Berikan ke petugas laboratorium</li>
                                        <li><strong>Terima Barang</strong> - Petugas akan memberikan barang beserta tanda terima</li>
                                        <li><strong>Pelihara Barang</strong> - Gunakan dengan hati-hati sesuai petunjuk</li>
                                        <li><strong>Kembalikan Tepat Waktu</strong> - Sesuai jadwal yang telah ditentukan</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Card - Peraturan Penggunaan -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-exclamation-triangle"></i> Peraturan Penggunaan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <h5>DILARANG:</h5>
                                    <ul>
                                        <li>Meminjam barang tanpa melalui prosedur</li>
                                        <li>Memberikan barang tanpa izin petugas</li>
                                        <li>Menggunakan barang di luar waktu praktik</li>
                                        <li>Merusak atau menghilangkan barang</li>
                                        <li>Mengembalikan barang dalam kondisi rusak tanpa laporan</li>
                                    </ul>

                                    <h5 class="mt-3">HARUS:</h5>
                                    <ul>
                                        <li>Mengikuti SOP penggunaan setiap barang</li>
                                        <li>Menjaga kebersihan barang</li>
                                        <li>Melaporkan jika ada kerusakan</li>
                                        <li>Mengembalikan dengan segera setelah digunakan</li>
                                        <li>Menandatangani tanda terima</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Card - Denda -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-money-bill"></i> Jadwal & Denda
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Maksimal Waktu Peminjaman:</strong> 1 (satu) minggu</p>
                                    <p><strong>Perpanjangan:</strong> Hubungi petugas sebelum masa berlaku habis</p>
                                    
                                    <h5 class="mt-3">Keterlambatan Pengembalian:</h5>
                                    <ul>
                                        <li>1-3 hari: Rp 5.000,- per hari</li>
                                        <li>4-7 hari: Rp 10.000,- per hari</li>
                                        <li>&gt;7 hari: Ganti rugi sesuai nilai barang</li>
                                    </ul>

                                    <h5 class="mt-3">Kerusakan Barang:</h5>
                                    <ul>
                                        <li>Kerusakan Ringan: 20% dari harga barang</li>
                                        <li>Kerusakan Berat: 50% dari harga barang</li>
                                        <li>Hilang/Rusak Total: 100% dari harga barang</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Card - Kontak Darurat -->
                            <div class="card shadow mb-4 border-left-danger">
                                <div class="card-header py-3 bg-danger">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-phone"></i> Kontak Darurat
                                    </h6>
                                </div>
                                <div class="card-body">
                                            <p><strong>Petugas Lab:</strong> +62-812-XXXX-XXXX</p>
                                            <p><strong>Kepala Lab Komputer:</strong> +62-812-YYYY-YYYY</p>
                                            <p><strong>Email:</strong> labkomputer@sekolah.com</p>
                                    <p><strong>Jam Operasional:</strong> Senin-Jumat, 08:00-16:00 WIB</p>
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
