-- =====================================================
-- MIGRATION: UAT (User Acceptance Test) Management tables
-- Run this SQL on the `dict_proj` database.
-- Models the physical DICT "User Acceptance Form (UAT)
-- Attachments" document: one header per location, with
-- multiple equipment/CPE line items linked by a foreign key.
-- =====================================================

-- 1. UAT header table (one row per location)
CREATE TABLE IF NOT EXISTS `uat` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `municipality` VARCHAR(150) NOT NULL,
  `strategy` VARCHAR(100) NOT NULL,
  `transport_location` VARCHAR(255) NOT NULL,
  `latitude` DECIMAL(10,7) DEFAULT NULL,
  `longitude` DECIMAL(10,7) DEFAULT NULL,
  `created_by` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. UAT equipment / CPE line items (multiple rows per header)
CREATE TABLE IF NOT EXISTS `uat_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uat_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL DEFAULT 1,
  `unit` VARCHAR(50) DEFAULT NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `serial_numbers` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uat_id` (`uat_id`),
  CONSTRAINT `fk_uat_items_uat` FOREIGN KEY (`uat_id`) REFERENCES `uat`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;