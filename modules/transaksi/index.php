<?php
require_once "../../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 1. PROSES POST: TAMBAH BARANG KE KERANJANG 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_keranjang'])) {
    $id_produk = (int)$_POST['id_produk'];
    $qty = (int)$_POST['qty'];
    
    if ($id_produk > 0 && $qty > 0) {
        $p_stmt = $pdo->prepare("SELECT * FROM m_produk WHERE id_produk = ?");
        $p_stmt->execute([$id_produk]);
        $prod = $p_stmt->fetch();
        
        if ($prod) {
            $current_qty = isset($_SESSION['cart'][$id_produk]) ? $_SESSION['cart'][$id_produk]['qty'] : 0;
            $total_qty = $current_qty + $qty;
            
            if ($prod['stok'] < $total_qty) {
                $_SESSION['error'] = "Stok [" . $prod['nama_produk'] . "] tidak mencukupi untuk transaksi ini. Sisa stok fisik: " . $prod['stok'] . " unit.";
            } else {
                $_SESSION['cart'][$id_produk] = [
                    'id_produk' => $prod['id_produk'],
                    'nama_produk' => $prod['nama_produk'],
                    'harga_jual' => $prod['harga_jual'],
                    'qty' => $total_qty,
                    'subtotal' => $total_qty * $prod['harga_jual']
                ];
                $_SESSION['success'] = "Barang berhasil masuk ke keranjang belanja!";
            }
        }
    } else {
        $_SESSION['error'] = "Silakan pilih produk terlebih dahulu sebelum menambahkan!";
    }
    header("Location: index.php");
    exit();
}

// 2. PROSES GET: KOSONGKAN KERANJANG
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: index.php");
    exit();
}

// 3. PROSES GET: HAPUS SATU BARANG
if (isset($_GET['remove'])) {
    $id_remove = (int)$_GET['remove'];
    unset($_SESSION['cart'][$id_remove]);
    header("Location: index.php");
    exit();
}

$products = $pdo->query("SELECT * FROM m_produk WHERE stok > 0 ORDER BY nama_produk ASC")->fetchAll();

$total_belanja = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total_belanja += $item['subtotal'];
    }
}

$path_mundur = "../../"; 
require_once "../../includes/header.php"; 
?>

