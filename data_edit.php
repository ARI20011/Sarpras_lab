<?php
require_once __DIR__ . '/koneksi.php';
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'kepala lab') {
    header('Location: login.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: index.php?page=data');
    exit;
}

$err  = '';
$load = mysqli_query($koneksi, "SELECT * FROM `barang` WHERE `id_barang` = $id LIMIT 1");
$row  = $load ? mysqli_fetch_assoc($load) : null;
if (!$row) {
    header('Location: index.php?page=data');
    exit;
}

$jenis = mysqli_query($koneksi, 'SELECT `id_jenis`, `nama_jenis` FROM `jenis_barang` ORDER BY `nama_jenis` ASC');

if (isset($_POST['simpan'])) {
    $id_jenis   = (int) ($_POST['id_jenis'] ?? 0);
    $nama       = trim((string) ($_POST['nama_barang'] ?? ''));
    $merek      = trim((string) ($_POST['merek'] ?? ''));
    $made_by    = trim((string) ($_POST['made_by'] ?? ''));
    $harga      = (int) preg_replace('/\D/', '', (string) ($_POST['harga'] ?? 0));
    $jumlah     = trim((string) ($_POST['Jumlah'] ?? ''));

    if ($id_jenis < 1 || $nama === '') {
        $err = 'Jenis barang dan nama barang wajib diisi.';
    } else {
        $n = mysqli_real_escape_string($koneksi, $nama);
        $m = mysqli_real_escape_string($koneksi, $merek);
        $d = mysqli_real_escape_string($koneksi, $made_by);
        $j = mysqli_real_escape_string($koneksi, $jumlah);
        $ok = mysqli_query(
            $koneksi,
            "UPDATE `barang` SET `id_jenis`=$id_jenis, `nama_barang`='$n', `merek`='$m', `made_by`='$d', `harga`=$harga, `Jumlah`='$j' WHERE `id_barang`=$id"
        );
        if ($ok) {
            header('Location: index.php?page=data&ok=1');
            exit;
        }
        $err = 'Gagal menyimpan: ' . mysqli_error($koneksi);
        $row = array_merge($row, [
            'id_jenis'   => $id_jenis,
            'nama_barang' => $nama,
            'merek'     => $merek,
            'made_by'   => $made_by,
            'harga'     => $harga,
            'Jumlah'    => $jumlah,
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Ubah Data Barang — Sarpras Lab</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <a href="index.php?page=data" class="btn btn-link mb-3"><i class="fas fa-arrow-left"></i> Kembali ke Data Barang</a>

        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ubah Data Barang</h6>
            </div>
            <div class="card-body">
                <?php if ($err) : ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <form method="post" class="user">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" required
                                placeholder="Masukkan nama barang" value="<?php echo htmlspecialchars($row['nama_barang'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="id_jenis">Jenis</label>
                            <select class="form-control" id="id_jenis" name="id_jenis" required>
                                <option value="">Pilih Jenis</option>
                                <?php
                                $cur = (int) ($row['id_jenis'] ?? 0);
                                if ($jenis) {
                                    mysqli_data_seek($jenis, 0);
                                    while ($j = mysqli_fetch_assoc($jenis)) {
                                        $s = (int) $j['id_jenis'] === $cur ? ' selected' : '';
                                        echo '<option value="' . (int) $j['id_jenis'] . '"' . $s . '>' . htmlspecialchars($j['nama_jenis'], ENT_QUOTES, 'UTF-8') . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="merek">Merek / Model</label>
                            <input type="text" class="form-control" id="merek" name="merek" placeholder="Merek / model"
                                value="<?php echo htmlspecialchars($row['merek'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="made_by">Made by (Produsen)</label>
                            <input type="text" class="form-control" id="made_by" name="made_by" placeholder="Nama pabrik / pencipta"
                                value="<?php echo htmlspecialchars($row['made_by'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="harga">Harga (Rp)</label>
                            <input type="text" class="form-control" id="harga" name="harga" inputmode="numeric"
                                placeholder="0" value="<?php echo number_format((int) ($row['harga'] ?? 0), 0, ',', '.'); ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="Jumlah">Jumlah (stok)</label>
                            <input type="text" class="form-control" id="Jumlah" name="Jumlah" placeholder="Contoh: 30 Unit"
                                value="<?php echo htmlspecialchars($row['Jumlah'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" name="simpan" class="btn btn-primary">Simpan Data</button>
                        <a href="index.php?page=data" class="btn btn-secondary">Batal</a>
                        <a href="data_detail.php?id=<?php echo $id; ?>" class="btn btn-info text-white">Lihat detail</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
