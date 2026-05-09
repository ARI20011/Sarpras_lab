<?php

require_once __DIR__ . '/koneksi.php';

// Cek session
if (empty($_SESSION['siswa'])) {
    echo '<script>alert("Sesi tidak valid"); location.href="login_siswa.php";</script>';
    exit;
}

// Proses penyimpanan data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');

    if ($nis === '' || $nama === '' || $kelas === '' || $email === '' || $telepon === '') {
        echo '<script>alert("Semua field wajib diisi"); location.href="form_data_siswa.php";</script>';
        exit;
    }

    $nis_safe = mysqli_real_escape_string($koneksi, $nis);
    $nama_safe = mysqli_real_escape_string($koneksi, $nama);
    $kelas_safe = mysqli_real_escape_string($koneksi, $kelas);
    $email_safe = mysqli_real_escape_string($koneksi, $email);
    $telepon_safe = mysqli_real_escape_string($koneksi, $telepon);

    $query = mysqli_query($koneksi, "SELECT * FROM `siswa` WHERE `nis` = '$nis_safe' LIMIT 1");
    if ($query && mysqli_num_rows($query) > 0) {
        $sql = "UPDATE `siswa` SET `nama_lengkap` = '$nama_safe', `kelas` = '$kelas_safe', `email` = '$email_safe', `no_telepon` = '$telepon_safe',
                `status_akun` = IFNULL(`status_akun`, 'Aktif'), `status_peminjaman` = IFNULL(`status_peminjaman`, 'Tidak Ada Hutang')
                WHERE `nis` = '$nis_safe'";
    } else {
        $sql = "INSERT INTO `siswa` (`nis`, `nama_lengkap`, `kelas`, `email`, `no_telepon`, `status_akun`, `status_peminjaman`)
                VALUES ('$nis_safe', '$nama_safe', '$kelas_safe', '$email_safe', '$telepon_safe', 'Aktif', 'Tidak Ada Hutang')";
    }

    if (mysqli_query($koneksi, $sql)) {
        $siswa_data = [
            'nis' => $nis,
            'nama_lengkap' => $nama,
            'kelas' => $kelas,
            'email' => $email,
            'no_telepon' => $telepon,
        ];
        $_SESSION['siswa'] = $siswa_data;
        echo '<script>alert("Data berhasil disimpan!"); location.href="dashboard_siswa.php";</script>';
    } else {
        echo '<script>alert("Gagal menyimpan data: ' . htmlspecialchars(mysqli_error($koneksi)) . '"); location.href="form_data_siswa.php";</script>';
    }
} else {
    echo '<script>location.href="login_siswa.php";</script>';
}
?>
