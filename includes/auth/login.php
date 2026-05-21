<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php"; 

$error_message = "";

// 1. CEK BRUTE FORCE PREVENTION (BLOKIR 5 MENIT)
if (isset($_SESSION['lock_time'])) {
    $seconds_remaining = $_SESSION['lock_time'] - time();
    if ($seconds_remaining > 0) {
        $error_message = "Terlalu banyak percobaan, tunggu 5 menit.";
    } else {
        unset($_SESSION['lock_time']);
        unset($_SESSION['attempts']);
    }
}

// 2. PROSES POST LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validasi Kolom Kosong (Sesuai Standar Ujian)
    if (empty($username) || empty($password)) {
        $error_message = "Username dan Password wajib diisi!";
    } elseif (empty($error_message)) { 
        
        $stmt = $pdo->prepare("SELECT * FROM m_user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            unset($_SESSION['attempts']);
            unset($_SESSION['lock_time']);

            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: ../../modules/transaksi/index.php");
            exit();
        } else {
            $_SESSION['attempts'] = isset($_SESSION['attempts']) ? $_SESSION['attempts'] + 1 : 1;

            if ($_SESSION['attempts'] >= 3) {
                $_SESSION['lock_time'] = time() + (5 * 60);
                $error_message = "Terlalu banyak percobaan, tunggu 5 menit.";
            } else {
                $error_message = "Username atau Password salah! (Sisa percobaan : " . (3 - $_SESSION['attempts']) . ")";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI-KASIR Maju Jaya</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body class="login-body">

<div class="login-wrapper">
    <div class="login-header">
        <h2>SI-KASIR</h2>
        <p>Sistem Kasir Swalayan - Maju Jaya</p>
    </div>

    <?php if (!empty($error_message)) : ?>
        <div class="alert alert-danger" style="text-align: left; margin-bottom: 1.5rem;">
            ⚠️ <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off">
        <div class="form-group" style="text-align: left;">
            <label class="form-label" for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" 
                   placeholder="Masukkan username" 
                   <?php echo isset($_SESSION['lock_time']) && $_SESSION['lock_time'] > time() ? 'disabled' : ''; ?>>
        </div>

        <div class="form-group" style="text-align: left; margin-bottom: 1.75rem;">
            <label class="form-label" for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" 
                   placeholder="Masukkan password" 
                   <?php echo isset($_SESSION['lock_time']) && $_SESSION['lock_time'] > time() ? 'disabled' : ''; ?>>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem;"
                <?php echo isset($_SESSION['lock_time']) && $_SESSION['lock_time'] > time() ? 'disabled' : ''; ?>>
            Masuk
        </button>
    </form>
</div>

</body>
</html>