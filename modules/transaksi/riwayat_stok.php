<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';
requireKasir();

$sql = "
    SELECT
        ts.*,
        mu.username
    FROM t_penjualan ts
    JOIN m_user mu
        ON ts.id_user = mu.id_user
    ORDER BY ts.tgl_transaksi DESC
    LIMIT 50
";
$stmt = $pdo->query($sql);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
include __DIR__ . '/../../includes/header.php';
?>