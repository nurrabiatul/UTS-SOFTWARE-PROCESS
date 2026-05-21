<?php
require_once "../../config/database.php";

$path_mundur = "../../";
require_once "../../includes/header.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$stok_kritis = isset($_GET['stok_kritis']) ? true : false;

$sql = "SELECT * FROM m_produk WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND nama_produk LIKE ?";
    $params[] = "%" . $search . "%";
}

if ($stok_kritis) {
    $sql .= " AND stok < 5";
}

$sql .= " ORDER BY nama_produk ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produk_list = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-title">
        <span>Gudang Inventory Swalayan "Maju Jaya"</span>
        <div>
            <?php if ($_SESSION['role'] === 'Admin') : ?>
                <a href="tambah.php" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">+ Tambah Barang</a>
            <?php endif; ?>
            <a href="log_stok.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Audit Mutasi Stok</a>
        </div>
    </div>

    <!-- Filter Pencarian Gudang -->
    <form action="" method="GET" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: flex-end;">
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 250px;">
            <label class="form-label">Cari Nama Barang</label>
            <input type="text" name="search" class="form-control" placeholder="Masukkan nama produk" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 8px;">
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                <input type="checkbox" name="stok_kritis" value="1" <?php echo $stok_kritis ? 'checked' : ''; ?> style="width: 18px; height: 18px;">
                Stok Kritis (< 5)
            </label>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">Cari & Filter</button>
            <a href="index.php" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <!-- Tabel Daftar Barang Gudang -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Barang / Produk</th>
                    <th>Harga Jual</th>
                    <th>Stok Tersedia</th>
                    <th>Status</th>
                    <?php if ($_SESSION['role'] === 'Admin') : ?>
                        <th style="text-align: center; width: 180px;">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($produk_list) > 0) : ?>
                    <?php foreach ($produk_list as $row) : ?>
                        <tr>
                            <td><?php echo $row['id_produk']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['nama_produk']); ?></strong></td>
                            <td>Rp <?php echo number_format($row['harga_jual'], 2, ',', '.'); ?></td>
                            <td><strong><?php echo $row['stok']; ?> pcs</strong></td>
                            <td>
                                <?php if ($row['stok'] <= 0) : ?>
                                    <span class="badge badge-danger">Habis</span>
                                <?php elseif ($row['stok'] < 5) : ?>
                                    <span class="badge badge-warning">Kritis</span>
                                <?php else : ?>
                                    <span class="badge badge-success">Tersedia</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($_SESSION['role'] === 'Admin') : ?>
                                <td style="text-align: center;">
                                    <a href="edit.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; color: #1e293b;">Edit</a>
                                    <a href="hapus.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; color: #dc2626;" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">Hapus</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?php echo $_SESSION['role'] === 'Admin' ? '6' : '5'; ?>" style="text-align: center; color: #64748b; padding: 2rem;">Tidak ada barang yang tersedia di database gudang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>