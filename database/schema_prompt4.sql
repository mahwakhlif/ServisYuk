-- ==========================================================
-- Database Relational Schema (PROMPT 4)
-- Sistem Jasa Perbaikan Peralatan Rumah Tangga Berbasis Web + AI
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `db_jasa_perbaikan` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_jasa_perbaikan`;

-- Drop existing tables in reverse dependency order if needed for clean re-creation
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `service_reports`;
DROP TABLE IF EXISTS `diagnoses`;
DROP TABLE IF EXISTS `technician_assignments`;
DROP TABLE IF EXISTS `service_requests`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `technicians`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabel Users
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'customer', 'technician') NOT NULL DEFAULT 'customer',
    `phone` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `avatar` VARCHAR(255) NULL DEFAULT 'default_avatar.png',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Technicians (1:1 with users table)
CREATE TABLE `technicians` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `specialization` VARCHAR(255) NOT NULL COMMENT 'Contoh: Spesialis AC, Kulkas, Mesin Cuci',
    `experience` VARCHAR(100) NOT NULL DEFAULT '3 Tahun',
    `availability` ENUM('available', 'busy', 'off') NOT NULL DEFAULT 'available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_technicians_availability` (`availability`),
    INDEX `idx_technicians_specialization` (`specialization`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Services (Katalog Layanan & Estimasi Tarif)
CREATE TABLE `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `service_name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `estimated_price` DECIMAL(12,2) NOT NULL DEFAULT 75000.00,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_services_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Service Requests (Permintaan Jasa Perbaikan)
CREATE TABLE `service_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_code` VARCHAR(50) NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `service_id` INT NULL,
    `appliance_name` VARCHAR(100) NOT NULL COMMENT 'AC, Kulkas, Mesin Cuci, TV, Kompor, Dispenser, dll.',
    `brand` VARCHAR(100) NULL,
    `model` VARCHAR(100) NULL,
    `complaint` TEXT NOT NULL,
    `photo` VARCHAR(255) NULL,
    `address` TEXT NOT NULL,
    `schedule` DATETIME NOT NULL,
    `status` ENUM(
        'Menunggu',
        'Dikonfirmasi',
        'Teknisi Ditugaskan',
        'Dalam Perjalanan',
        'Pemeriksaan',
        'Dalam Perbaikan',
        'Selesai',
        'Dibatalkan'
    ) NOT NULL DEFAULT 'Menunggu',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL,
    INDEX `idx_requests_customer` (`customer_id`),
    INDEX `idx_requests_service` (`service_id`),
    INDEX `idx_requests_status` (`status`),
    INDEX `idx_requests_schedule` (`schedule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Technician Assignments (Penugasan Teknisi)
CREATE TABLE `technician_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `technician_id` INT NOT NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('assigned', 'accepted', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'assigned',
    `notes` TEXT NULL,
    FOREIGN KEY (`request_id`) REFERENCES `service_requests`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`technician_id`) REFERENCES `technicians`(`id`) ON DELETE CASCADE,
    INDEX `idx_assignments_request` (`request_id`),
    INDEX `idx_assignments_technician` (`technician_id`),
    INDEX `idx_assignments_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel Diagnoses (Hasil Diagnosa AI & Teknisi)
CREATE TABLE `diagnoses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NULL,
    `complaint` TEXT NOT NULL,
    `ai_result` TEXT NULL COMMENT 'JSON/Text Analisis AI: Kemungkinan Penyebab, Urgensi, Safety Warning, Estimasi Biaya',
    `technician_result` TEXT NULL COMMENT 'Catatan konfirmasi atau temuan fisik oleh teknisi',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `service_requests`(`id`) ON DELETE SET NULL,
    INDEX `idx_diagnoses_request` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabel Service Reports (Laporan Akhir Pengerjaan 1:1)
CREATE TABLE `service_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL UNIQUE,
    `technician_id` INT NOT NULL,
    `inspection_result` TEXT NOT NULL,
    `repair_action` TEXT NOT NULL,
    `replaced_component` TEXT NULL,
    `service_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sparepart_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('unpaid', 'paid') NOT NULL DEFAULT 'paid',
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `notes` TEXT NULL,
    `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `service_requests`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`technician_id`) REFERENCES `technicians`(`id`) ON DELETE CASCADE,
    INDEX `idx_reports_request` (`request_id`),
    INDEX `idx_reports_technician` (`technician_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
