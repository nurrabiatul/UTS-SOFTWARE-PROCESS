<?php
require_once "../../config/database.php";

$path_mundur = "../../";
require_once "../../includes/header.php";

$log_stok = $pdo->query("SELECT l.*, p.nama_produk FROM t_log_stok l JOIN m_produk p ON l.id_produk = p.id_produk ORDER BY l.waktu_log DESC")->fetchAll();
?>

<div class="card">
    <div class="card-title">
        <span>Laporan Audit Mutasi Barang (Log Stok)</span>
        <a href="index.php" class="btn btn-outline">Kembali ke Gudang</a>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu Log</th>
                    <th>Nama Barang</th>
                    <th>Arah Mutasi</th>
                    <th>Jumlah</th>
                    <th>Alasan Mutasi (Keterangan)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($log_stok) > 0) : ?>
                    <?php foreach ($log_stok as $row) : ?>
                        <?php 
                        $tipe = isset($row['Tipe']) ? $row['Tipe'] : (isset($row['tipe']) ? $row['tipe'] : 'Keluar');
                        $jumlah = isset($row['Jumlah']) ? $row['Jumlah'] : (isset($row['jumlah']) ? $row['jumlah'] : 0);
                        $keterangan = isset($row['Keterangan']) ? $row['Keterangan'] : (isset($row['keterangan']) ? $row['keterangan'] : '');
                        ?>
                        <tr>
                            <td><?php echo date('d-M-Y H:i:s', strtotime($row['waktu_log'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['nama_produk']); ?></strong></td>
                            <td>
                                <?php if ($tipe === 'Masuk') : ?>
                                    <span class="badge badge-success">Masuk</span>
                                <?php else : ?>
                                    <span class="badge badge-danger">Keluar</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo $jumlah; ?> pcs</strong></td>
                            <td><span style="font-style: italic; color: #475569;"><?php echo htmlspecialchars($keterangan); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b; padding: 2rem;">Belum ada riwayat mutasi stok.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>