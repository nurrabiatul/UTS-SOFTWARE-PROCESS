<?php
require_once "../../config/database.php";

$id_penjualan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, u.username FROM t_penjualan p JOIN m_user u ON p.id_user = u.id_user WHERE p.id_penjualan = ?");
$stmt->execute([$id_penjualan]);
$transaksi = $stmt->fetch();

if (!$transaksi) {
    $_SESSION['error'] = "Nota transaksi tidak ditemukan!";
    header("Location: index.php");
    exit();
}

$stmt_detail = $pdo->prepare("SELECT d.*, prod.nama_produk, prod.harga_jual FROM t_penjualan_detail d JOIN m_produk prod ON d.id_produk = prod.id_produk WHERE d.id_penjualan = ?");
$stmt_detail->execute([$id_penjualan]);
$items = $stmt_detail->fetchAll();

// AMBIL DATA DARI SESSION TANPA UNSET DULU
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// AMBIL DATA PEMBAYARAN DARI SESSION
$uang_bayar = $_SESSION['uang_bayar'] ?? 0;
$kembalian  = $_SESSION['kembalian'] ?? 0;

$path_mundur = "../../";
require_once "../../includes/header.php";
?>

<div class="nota-page-wrapper">
    <div style="max-width: 500px; width: 100%; margin: 0 auto; background-color: white; padding: 3rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border: 1px solid #cbd5e1;" id="struk-cetak">
        
        <div class="nota-header" style="text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 1.75rem; margin-bottom: 1.75rem;">
            <h2 style="font-weight: 800; color: #1e293b; margin-bottom: 0.5rem; letter-spacing: 1px;">MAJU JAYA</h2>
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.35rem;">Swalayan & Minimarket Terintegrasi</p>
            <p style="font-size: 0.75rem; color: #94a3b8;">Kasir : <strong><?php echo htmlspecialchars($transaksi['username']); ?></strong></p>
        </div>
        
        <div class="nota-info" style="font-size: 0.85rem; color: #334155; border-bottom: 2px dashed #cbd5e1; padding-bottom: 1.5rem; margin-bottom: 1.75rem; display: flex; flex-direction: column; gap: 0.60rem;">
            <div style="display: flex; justify-content: space-between;">
                <span>No. Nota:</span>
                <strong style="color: #0f172a;"><?php echo $transaksi['nomor_nota']; ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Waktu:</span>
                <span style="color: #0f172a;"><?php echo date('d-M-Y H:i:s', strtotime($transaksi['tgl_transaksi'])); ?></span>
            </div>
        </div>
        
        <div class="nota-items" style="border-bottom: 2px dashed #e2e8f0; padding-bottom: 1.5rem; margin-bottom: 1.75rem;">
            <p style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 1.25rem; letter-spacing: 0.5px;">Item Belanjaan</p>
            
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($items as $item) : 
                    $qty = isset($item['Qty']) ? $item['Qty'] : $item['qty'];
                    $subtotal = isset($item['Subtotal']) ? $item['Subtotal'] : $item['subtotal'];
                ?>
                    <div class="nota-item-row" style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <div>
                            <span style="font-weight: 600; color: #0f172a;"><?php echo htmlspecialchars($item['nama_produk']); ?></span><br>
                            <span style="font-size: 0.8rem; color: #64748b;"><?php echo $qty; ?> pcs x Rp <?php echo number_format($item['harga_jual'], 0, ',', '.'); ?></span>
                        </div>
                        <span style="font-weight: 700; align-self: center; color: #0f172a;">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="nota-totals" style="font-size: 0.95rem; display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; font-weight: 700;">
                <span>TOTAL TAGIHAN:</span>
                <span>Rp <?php echo number_format($transaksi['total_bayar'], 0, ',', '.'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; color: #334155;">
                <span>TUNAI / BAYAR:</span>
                <span>Rp <?php echo number_format($uang_bayar, 0, ',', '.'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: 700; color: #0d9488;">
                <span>UANG KEMBALIAN:</span>
                <span>Rp <?php echo number_format($kembalian, 0, ',', '.'); ?></span>
            </div>
        </div>
        
        <div class="nota-footer" style="text-align: center; font-size: 0.8rem; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 1.75rem;">
            <p>Terima kasih telah berbelanja di Swalayan Maju Jaya!</p>
        </div>
    </div>

    <div class="btn-print-container" style="display: flex; justify-content: center; gap: 1rem; margin-top: 2rem; width: 100%; max-width: 500px;">
        <button onclick="window.print();" class="btn btn-secondary" style="flex: 1;">Cetak Struk Belanja</button>
        <a href="index.php" class="btn btn-primary" style="flex: 1;">Transaksi Baru</a>
    </div>
</div>

<?php 
unset($_SESSION['uang_bayar']);
unset($_SESSION['kembalian']);
require_once "../../includes/footer.php"; 
?>