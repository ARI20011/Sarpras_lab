<?php
/**
 * ===========================
 * LOGOUT.PHP - Admin/Staff Logout
 * ===========================
 * 
 * File ini berfungsi untuk:
 * 1. Menghancurkan session user (admin/staff)
 * 2. Mengarahkan kembali ke halaman login
 * 
 * Proses:
 * - Session dihancurkan dengan session_destroy()
 * - User diarahkan ke login.php untuk login ulang
 */

// Mulai session (jika belum ada)
session_start();

// Hancurkan semua data session
session_destroy();

// Arahkan ke halaman login
header("Location: login.php");
?>