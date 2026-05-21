<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi login user.
function check_login() {
    if (!isset($_SESSION['id_user'])) {
        if (file_exists('includes/auth/login.php')) {
            header("Location: includes/auth/login.php");
        } elseif (file_exists('../includes/auth/login.php')) {
            header("Location: ../includes/auth/login.php");
        } else {
            header("Location: ../../includes/auth/login.php");
        }
        exit();
    }
}

// Validasi hak akses khusus Admin.
function restrict_to_admin() {
    check_login();
    if ($_SESSION['role'] !== 'Admin') {
        $_SESSION['error'] = "Akses ditolak! Menu ini hanya untuk Admin.";
        if (file_exists('modules/transaksi/index.php')) {
            header("Location: modules/transaksi/index.php");
        } else {
            header("Location: ../transaksi/index.php");
        }
        exit();
    }
}

// Sanitasi data input untuk mencegah serangan XSS
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>