<?php
require_once "../../config/database.php";
require_once "../../includes/auth_helper.php";
restrict_to_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM m_produk WHERE id_produk = ?");
$stmt->execute([$id]);
$produk = $stmt->fetch();

if (!$produk) {
    $_SESSION['error'] = "Produk tidak ditemukan!";
    header("Location: index.php");
    exit();
}

$val_nama = $produk['nama_produk'];
$val_harga = $produk['harga_jual'];
$val_stok = $produk['stok'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = isset($_POST['nama_produk']) ? trim($_POST['nama_produk']) : '';
    $harga_jual = isset($_POST['harga_jual']) ? trim($_POST['harga_jual']) : '';
    $stok_baru = isset($_POST['stok']) ? trim($_POST['stok']) : '';
    $keterangan_input = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : '';
    
    $val_nama = $nama_produk;
    $val_harga = $harga_jual;
    $val_stok = $stok_baru;

    if ($nama_produk === '' || $harga_jual === '' || $stok_baru === '') {
        $_SESSION['error'] = "Data produk tidak lengkap, semua kolom wajib diisi!";
    } elseif (!is_numeric($harga_jual) || $harga_jual < 0 || !is_numeric($stok_baru) || $stok_baru < 0) {
        $_SESSION['error'] = "Harga dan stok harus berupa angka positif!";
    } else {
        try {
            $pdo->beginTransaction();
            $selisih = $stok_baru - $produk['stok'];
            
            $up = $pdo->prepare("UPDATE m_produk SET nama_produk = ?, harga_jual = ?, stok = ? WHERE id_produk = ?");
            $up->execute([$nama_produk, $harga_jual, $stok_baru, $id]);
            
            if ($selisih != 0) {
                $tipe = $selisih > 0 ? 'Masuk' : 'Keluar';
                $jumlah = abs($selisih); 
                
                if (!empty($keterangan_input)) {
                    $alasan = $keterangan_input;
                } else {
                    $alasan = ($selisih > 0) ? "Stok dimasukkan: $jumlah pcs" : "Stok dikurangi: $jumlah pcs";
                }
                
                $log = $pdo->prepare("INSERT INTO t_log_stok (id_produk, Jumlah, Tipe, Keterangan) VALUES (?, ?, ?, ?)");
                $log->execute([$id, $jumlah, $tipe, $alasan]);
            }
            
            $pdo->commit();
            $_SESSION['success'] = "Produk [$nama_produk] diperbarui!";
            header("Location: index.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal memperbarui produk: " . $e->getMessage();
        }
    }
}

$path_mundur = "../../";
require_once "../../includes/header.php";
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-title"> Perbarui Stok</div>
    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Nama Barang</label>
            <input type="text" name="nama_produk" class="form-control" 
                   value="<?php echo htmlspecialchars($val_nama); ?>" 
                   required 
                   oninvalid="this.setCustomValidity('Data produk tidak lengkap, semua kolom wajib diisi!')" 
                   oninput="this.setCustomValidity('')">
        </div>
        <div class="form-group">
            <label class="form-label">Harga Jual (Rp)</label>
            <input type="number" step="0.01" name="harga_jual" class="form-control" 
                   value="<?php echo htmlspecialchars($val_harga); ?>" 
                   required 
                   oninvalid="this.setCustomValidity('Data produk tidak lengkap, semua kolom wajib diisi!')" 
                   oninput="this.setCustomValidity('')">
        </div>
        <div class="form-group" style="background-color: #f1f5f9; padding: 1rem; border-radius: 8px;">
            <label class="form-label">Stok Fisik Saat Ini </label>
            <p style="font-size: 0.8rem; margin-bottom: 0.5rem;">Sisa sistem sebelum perubahan: <strong><?php echo $produk['stok']; ?></strong>.</p>
            <input type="number" name="stok" class="form-control" 
                   value="<?php echo htmlspecialchars($val_stok); ?>" 
                   style="background-color: white;" 
                   required 
                   oninvalid="this.setCustomValidity('Data produk tidak lengkap, semua kolom wajib diisi!')" 
                   oninput="this.setCustomValidity('')">
        </div>
        <div class="form-group">
            <label class="form-label">Keterangan Audit Log</label>
            <input type="text" name="keterangan" class="form-control" placeholder="Tulis alasan jika stok diubah...">
        </div>
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-secondary" style="flex: 1;">Perbarui</button>
            <a href="index.php" class="btn btn-outline" style="flex: 1; text-align: center;">Kembali</a>
        </div>
    </form>
</div>

<?php require_once "../../includes/footer.php"; ?>