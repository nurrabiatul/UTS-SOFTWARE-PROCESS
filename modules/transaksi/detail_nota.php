<?php
require_once "../../config/database.php";

$id_penjualan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_penjualan <= 0) {
    $_SESSION['error'] = "ID transaksi tidak valid!";
    header("Location: ../laporan/index.php");
    exit();
}

$stmt_header = $pdo->prepare("SELECT p.*, u.username FROM t_penjualan p INNER JOIN m_user u ON p.id_user = u.id_user WHERE p.id_penjualan = ?");
$stmt_header->execute([$id_penjualan]);
$nota = $stmt_header->fetch();

if (!$nota) {
    $_SESSION['error'] = "Nota tidak ditemukan!";
    header("Location: ../laporan/index.php");
    exit();
}

$stmt_detail = $pdo->prepare("SELECT d.*, p.nama_produk, p.harga_jual FROM t_penjualan_detail d INNER JOIN m_produk p ON d.id_produk = p.id_produk WHERE d.id_penjualan = ?");
$stmt_detail->execute([$id_penjualan]);
$items = $stmt_detail->fetchAll();

$path_mundur = "../../";
require_once "../../includes/header.php";
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-title">
        <span>Rincian Lengkap Nota #<?php echo htmlspecialchars($nota['nomor_nota']); ?></span>
        <div>
            <a href="../laporan/index.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Kembali</a>
            <a href="nota.php?id=<?php echo $id_penjualan; ?>" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;"> Cetak Struk</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; background-color: #f1f5f9; padding: 1.25rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #cbd5e1;">
        <div>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Waktu Transaksi</span>
            <p style="font-weight: 600; margin-top: 0.25rem;"><?php echo date('d-M-Y H:i:s', strtotime($nota['tgl_transaksi'])); ?></p>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Kasir Melayani</span>
            <p style="font-weight: 600; margin-top: 0.25rem;"> <?php echo htmlspecialchars($nota['username']); ?></p>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Total Transaksi</span>
            <p style="font-weight: 800; color: #1e293b; margin-top: 0.25rem;">Rp <?php echo number_format($nota['total_bayar'], 2, ',', '.'); ?></p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">No.</th>
                    <th>Nama Produk / Barang</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: center; width: 120px;">Qty Belanja</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($items as $item) : 
                    $qty = isset($item['Qty']) ? $item['Qty'] : (isset($item['qty']) ? $item['qty'] : 0);
                    $subtotal = isset($item['Subtotal']) ? $item['Subtotal'] : (isset($item['subtotal']) ? $item['subtotal'] : 0);
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong></td>
                        <td style="text-align: right;">Rp <?php echo number_format($item['harga_jual'], 2, ',', '.'); ?></td>
                        <td style="text-align: center;"><strong><?php echo $qty; ?> unit</strong></td>
                        <td style="text-align: right; font-weight: 700; color: #1e293b;">Rp <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <tr style="background-color: #f1f5f9; font-weight: 800;">
                    <td colspan="3" style="text-align: right; text-transform: uppercase; font-size: 0.9rem;">Total Belanja :</td>
                    <td colspan="2" style="text-align: right; font-size: 1.1rem; color: #1e293b;">Rp <?php echo number_format($nota['total_bayar'], 2, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>