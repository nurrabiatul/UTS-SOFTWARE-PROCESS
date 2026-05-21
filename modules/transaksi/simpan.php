<?php
require_once "../../config/database.php";
require_once "../../includes/auth_helper.php";
check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_nota = sanitize($_POST['nomor_nota']);
    $uang_bayar = (float)$_POST['uang_bayar'];
    $total_tagihan = (float)$_POST['total_tagihan'];
    $id_user = (int)$_SESSION['id_user'];
    
    if (!isset($_POST['transaction_token']) || !isset($_SESSION['transaction_token']) || $_POST['transaction_token'] !== $_SESSION['transaction_token']) {
        $_SESSION['error'] = "Transaksi sudah diproses sebelumnya (Duplicate Nota).";
        header("Location: index.php");
        exit();
    }
    
    unset($_SESSION['transaction_token']);
    
    if (empty($_SESSION['cart'])) {
        $_SESSION['error'] = "Keranjang belanja kosong!";
        header("Location: index.php");
        exit();
    }
    
    if ($uang_bayar < $total_tagihan) {
        $_SESSION['error'] = "Uang bayar kurang dari total tagihan.";
        header("Location: index.php");
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        $tgl_transaksi = date('Y-m-d H:i:s');
        $ins_header = $pdo->prepare("INSERT INTO t_penjualan (nomor_nota, tgl_transaksi, total_bayar, id_user) VALUES (?, ?, ?, ?)");
        $ins_header->execute([$nomor_nota, $tgl_transaksi, $total_tagihan, $id_user]);
        $id_penjualan = $pdo->lastInsertId();
        
        foreach ($_SESSION['cart'] as $id_produk => $item) {
            $qty_beli = $item['qty'];
            
            $p_stmt = $pdo->prepare("SELECT stok, nama_produk FROM m_produk WHERE id_produk = ? FOR UPDATE");
            $p_stmt->execute([$id_produk]);
            $prod = $p_stmt->fetch();
            
            if (!$prod || $prod['stok'] < $qty_beli) {
                throw new Exception("Stok [" . ($prod['nama_produk'] ?? 'Barang') . "] tidak mencukupi untuk transaksi ini.");
            }
            
            $ins_detail = $pdo->prepare("INSERT INTO t_penjualan_detail (id_penjualan, id_produk, Qty, Subtotal) VALUES (?, ?, ?, ?)");
            $ins_detail->execute([$id_penjualan, $id_produk, $qty_beli, $item['subtotal']]);
            
            $up_stock = $pdo->prepare("UPDATE m_produk SET stok = stok - ? WHERE id_produk = ?");
            $up_stock->execute([$qty_beli, $id_produk]);
            
            $ins_log = $pdo->prepare("INSERT INTO t_log_stok (id_produk, Jumlah, Tipe, Keterangan) VALUES (?, ?, 'Keluar', ?)");
            $ins_log->execute([$id_produk, $qty_beli, "Penjualan Nota #" . $nomor_nota]);
        }
        
        $pdo->commit();
        
        $_SESSION['kembalian'] = $uang_bayar - $total_tagihan;
        $_SESSION['uang_bayar'] = $uang_bayar;
        unset($_SESSION['cart']);
        
        header("Location: nota.php?id=" . $id_penjualan);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Transaksi Gagal! " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
}
?>