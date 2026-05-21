<?php
require_once "auth_helper.php";
check_login();

$base_path = isset($path_mundur) ? $path_mundur : "../../";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-KASIR Maju Jaya</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-brand">
        SI-<span>KASIR</span>
    </div>
    
    <ul class="nav-menu">
        <li><a href="<?php echo $base_path; ?>modules/transaksi/index.php" class="nav-link">Kasir Utama</a></li>
        <li><a href="<?php echo $base_path; ?>modules/produk/index.php" class="nav-link">Gudang & Produk</a></li>
        
        <?php if ($_SESSION['role'] === 'Admin') : ?>
            <li><a href="<?php echo $base_path; ?>modules/laporan/index.php" class="nav-link">Dashboard Admin</a></li>
            <li><a href="<?php echo $base_path; ?>modules/produk/user.php" class="nav-link">Data User</a></li>
        <?php endif; ?>
        
        <li>
            <div class="user-badge">
                <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $_SESSION['role']; ?>)
            </div>
        </li>
        <li><a href="<?php echo $base_path; ?>includes/auth/logout.php" class="logout-btn">Keluar</a></li>
    </ul>
</nav>

<div class="main-content">
    
    <!-- Penampil Notifikasi Status Sesi -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            ⚠️ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            ✅ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>