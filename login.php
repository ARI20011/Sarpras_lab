<?php
/**
 * ===========================
 * LOGIN.PHP - Admin/Staff Login Page
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menampilkan form login untuk admin/staff
 * 2. Memvalidasi username dan password
 * 3. Menyimpan data user ke session jika login berhasil
 * 4. Mengarahkan ke dashboard jika login sukses
 * 
 * Autentikasi:
 * - Username: dari input form
 * - Password: dari input form (di-hash dengan MD5 sebelum dicocokkan)
 * - Database: table 'user' di database 'sarpras_lab'
 */

// Koneksi ke database
include "koneksi.php";


?>
<!DOCTYPE html>
<html lang="en">


<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Login - Sistem Perpustakaan</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

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
                                        <h1 class="h4 text-gray-900 mb-4">Login Sarpras_lab</h1>
                                        <?php if (!empty($error)): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <?php echo htmlspecialchars($error); ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    // Cek apakah form login di-submit
                                    if (isset($_POST['login'])) {
                                        // Ambil input username dan password dari form
                                        $username = $_POST['username'];
                                        $password = md5($_POST['password']); // Hash password dengan MD5

                                        // Query ke database untuk mencari user dengan username dan password yang sesuai
                                        $data = mysqli_query($koneksi, "SELECT * FROM user
                                         WHERE username='$username' AND password='$password'");
                                        $cek = mysqli_num_rows($data);                                    
                                                                      
            
                                            // Jika user ditemukan (cek > 0)
                                            if ($cek > 0){
                                            // Simpan data user ke session
                                            $_SESSION['user'] = mysqli_fetch_array($data);        
                                            echo '<script>alert("login berhasil");
                                            location.href="index.php";</script>';    
                                            }
                                            // Jika user tidak ditemukan
                                            else {
                                            echo '<script>alert("login gagal");</script>';
                                        }
                                    }
                                    ?>
                                    <!-- Form Login -->
                                    <form method="POST" class="user">
                                        <!-- Input Username -->
                                        <div class="form-group">
                                            <input type="text"  class="form-control form-control-user"
                                                name="username" placeholder="Username...">
                                        </div>
                                        <!-- Input Password -->
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                name="password" placeholder="Password">
                                        </div>
                                        <!-- Remember Me Checkbox -->
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Remember
                                                    Me</label>
                                            </div>
                                        </div>
                                        <!-- Submit Button -->
                                        <button type="submit" name="login" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                        <hr>
                                        <!-- Link ke Student Login -->
                                        <a href="login_siswa.php" class="btn btn-secondary btn-user btn-block">
                                            Login Sebagai Siswa
                                        </a>
                                        
                                    
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