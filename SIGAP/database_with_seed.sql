-- Database initialization with sample data for SIGAP application
-- Import this file in phpMyAdmin or via mysql CLI to create the schema and some sample members.
-- The script does NOT create an admin user; run setup_admin.php to create a secure admin (it will hash the password).

CREATE DATABASE IF NOT EXISTS `member_data` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `member_data`;

-- Table: users
-- Stores admin/login users for the app. Use setup_admin.php to create the initial admin user with a proper password hash.
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: members
CREATE TABLE IF NOT EXISTS `members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `telepon` VARCHAR(50) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL,
  `tanggal_lahir` DATE DEFAULT NULL,
  `tanggal_bergabung` DATE DEFAULT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Aktif',
  `foto` VARCHAR(255) DEFAULT NULL,
  `jenis_kelamin` VARCHAR(20) DEFAULT NULL,
  `pekerjaan` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_members_email` (`email`),
  INDEX `idx_members_status` (`status`),
  INDEX `idx_members_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: atestasi
CREATE TABLE IF NOT EXISTS `atestasi` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` INT UNSIGNED NOT NULL,
  `gereja_asal` VARCHAR(255) DEFAULT NULL,
  `gereja_tujuan` VARCHAR(255) DEFAULT NULL,
  `tanggal_keluar` DATE DEFAULT NULL,
  `tanggal_masuk` DATE DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_atestasi_member` (`member_id`),
  INDEX `idx_atestasi_status` (`status`),
  CONSTRAINT `fk_atestasi_member` FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `member_id` INT UNSIGNED DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_notifications_member` (`member_id`),
  INDEX `idx_notifications_is_read` (`is_read`),
  CONSTRAINT `fk_notifications_member` FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: activity_logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `table_name` VARCHAR(255) DEFAULT NULL,
  `record_id` INT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_activity_user` (`user_id`),
  INDEX `idx_activity_table` (`table_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample member data (active)
INSERT INTO members (nama, email, telepon, alamat, tanggal_lahir, tanggal_bergabung, status) VALUES
('Budi Santoso', 'budi@example.com', '0812000000', 'Jl. Merdeka 1', '1990-05-10', '2018-01-01', 'Aktif'),
('Siti Aminah', 'siti@example.com', '0813000000', 'Jl. Merdeka 2', '1988-12-20', '2019-03-15', 'Aktif'),
('Andi Wijaya', 'andi.w@example.com', '0814000000', 'Jl. Mawar 3', '1992-07-08', '2020-06-10', 'Aktif');

-- Optional: small sample notifications (linked to members above)
INSERT INTO notifications (`type`, `title`, `message`, `member_id`) VALUES
('info', 'Selamat Datang', 'Terima kasih telah bergabung.', 1),
('info', 'Perbarui Profil', 'Silakan lengkapi data profil Anda.', 2);

-- End of seeded SQL
