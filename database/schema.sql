-- ==========================================================
-- Database Schema: db_jasa_perbaikan
-- Sistem Jasa Perbaikan Peralatan Rumah Tangga Berbasis Web + AI
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `db_jasa_perbaikan` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_jasa_perbaikan`;

-- 1. Tabel Users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'technician', 'customer') NOT NULL DEFAULT 'customer',
    `phone` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `avatar` VARCHAR(255) NULL DEFAULT 'default_avatar.png',
    `specialization` VARCHAR(255) NULL COMMENT 'Untuk teknisi, misal: Spesialis AC & Pendingin',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Kategori Peralatan
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(100) NOT NULL DEFAULT 'fa-solid fa-screwdriver-wrench',
    `description` TEXT NULL,
    `base_inspection_fee` DECIMAL(12,2) NOT NULL DEFAULT 50000.00,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Layanan Spesifik
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `estimated_price_min` DECIMAL(12,2) NOT NULL DEFAULT 75000.00,
    `estimated_price_max` DECIMAL(12,2) NOT NULL DEFAULT 250000.00,
    `warranty_days` INT NOT NULL DEFAULT 30,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Pemesanan Perbaikan (Bookings)
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_code` VARCHAR(30) NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `technician_id` INT NULL,
    `category_id` INT NOT NULL,
    `service_id` INT NULL,
    `appliance_brand` VARCHAR(100) NULL,
    `appliance_model` VARCHAR(100) NULL,
    `issue_description` TEXT NOT NULL,
    `ai_diagnosis_summary` TEXT NULL,
    `initial_cost_estimate` VARCHAR(100) NULL,
    `scheduled_date` DATE NOT NULL,
    `scheduled_time` VARCHAR(50) NOT NULL,
    `address` TEXT NOT NULL,
    `address_notes` VARCHAR(255) NULL,
    `photo_url` VARCHAR(255) NULL,
    `status` ENUM('pending', 'assigned', 'on_the_way', 'in_progress', 'waiting_parts', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `cancellation_reason` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Log Riwayat Status (Tracking Timeline)
CREATE TABLE IF NOT EXISTS `booking_status_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `notes` TEXT NULL,
    `updated_by_user_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`updated_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel Rincian Biaya & Invoice (Costs & Invoices)
CREATE TABLE IF NOT EXISTS `booking_costs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL UNIQUE,
    `service_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sparepart_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sparepart_details` TEXT NULL COMMENT 'JSON atau teks rincian suku cadang',
    `discount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid',
    `payment_method` VARCHAR(50) NULL DEFAULT 'Tunai / Transfer',
    `proof_of_payment` VARCHAR(255) NULL,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `paid_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabel Ulasan & Penilaian (Reviews)
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `technician_id` INT NULL,
    `rating` TINYINT(1) NOT NULL DEFAULT 5,
    `comment` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabel Basis Pengetahuan AI & Expert Rules
CREATE TABLE IF NOT EXISTS `ai_knowledge_base` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NULL,
    `category_name` VARCHAR(100) NOT NULL,
    `symptom_keywords` TEXT NOT NULL COMMENT 'Katakunci pencocokan gejala',
    `issue_title` VARCHAR(200) NOT NULL,
    `probable_causes` TEXT NOT NULL,
    `diy_check` TEXT NOT NULL,
    `safety_warning` TEXT NOT NULL,
    `estimated_cost_min` DECIMAL(12,2) NOT NULL DEFAULT 50000.00,
    `estimated_cost_max` DECIMAL(12,2) NOT NULL DEFAULT 350000.00,
    `repair_time_estimate` VARCHAR(50) NOT NULL DEFAULT '1 - 2 Jam',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tabel Pengaturan Sistem
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
