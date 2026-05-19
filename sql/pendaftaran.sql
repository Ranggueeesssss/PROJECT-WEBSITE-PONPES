-- phpMyAdmin SQL Dump
-- Database: `db_ponpes`
-- Struktur dari tabel `pendaftaran_santri`

CREATE TABLE `pendaftaran_santri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(150) NOT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat_lengkap` text NOT NULL,
  `nomor_handphone` varchar(20) NOT NULL,
  `jenjang_pendaftaran` varchar(50) NOT NULL,
  `tahu_ponpes` varchar(150) NOT NULL,
  `nama_informan` varchar(150) NOT NULL,
  `file_ijazah` varchar(255) DEFAULT NULL,
  `file_kk` varchar(255) NOT NULL,
  `file_akta` varchar(255) DEFAULT NULL,
  `file_ktp_ortu` varchar(255) NOT NULL,
  `file_surat_tjm` varchar(255) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================================
-- JALANKAN QUERY INI JIKA TABEL SUDAH ADA (ALTER TABLE)
-- Tambahkan kolom baru ke tabel yang sudah ada di phpMyAdmin
-- =========================================================================
ALTER TABLE `pendaftaran_santri`
  ADD COLUMN `file_ktp_ortu`  varchar(255) NOT NULL DEFAULT '' AFTER `file_akta`,
  ADD COLUMN `file_surat_tjm` varchar(255) NOT NULL DEFAULT '' AFTER `file_ktp_ortu`;