<div class="kasir-container">
    <div>
        <div class="card">
            <div class="card-title">Input Item Kasir</div>
            <form action="" method="POST" id="form-kasir" autocomplete="off">
                <input type="hidden" name="tambah_keranjang" value="1">
                
                <input type="hidden" name="id_produk" id="id_produk_hidden" required>
                
                <div class="form-group">
                    <label class="form-label" for="search-produk">Cari & Pilih Produk</label>
                    <div class="custom-dropdown-container">
                        <input type="text" id="search-produk" class="form-control" placeholder="Ketik nama produk untuk mencari..." required>
                        
                        <div id="custom-dropdown-results" class="custom-dropdown-results">
                            <?php foreach ($products as $p) : ?>
                                <div class="custom-dropdown-item" 
                                     data-id="<?php echo $p['id_produk']; ?>" 
                                     data-nama="<?php echo htmlspecialchars($p['nama_produk']); ?>"
                                     data-harga="Rp <?php echo number_format($p['harga_jual'], 0, ',', '.'); ?>"
                                     data-stok="<?php echo $p['stok']; ?>">
                                    <div>
                                        <span style="font-weight: 600;"><?php echo htmlspecialchars($p['nama_produk']); ?></span>
                                        <span class="product-price" style="margin-left: 8px;">(Rp <?php echo number_format($p['harga_jual'], 0, ',', '.'); ?>)</span>
                                    </div>
                                    <span class="product-stock">Sisa: <?php echo $p['stok']; ?> unit</span>
                                </div>
                            <?php endforeach; ?>
                            <div class="custom-dropdown-item" id="no-results" style="display: none; color: #64748b; font-style: italic; cursor: default; background: none;">
                                Barang tidak ditemukan...
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="qty">Quantity (Jumlah Beli)</label>
                    <input type="number" name="qty" id="qty" class="form-control" value="1" min="1" required style="font-weight: bold; font-size: 1.1rem; text-align: center;">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">+ Masukkan Keranjang</button>
            </form>
        </div>
        
        <!-- Tabel Detail Keranjang Belanja -->
        <div class="card">
            <div class="card-title">
                <span>Keranjang Belanja</span>
                <?php if (!empty($_SESSION['cart'])) : ?>
                    <a href="index.php?clear=1" class="btn btn-outline" style="color: #dc2626; padding: 0.3rem 0.6rem; font-size: 0.8rem;">Kosongkan</a>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Harga Satuan</th>
                            <th style="text-align: center;">Qty</th>
                            <th>Subtotal</th>
                            <th style="text-align: center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($_SESSION['cart'])) : ?>
                            <?php foreach ($_SESSION['cart'] as $item) : 
                                $qty = isset($item['Qty']) ? $item['Qty'] : (isset($item['qty']) ? $item['qty'] : 0);
                                $subtotal = isset($item['Subtotal']) ? $item['Subtotal'] : (isset($item['subtotal']) ? $item['subtotal'] : 0);
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong></td>
                                    <td>Rp <?php echo number_format($item['harga_jual'], 0, ',', '.'); ?></td>
                                    <td style="text-align: center; font-weight: 700;"><?php echo $qty; ?> pcs</td>
                                    <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                    <td style="text-align: center;">
                                        <a href="index.php?remove=<?php echo $item['id_produk']; ?>" class="btn btn-danger" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">X</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b; padding: 2.5rem;">Keranjang belanja kosong.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div>
        <!-- Panel Nota Pembayaran -->
        <div class="card" style="border: 2px solid #0d9488;">
            <div class="card-title">Nota & Pembayaran</div>
            <div style="background-color: #0f172a; color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                <p style="font-size: 0.85rem; color: #94a3b8; font-weight: 700;">TOTAL HARUS DIBAYAR</p>
                <h2 style="font-size: 2rem; color: #0d9488;">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></h2>
            </div>
            
            <form action="simpan.php" method="POST">
                <input type="hidden" name="transaction_token" value="<?php echo $_SESSION['transaction_token'] = bin2hex(random_bytes(32)); ?>">
                <input type="hidden" name="total_tagihan" value="<?php echo $total_belanja; ?>">
                
                <div class="form-group">
                    <label class="form-label">Nomor Nota</label>
                    <input type="text" name="nomor_nota" class="form-control" readonly value="<?php echo 'PJN-' . date('YmdHis'); ?>" style="background-color: #f1f5f9; font-weight: 700;">
                </div>
                <div class="form-group">
                    <label class="form-label">Uang Tunai Pembayaran (Rp)</label>
                    <input type="number" name="uang_bayar" class="form-control" required min="0" placeholder="Input nominal..." style="font-size: 1.25rem; font-weight: 700;" <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                </div>
                <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 1rem; font-size: 1.1rem;" <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>Selesaikan Transaksi ✔</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("search-produk");
    const resultsContainer = document.getElementById("custom-dropdown-results");
    const items = Array.from(document.querySelectorAll(".custom-dropdown-item:not(#no-results)"));
    const noResults = document.getElementById("no-results");
    const hiddenInput = document.getElementById("id_produk_hidden");

    searchInput.addEventListener("focus", function() {
        resultsContainer.style.display = "block";
        filterDropdown();
    });

    document.addEventListener("click", function(event) {
        if (!event.target.closest(".custom-dropdown-container")) {
            resultsContainer.style.display = "none";
        }
    });

    searchInput.addEventListener("input", filterDropdown);

    function filterDropdown() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        items.forEach(item => {
            const nama = item.getAttribute("data-nama");
            if (nama && nama.includes(searchTerm)) {
                item.style.display = "flex";
                visibleCount++;
            } else {
                item.style.display = "none";
            }
        });

        if (visibleCount === 0 && searchTerm !== "") {
            noResults.style.display = "block";
        } else {
            noResults.style.display = "none";
        }
    }

    items.forEach(item => {
        item.addEventListener("click", function() {
            const id = this.getAttribute("data-id");
            const nama = this.getAttribute("data-nama");
            const harga = this.getAttribute("data-harga");
            
            searchInput.value = this.querySelector('span').textContent;
            hiddenInput.value = id;

            resultsContainer.style.display = "none";
            
            document.getElementById("qty").focus();
        });
    });

    searchInput.addEventListener("change", function() {
        if (this.value.trim() === "") {
            hiddenInput.value = "";
        }
    });

    searchInput.focus();
});
</script>

<?php require_once "../../includes/footer.php"; ?>