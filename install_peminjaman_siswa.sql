-- Jalankan sekali di phpMyAdmin (database: sarpras_lab) agar peminjaman & halaman inventaris lengkap.
CREATE TABLE IF NOT EXISTS `peminjaman_siswa` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_barang` int NOT NULL,
  `jumlah` int NOT NULL DEFAULT 1,
  `tujuan` varchar(500) NOT NULL DEFAULT '',
  `durasi` varchar(100) NOT NULL DEFAULT '',
  `catatan` varchar(500) DEFAULT '-',
  `kelas` varchar(100) NOT NULL DEFAULT '',
  `nama` varchar(200) NOT NULL DEFAULT '',
  `tgl_pinjam` date DEFAULT NULL,
  `tanggal_pinjam` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Menunggu',
  PRIMARY KEY (`id`),
  KEY `idx_peminjaman_id_barang` (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opsional: isi status kosong pada tabel siswa (baris lama yang NULL di phpMyAdmin)
UPDATE `siswa`
SET `status_akun` = COALESCE(`status_akun`, 'Aktif'),
    `status_peminjaman` = COALESCE(`status_peminjaman`, 'Tidak Ada Hutang')
WHERE `status_akun` IS NULL OR `status_peminjaman` IS NULL;
