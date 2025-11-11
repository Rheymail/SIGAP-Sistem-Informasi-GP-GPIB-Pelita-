<?php
require_once 'config.php';

// Pastikan user sudah login sebelum logout
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect ke login dengan message
header("Location: login.php?message=logout_success");
exit();
?>