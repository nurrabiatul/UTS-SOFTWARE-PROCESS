<?php
require_once "../../config/database.php";
require_once "../../includes/auth_helper.php";

restrict_to_admin();

if (isset($_GET['filter_cepat']) && $_GET['filter_cepat'] === 'hari_ini') {
    $tgl_mulai = date('Y-m-d');
    $tgl_selesai = date('Y-m-d');
} else {
    $tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
    $tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');
}

// 1. QUERY REKAPITULASI NOTA INDIVIDUAL (INNER JOIN)
$query_pjn = "SELECT p.*, u.username 
              FROM t_penjualan p 
              INNER JOIN m_user u ON p.id_user = u.id_user 
              WHERE DATE(p.tgl_transaksi) BETWEEN ? AND ? 
              ORDER BY p.tgl_transaksi DESC";
$stmt_pjn = $pdo->prepare($query_pjn);
$stmt_pjn->execute([$tgl_mulai, $tgl_selesai]);
$laporan_penjualan = $stmt_pjn->fetchAll();

// Menghitung Ringkasan Metrik Keuangan untuk Dashboard Laporan
$total_pendapatan = 0;
$total_nota = count($laporan_penjualan);
foreach ($laporan_penjualan as $row) {
    $total_pendapatan += $row['total_bayar'];
}

// 2. QUERY PRODUK TERLARIS / BEST SELLER (INNER JOIN & AGREGASI)
$query_best = "SELECT prod.nama_produk, SUM(detail.Qty) AS total_qty, SUM(detail.Subtotal) AS total_omset
               FROM t_penjualan_detail detail
               INNER JOIN m_produk prod ON detail.id_produk = prod.id_produk
               INNER JOIN t_penjualan p ON detail.id_penjualan = p.id_penjualan
               WHERE DATE(p.tgl_transaksi) BETWEEN ? AND ?
               GROUP BY detail.id_produk
               ORDER BY total_qty DESC 
               LIMIT 5";
$stmt_best = $pdo->prepare($query_best);
$stmt_best->execute([$tgl_mulai, $tgl_selesai]);
$best_sellers = $stmt_best->fetchAll();

// 3. QUERY REKAPITULASI NOTA PER TANGGAL (GROUP BY DATE & SUM)
$query_rekap_tgl = "SELECT DATE(tgl_transaksi) AS tgl, COUNT(id_penjualan) AS jml_nota, SUM(total_bayar) AS total_omset
                    FROM t_penjualan
                    WHERE DATE(tgl_transaksi) BETWEEN ? AND ?
                    GROUP BY DATE(tgl_transaksi)
                    ORDER BY tgl DESC";
$stmt_rekap_tgl = $pdo->prepare($query_rekap_tgl);
$stmt_rekap_tgl->execute([$tgl_mulai, $tgl_selesai]);
$rekap_per_tanggal = $stmt_rekap_tgl->fetchAll();

$path_mundur = "../../";
require_once "../../includes/header.php";
?>

<!-- Tombol Print Laporan -->
<div class="btn-print-container" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h3 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px;">Laporan & Analisis Kasir</h3>
    <button onclick="window.print();" class="btn btn-primary">Cetak Laporan</button>
</div>

<!-- Form Filter Rentang Tanggal -->
<div class="card btn-print-container" style="margin-bottom: 1.5rem;">
    <div class="card-title" style="font-size: 0.9rem;">Filter Rentang Tanggal Laporan</div>
    <form action="" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="tgl_mulai" class="form-control" value="<?php echo htmlspecialchars($tgl_mulai); ?>" required>
        </div>
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="tgl_selesai" class="form-control" value="<?php echo htmlspecialchars($tgl_selesai); ?>" required>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button type="submit" class="btn btn-secondary">Filter Data</button>
            <!-- Tombol Filter Cepat -->
            <a href="index.php?filter_cepat=hari_ini" class="btn btn-primary" style="background-color: #0d9488;">Hari Ini</a>
            <a href="index.php" class="btn btn-outline">Reset</a>
        </div>
    </form>
</div>

