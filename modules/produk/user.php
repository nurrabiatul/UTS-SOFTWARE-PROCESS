<?php
require_once "../../config/database.php";
require_once "../../includes/auth_helper.php";
restrict_to_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    $role = sanitize($_POST['role']);
    
    if (empty($username) || empty($password) || empty($role)) {
        $_SESSION['error'] = "Semua kolom pendaftaran wajib diisi!";
    } else {
        $cek = $pdo->prepare("SELECT COUNT(*) FROM m_user WHERE username = ?");
        $cek->execute([$username]);
        if ($cek->fetchColumn() > 0) {
            $_SESSION['error'] = "Username sudah digunakan, silakan pilih yang lain.";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO m_user (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password_hash, $role]);
            $_SESSION['success'] = "Akun [$username] berhasil didaftarkan sebagai $role!";
            header("Location: user.php");
            exit();
        }
    }
}

if (isset($_GET['hapus'])) {
    $id_user_hapus = (int)$_GET['hapus'];
    
    if ($id_user_hapus === (int)$_SESSION['id_user']) {
        $_SESSION['error'] = "Anda tidak boleh menghapus akun Anda sendiri!";
    } else {
        $cek_trans = $pdo->prepare("SELECT COUNT(*) FROM t_penjualan WHERE id_user = ?");
        $cek_trans->execute([$id_user_hapus]);
        
        if ($cek_trans->fetchColumn() > 0) {
            $_SESSION['error'] = "User tidak bisa dihapus karena masih terikat data transaksi!";
        } else {
            $del = $pdo->prepare("DELETE FROM m_user WHERE id_user = ?");
            $del->execute([$id_user_hapus]);
            $_SESSION['success'] = "Akun kasir berhasil dihapus.";
        }
    }
    header("Location: user.php");
    exit();
}

$users = $pdo->query("SELECT id_user, username, role FROM m_user ORDER BY role ASC")->fetchAll();

$path_mundur = "../../";
require_once "../../includes/header.php";
?>

<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; align-items: start;">
    <div class="card">
        <div class="card-title">Daftarkan Akun Baru</div>
        <form action="" method="POST">
            <input type="hidden" name="tambah_user" value="1">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required placeholder="Ketik username baru">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Ketik password">
            </div>
            <div class="form-group">
                <label class="form-label">Role Akses</label>
                <select name="role" class="form-control" required>
                    <option value="Kasir">Kasir</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary" style="width: 100%;">Buat Akun</button>
        </form>
    </div>
    
    <div class="card">
        <div class="card-title">Daftar Pengguna Sistem</div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u) : ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                            <td><span class="badge <?php echo $u['role'] === 'Admin' ? 'badge-success' : 'badge-warning'; ?>"><?php echo $u['role']; ?></span></td>
                            <td style="text-align: center;">
                                <?php if ($u['id_user'] !== (int)$_SESSION['id_user']) : ?>
                                    <a href="user.php?hapus=<?php echo $u['id_user']; ?>" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('Apakah Anda yakin?');">Hapus</a>
                                <?php else : ?>
                                    <span style="font-size: 0.8rem; color: #64748b; font-style: italic;">Aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>