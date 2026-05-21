<?php
require_once "../../config/database.php";
require_once "../../includes/auth_helper.php";
restrict_to_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $cek = $pdo->prepare("SELECT COUNT(*) FROM t_penjualan_detail WHERE id_produk = ?");
    $cek->execute([$id]);
    $ada_transaksi = $cek->fetchColumn();
    
    if ($ada_transaksi > 0) {
        $_SESSION['error'] = "Gagal! Barang tidak boleh dihapus jika sudah pernah ada dalam transaksi penjualan!";
    } else {
        $del = $pdo->prepare("DELETE FROM m_produk WHERE id_produk = ?");
        $del->execute([$id]);
        $_SESSION['success'] = "Barang berhasil dihapus secara permanen.";
    }
} else {
    $_SESSION['error'] = "ID tidak valid!";
}

header("Location: index.php");
exit();
?>