<!-- Kotak Metrik Ringkasan Keuangan -->
<div class="metrics-grid" style="margin-bottom: 1.5rem;">
    <div class="metric-card" style="border-left: 4px solid #0d9488;">
        <span class="metric-title">Total Omset Pendapatan</span>
        <span class="metric-value" style="color: #0f172a;">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></span>
    </div>
    <div class="metric-card" style="border-left: 4px solid #1e293b;">
        <span class="metric-title">Jumlah Nota Terbit</span>
        <span class="metric-value"><?php echo $total_nota; ?> Transaksi</span>
    </div>
    <div class="metric-card" style="border-left: 4px solid #d97706;">
        <span class="metric-title">Rata-rata per Nota</span>
        <span class="metric-value" style="font-size: 1.35rem;">
            Rp <?php echo $total_nota > 0 ? number_format($total_pendapatan / $total_nota, 0, ',', '.') : 0; ?>
        </span>
    </div>
</div>

<div class="kasir-container" style="grid-template-columns: 1.1fr 0.9fr; gap: 1.5rem; align-items: start;">
    
    <!-- Tabel Rekapitulasi per Tanggal & Tabel Transaksi Harian -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Tabel Rekapitulasi Omset per Tanggal -->
        <div class="card">
            <div class="card-title">Rekapitulasi Nota per Tanggal</div>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: -0.75rem; margin-bottom: 1rem;">
                Ringkasan akumulasi transaksi harian dan total omset per tanggal.
            </p>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal Hari</th>
                            <th style="text-align: center;">Jumlah Nota</th>
                            <th style="text-align: right;">Total Omset Harian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rekap_per_tanggal)) : ?>
                            <?php foreach ($rekap_per_tanggal as $rekap) : ?>
                                <tr>
                                    <td><strong><?php echo date('d-M-Y', strtotime($rekap['tgl'])); ?></strong></td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-warning" style="font-size: 0.8rem; padding: 0.25rem 0.6rem;"><?php echo $rekap['jml_nota']; ?> Nota</span>
                                    </td>
                                    <td style="text-align: right; font-weight: 800; color: #0f172a;">
                                        Rp <?php echo number_format($rekap['total_omset'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #64748b; padding: 2rem;">Belum ada ringkasan omset tanggal.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Detail Nota Penjualan Individual -->
        <div class="card-nota">
            <div class="card-title">Riwayat Rincian Nota Penjualan</div>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: -0.75rem; margin-bottom: 1rem;">
                Daftar lengkap nota belanja aktif dari tanggal <strong><?php echo date('d-M-Y', strtotime($tgl_mulai)); ?></strong> s/d <strong><?php echo date('d-M-Y', strtotime($tgl_selesai)); ?></strong>.
            </p>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Nota</th>
                            <th>Tanggal & Waktu</th>
                            <th>Kasir</th>
                            <th style="text-align: right;">Total Belanja</th>
                            <th style="text-align: center;" class="btn-print-container">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($laporan_penjualan)) : ?>
                            <?php foreach ($laporan_penjualan as $row) : ?>
                                <tr>
                                    <td><strong style="color: #1e293b;"><?php echo htmlspecialchars($row['nomor_nota']); ?></strong></td>
                                    <td><?php echo date('d-M-Y H:i', strtotime($row['tgl_transaksi'])); ?></td>
                                    <td><span class="badge badge-success"><?php echo htmlspecialchars($row['username']); ?></span></td>
                                    <td style="text-align: right; font-weight: 700;">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                    <td style="text-align: center;" class="btn-print-container">
                                        <a href="../transaksi/nota.php?id=<?php echo $row['id_penjualan']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Lihat Nota</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b; padding: 2.5rem;">Tidak ada transaksi penjualan pada rentang tanggal terpilih.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Produk Terlaris (Best Seller) -->
    <div class="card best-seller-card">
        <div class="card-title">Produk Best Seller (Terlaris)</div>
        <p style="font-size: 0.8rem; color: #64748b; margin-top: -0.75rem; margin-bottom: 1rem;">
            Peringkat barang berdasarkan akumulasi unit barang terbanyak yang berhasil terjual.
        </p>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Total Omset</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($best_sellers)) : ?>
                        <?php foreach ($best_sellers as $row) : ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['nama_produk']); ?></strong></td>
                                <td style="text-align: center;">
                                    <span style="background-color: #f1f5f9; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 700; color: #1e293b; font-size: 0.8rem;">
                                        <?php echo $row['total_qty']; ?> pcs
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a;">Rp <?php echo number_format($row['total_omset'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #64748b; padding: 2.5rem;">Belum ada data penjualan barang.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once "../../includes/footer.php"; ?>