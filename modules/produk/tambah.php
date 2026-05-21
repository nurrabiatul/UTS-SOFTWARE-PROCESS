<?php
// tambah.php - Disimpan di si-kasir/modules/produk/tambah.php
require_once "../../config/database.php";
require_once "../../includes/auth_helper.php";
restrict_to_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = isset($_POST['nama_produk']) ? trim($_POST['nama_produk']) : '';
    $harga_jual = isset($_POST['harga_jual']) ? trim($_POST['harga_jual']) : '';
    $stok_awal = isset($_POST['stok_awal']) ? trim($_POST['stok_awal']) : '';
    
    // Validasi Kolom Kosong Sesuai Standar Ujian (Sisi Server / PHP Backup)
    if ($nama_produk === '' || $harga_jual === '' || $stok_awal === '') {
        $_SESSION['error'] = "Data produk tidak lengkap, semua kolom wajib diisi!";
    } elseif (!is_numeric($harga_jual) || $harga_jual < 0 || !is_numeric($stok_awal) || $stok_awal < 0) {
        $_SESSION['error'] = "Harga harus berupa angka positif!";
    } else {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO m_produk (nama_produk, harga_jual, stok) VALUES (?, ?, ?)");
            $stmt->execute([$nama_produk, $harga_jual, $stok_awal]);
            $id_produk = $pdo->lastInsertId();
            
            $stmt_log = $pdo->prepare("INSERT INTO t_log_stok (id_produk, Jumlah, Tipe, Keterangan) VALUES (?, ?, 'Masuk', ?)");
            $stmt_log->execute([$id_produk, $stok_awal, "Saldo stok awal barang baru"]);
            
            $pdo->commit();
            
            $_SESSION['success'] = "Produk [$nama_produk] berhasil disimpan!";
            header("Location: index.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal menyimpan produk: " . $e->getMessage();
        }
    }
}

$path_mundur = "../../";
require_once "../../includes/header.php";
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-title">Tambah Barang Baru</div>
    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Nama Barang / Produk</label>
            <input type="text" name="nama_produk" class="form-control" 
                   placeholder="Contoh: Indomie Goreng"
                   required 
                   oninvalid="this.setCustomValidity('Data produk tidak lengkap, semua kolom wajib diisi!')" 
                   oninput="this.setCustomValidity('')">
        </div>
        <div class="form-group">
            <label class="form-label">Harga Jual (Rp)</label>
            <input type="number" step="0.01" name="harga_jual" class="form-control" 
                   placeholder="Contoh: 3000"
                   required 
                   oninvalid="this.setCustomValidity('Data produk tidak lengkap, semua kolom wajib diisi!')" 
                   oninput="this.setCustomValidity('')">
        </div>
        <div class="form-group">
            <label class="form-label">Stok Awal</label>
            <input type="number" name="stok_awal" class="form-control" min="0" 
                   placeholder="Contoh: 100"
                   required 
                   oninvalid="this.setCustomValidity('Data produk tidak lengkap, semua kolom wajib diisi!')" 
                   oninput="this.setCustomValidity('')">
        </div>
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-secondary" style="flex: 1;">Simpan</button>
            <a href="index.php" class="btn btn-outline" style="flex: 1; text-align: center;">Batal</a>
        </div>
    </form>
</div>

<?php require_once "../../includes/footer.php"; ?>