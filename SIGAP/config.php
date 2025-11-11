<?php
// Men-nonaktifkan tampilan error untuk lingkungan produksi
// Ini mencegah bocornya informasi sensitif melalui pesan error PHP
error_reporting(0);
ini_set('display_errors', 0);

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'member_data');

// Membuat koneksi dengan error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    // Jika koneksi gagal, catat di log server (jangan tampilkan ke user)
    // error_log("Koneksi gagal: " . mysqli_connect_error());
    die("Tidak dapat terhubung ke server. Silakan coba lagi nanti.");
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Start session
session_start();

// Include helper functions
require_once __DIR__ . '/includes/functions.php';

// Generate birthday notifications daily (functions.php checks table existence)
if (!isset($_SESSION['notifications_generated']) || $_SESSION['notifications_generated'] != date('Y-m-d')) {
    generateBirthdayNotifications($conn);
    $_SESSION['notifications_generated'] = date('Y-m-d');
}

// Fungsi untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    return true;
}
?>