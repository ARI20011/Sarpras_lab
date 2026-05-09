<?php
/**
 * ===========================
 * DATA_HAPUS.PHP - Delete Item/Equipment
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menghapus data barang dari database berdasarkan ID
 * 2. Memverifikasi hak akses (hanya untuk kepala lab)
 * 3. Mengarahkan kembali ke halaman data setelah penghapusan
 * 
 * Persyaratan:
 * - User harus sudah login (session ada)
 * - User harus memiliki role 'kepala lab'
 * - ID barang harus valid dan ada di URL parameter (?id=X)
 */

// Koneksi database
require_once __DIR__ . '/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Cek apakah user memiliki role 'kepala lab' untuk menghapus data
if (($_SESSION['user']['role'] ?? '') !== 'kepala lab') {
    header('Location: index.php');
    exit;
}

// Ambil ID barang dari URL parameter
$id = (int) ($_GET['id'] ?? 0);

// Jika ID valid (lebih dari 0), lakukan penghapusan
if ($id > 0) {
    // Query DELETE untuk menghapus barang dengan ID tertentu
    mysqli_query($koneksi, "DELETE FROM `barang` WHERE `id_barang` = $id");
}

// Arahkan kembali ke halaman data barang
header('Location: index.php?page=data');
exit;
