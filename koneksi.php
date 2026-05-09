<?php
/**
 * ===========================
 * KONEKSI.PHP - Database Connection File
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menginisialisasi session PHP
 * 2. Melakukan koneksi ke database MySQL
 * 3. Menangani error koneksi database
 * 
 * Database: sarpras_lab
 * User: root
 * Password: (kosong)
 * Host: localhost
 */

// Mulai atau lanjutkan session
session_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database MySQL
// Parameter: host, username, password, database_name
$koneksi = mysqli_connect("localhost", "root", "", "sarpras_lab");

// Cek apakah koneksi berhasil
// Jika gagal, tampilkan pesan error
if (mysqli_connect_errno()) {
    echo "Koneksi database gagal : " . mysqli_connect_error();
}
?>