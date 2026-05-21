-- =============================================
-- DATABASE: db_majujaya
-- Sistem Informasi Kasir Terintegrasi (SI-KASIR)
-- By: Muhammad Maulana Rachman
-- =============================================

CREATE DATABASE IF NOT EXISTS db_majujaya
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_majujaya;

-- =============================================
-- 1. TABEL: m_user
-- =============================================
CREATE TABLE m_user (
    id_user   INT            NOT NULL AUTO_INCREMENT,
    username  VARCHAR(50)    NOT NULL,
    password  VARCHAR(255)   NOT NULL,
    role      ENUM('Admin','Kasir') NOT NULL,
    PRIMARY KEY (id_user),
    UNIQUE KEY uk_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 2. TABEL: m_produk
-- =============================================
CREATE TABLE m_produk (
    id_produk    INT             NOT NULL AUTO_INCREMENT,
    nama_produk  VARCHAR(100)    NOT NULL,
    harga_jual   DECIMAL(10,2)   NOT NULL,
    stok         INT             NOT NULL DEFAULT 0,
    PRIMARY KEY (id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 3. TABEL: t_penjualan
-- =============================================
CREATE TABLE t_penjualan (
    id_penjualan  INT             NOT NULL AUTO_INCREMENT,
    nomor_nota    VARCHAR(20)     NOT NULL,
    tgl_transaksi DATETIME        NOT NULL,
    total_bayar   DECIMAL(12,2)   NOT NULL,
    id_user       INT             NOT NULL,
    PRIMARY KEY (id_penjualan),
    UNIQUE KEY uk_nomor_nota (nomor_nota),
    CONSTRAINT fk_penjualan_user
        FOREIGN KEY (id_user) REFERENCES m_user(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 4. TABEL: t_penjualan_detail
-- =============================================
CREATE TABLE t_penjualan_detail (
    id_detail     INT             NOT NULL AUTO_INCREMENT,
    id_penjualan  INT             NOT NULL,
    id_produk     INT             NOT NULL,
    qty           INT             NOT NULL,
    subtotal      DECIMAL(12,2)   NOT NULL,
    PRIMARY KEY (id_detail),
    CONSTRAINT fk_detail_penjualan
        FOREIGN KEY (id_penjualan) REFERENCES t_penjualan(id_penjualan),
    CONSTRAINT fk_detail_produk
        FOREIGN KEY (id_produk) REFERENCES m_produk(id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 5. TABEL: t_log_stok
-- =============================================
CREATE TABLE t_log_stok (
    id_log     INT                      NOT NULL AUTO_INCREMENT,
    id_produk  INT                      NOT NULL,
    jumlah     INT                      NOT NULL,
    tipe       ENUM('Masuk','Keluar')   NOT NULL,
    keterangan VARCHAR(255)             NULL,
    waktu_log  TIMESTAMP                NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_log),
    CONSTRAINT fk_log_produk
        FOREIGN KEY (id_produk) REFERENCES m_produk(id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- SEED DATA: User default Admin
-- Password: admin123 (hash bcrypt)
-- =============================================
INSERT INTO m_user (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin'),
('kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir');

-- =============================================
-- SEED DATA: Produk contoh
-- =============================================
INSERT INTO m_produk (nama_produk, harga_jual, stok) VALUES
('Indomie Goreng', 3500.00, 100),
('Aqua 600ml', 4000.00, 50),
('Teh Botol Sosro', 5000.00, 80),
('Chitato Original', 12000.00, 30),
('Minyak Goreng 1L', 18000.00, 25);
