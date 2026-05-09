<?php

require_once __DIR__ . '/koneksi.php';

// Cek session siswa
if (empty($_SESSION['siswa'])) {
    echo '<script>alert("Silahkan login terlebih dahulu"); location.href="login_siswa.php";</script>';
    exit;
}

$nis = $_SESSION['siswa']['nis'] ?? '';
if ($nis === '') {
    echo '<script>alert("NIS tidak ditemukan di session"); location.href="login_siswa.php";</script>';
    exit;
}

// Cek apakah siswa sudah terdaftar di database
$nis_safe = mysqli_real_escape_string($koneksi, $nis);
$query = mysqli_query($koneksi, "SELECT * FROM `siswa` WHERE `nis` = '$nis_safe' LIMIT 1");
if ($query && mysqli_num_rows($query) > 0) {
    $siswa_data = mysqli_fetch_assoc($query);
    $_SESSION['siswa'] = $siswa_data;
    echo '<script>location.href="dashboard_siswa.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Form Data Siswa - Lab Komputer">
    <meta name="author" content="">

    <title>Form Data Siswa - Lab Komputer</title>

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
                                        <h1 class="h4 text-gray-900 mb-4">Lengkapi Data Siswa</h1>
                                    </div>

                                    <form method="POST" action="save_siswa.php" class="user">
                                        <div class="form-group">
                                            <label><strong>NIS</strong></label>
                                            <input type="text" class="form-control form-control-user" value="<?php echo htmlspecialchars($nis); ?>" readonly>
                                            <input type="hidden" name="nis" value="<?php echo htmlspecialchars($nis); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Nama Lengkap</strong> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-user" placeholder="Masukkan nama lengkap..." name="nama" required>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Kelas</strong> <span class="text-danger">*</span></label>
                                            <select class="form-control" name="kelas" required>
                                                <option value="">-- Pilih Kelas --</option>
                                                <optgroup label="Kelas X">
                                                    <option value="X PPLG 1">X PPLG 1</option>
                                                    <option value="X PPLG 2">X PPLG 2</option>
                                                    <option value="X PPLG 3">X PPLG 3</option>
                                                    <option value="X DKV 1">X DKV 1</option>
                                                    <option value="X DKV 2">X DKV 2</option>
                                                </optgroup>
                                                <optgroup label="Kelas XI">
                                                    <option value="XI PPLG 1">XI PPLG 1</option>
                                                    <option value="XI PPLG 2">XI PPLG 2</option>
                                                    <option value="XI PPLG 3">XI PPLG 3</option>
                                                    <option value="XI DKV 1">XI DKV 1</option>
                                                    <option value="XI DKV 2">XI DKV 2</option>
                                                </optgroup>
                                                <optgroup label="Kelas XII">
                                                    <option value="XII PPLG 1">XII PPLG 1</option>
                                                    <option value="XII PPLG 2">XII PPLG 2</option>
                                                    <option value="XII PPLG 3">XII PPLG 3</option>
                                                    <option value="XII DKV 1">XII DKV 1</option>
                                                    <option value="XII DKV 2">XII DKV 2</option>
                                                </optgroup>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Email</strong> <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control form-control-user" placeholder="Email..." name="email" required>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>No. Telepon</strong> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-user" placeholder="No. Telepon..." name="telepon" required>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Simpan Data
                                        </button>

                                        <hr>
                                        <a href="logout_siswa.php" class="btn btn-danger btn-user btn-block">
                                            Batal
                                        </a>
                                    </form>

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
