<?php
session_start();

// Cek status login pengguna dan alihkan secara relatif biasa tanpa menyertakan database
if (!isset($_SESSION['id_user'])) {
    header("Location: includes/auth/login.php");
} else {
    header("Location: modules/transaksi/index.php");
}
exit();
?>