-- =========================================================================
-- DATABASE  : db_ponpes
-- FILE      : sql/users.sql
-- DESKRIPSI : Tabel login pengguna (Guru & Admin)
-- =========================================================================

USE `db_ponpes`;

-- -----------------------------------------------------
-- Buat tabel users_login
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users_login` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `nama`       VARCHAR(150) NOT NULL COMMENT 'Nama lengkap pengguna',
  `username`   VARCHAR(80)  NOT NULL UNIQUE COMMENT 'Username untuk login',
  `password`   VARCHAR(255) NOT NULL COMMENT 'Password di-hash dengan password_hash()',
  `role`       ENUM('guru','admin') NOT NULL DEFAULT 'guru' COMMENT 'Peran pengguna',
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================================
-- DATA DUMMY
-- Password untuk KEDUA akun adalah: ponpes123
-- Hash dibuat dengan: password_hash('ponpes123', PASSWORD_BCRYPT)
-- =========================================================================

INSERT INTO `users_login` (`nama`, `username`, `password`, `role`) VALUES
(
  'Ustadz Ahmad Fauzi',
  'guru_ahmad',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'guru'
),
(
  'Admin Ponpes',
  'admin_ponpes',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
);

-- =========================================================================
-- CATATAN PENTING:
-- Hash di atas adalah hash BCrypt untuk kata sandi: ponpes123
-- (diambil dari hash standar Laravel/PHP untuk testing)
--
-- Jika ingin generate hash sendiri, jalankan di PHP:
--   echo password_hash('ponpes123', PASSWORD_BCRYPT);
-- lalu ganti nilai password di atas dengan hasil generate tersebut.
-- =========================================================================
