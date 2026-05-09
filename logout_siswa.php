<?php
/**
 * ===========================
 * LOGOUT_SISWA.PHP - Student Logout
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menghancurkan session siswa
 * 2. Mengarahkan kembali ke halaman login siswa
 * 
 * Proses:
 * - Session siswa dihancurkan dengan session_destroy()
 * - Siswa diarahkan ke login_siswa.php untuk login ulang
 */

// Mulai session (jika belum ada)
session_start();

// Hancurkan semua data session
session_destroy();

// Arahkan ke halaman login siswa
header('Location: login_siswa.php');
exit;
?>
