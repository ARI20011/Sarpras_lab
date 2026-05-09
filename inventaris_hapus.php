<?php
/**
 * ===========================
 * INVENTARIS_HAPUS.PHP - Inventory Edit/Delete Handler
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menangani update status inventaris
 * 2. Menangani penghapusan record inventaris
 * 3. Redirect kembali ke index.php?page=inventaris setelah operasi selesai
 * 
 * Akses:
 * - Memerlukan session login yang valid
 * - Update/delete hanya bisa dilakukan dengan form POST yang valid
 */

// Koneksi database
if (!isset($koneksi)) {
    require_once __DIR__ . '/koneksi.php';
}

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Fungsi untuk mengambil status opsi dari database (enum values)
function getInventarisStatusOptions($koneksi)
{
    // Default status yang akan digunakan jika database error
    $default = ['Sedang di pakai', 'Belum di pakai', 'Selesai dipakai'];
    
    // Query untuk mengecek tipe kolom status
    $result = mysqli_query($koneksi, "SHOW COLUMNS FROM `inventaris` LIKE 'status'");
    if (!$result) {
        return $default;
    }

    // Ambil info kolom
    $row = mysqli_fetch_assoc($result);
    $type = (string) ($row['Type'] ?? '');
    
    // Parse enum values dari tipe kolom
    if (!preg_match("/^enum\((.*)\)$/i", $type, $matches)) {
        return $default;
    }

    // Extract nilai-nilai enum
    $raw = str_getcsv($matches[1], ',', "'");
    $options = array_values(array_filter(array_map('trim', $raw), static function ($v) {
        return $v !== '';
    }));

    return !empty($options) ? $options : $default;
}

// Ambil daftar status yang valid
$statusOptions = getInventarisStatusOptions($koneksi);

// Proses update status inventaris
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $idUpdate = (int) ($_POST['id_inventaris'] ?? 0);
    $statusBaru = trim((string) ($_POST['status'] ?? ''));

    // Validasi: ID harus valid dan status harus ada dalam list yang valid
    if ($idUpdate > 0 && in_array($statusBaru, $statusOptions, true)) {
        $statusEscaped = mysqli_real_escape_string($koneksi, $statusBaru);
        // Query UPDATE untuk mengubah status
        mysqli_query(
            $koneksi,
            "UPDATE `inventaris` SET `status` = '$statusEscaped' WHERE `id_inventaris` = $idUpdate LIMIT 1"
        );
    }

    header('Location: index.php?page=inventaris');
    exit;
}

// Proses hapus inventaris
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $idHapus = (int) ($_POST['delete_id'] ?? 0);
    if ($idHapus > 0) {
        // Query DELETE untuk menghapus inventaris
        mysqli_query($koneksi, "DELETE FROM `inventaris` WHERE `id_inventaris` = $idHapus LIMIT 1");
    }

    header('Location: index.php?page=inventaris');
    exit;
}

// Jika akses langsung tanpa operasi, redirect ke inventaris
header('Location: index.php?page=inventaris');
exit;
?>
