<?php
/**
 * ===========================
 * LOGIN_SISWA.PHP - Student Login Page
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan form login siswa dengan input NIS (Nomor Induk Siswa)
 * 2. Memvalidasi input NIS dari siswa
 * 3. Menyimpan data NIS ke session
 * 4. Mengarahkan siswa ke form_data_siswa.php untuk melengkapi data
 * 
 * Catatan:
 * - Login siswa menggunakan tabel `siswa` di database
 * - Jika NIS sudah terdaftar, siswa langsung diarahkan ke dashboard
 * - Jika belum terdaftar, siswa akan melengkapi data pribadi
 */


require_once __DIR__ . '/koneksi.php';

// Proses login siswa dengan NIS
if (isset($_POST['login'])) {
    // Ambil NIS dari input form
    $nis = trim($_POST['nis']);
    if ($nis === '') {
        echo '<script>alert("Silahkan masukkan NIS"); location.href="login_siswa.php";</script>';
        exit;
    }

    $nis_safe = mysqli_real_escape_string($koneksi, $nis);
    $query = mysqli_query($koneksi, "SELECT * FROM `siswa` WHERE `nis` = '$nis_safe' LIMIT 1");

    if ($query && mysqli_num_rows($query) > 0) {
        $siswa_data = mysqli_fetch_assoc($query);
        $_SESSION['siswa'] = $siswa_data;
        echo '<script>location.href="dashboard_siswa.php";</script>';
        exit;
    }

    // Jika data belum ada, simpan NIS saja dan lanjutkan ke form input data siswa
    $_SESSION['siswa'] = array(
        'nis' => $nis
    );
    echo '<script>location.href="form_data_siswa.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Login Siswa - Lab Komputer">
    <meta name="author" content="">

    <title>Login Siswa - Lab Komputer</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-2">Login Siswa</h1>
                                        <p class="text-gray-600 mb-4">Lab Komputer</p>
                                    </div>

                                    <!-- Form Login Siswa dengan NIS -->
                                    <form method="POST" class="user">
                                        <div class="form-group">
                                            <!-- Input NIS (Nomor Induk Siswa) -->
                                            <input type="text" class="form-control form-control-user" placeholder="Masukkan NIS..." name="nis" required autofocus>
                                        </div>
                                        <!-- Tombol Login -->
                                        <button type="submit" name="login" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                        <hr>
                                    </form>

                                    <div class="text-center">
                                        <!-- Link ke Admin Login -->
                                        <a class="small" href="login.php">Login Sebagai Admin</a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
