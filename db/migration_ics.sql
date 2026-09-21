-- =====================================================
-- MIGRATION: Add Inventory Custodian Slip (ICS) tables
-- Run this SQL on the `dict_proj` database
-- =====================================================

-- 1. ICS header table (one row per slip)
CREATE TABLE `ics` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ics_no` VARCHAR(50) NOT NULL,
  `date_issued` DATE NOT NULL,
  `received_by` VARCHAR(255) DEFAULT NULL,
  `received_by_position` VARCHAR(255) DEFAULT NULL,
  `received_from` VARCHAR(255) DEFAULT NULL,
  `received_from_position` VARCHAR(255) DEFAULT NULL,
  `date_received` DATE DEFAULT NULL,
  `total` DECIMAL(12,2) DEFAULT 0.00,
  `created_by` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ics_no` (`ics_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. ICS itemized detail table (multiple rows per slip)
CREATE TABLE `ics_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ics_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL,
  `unit` VARCHAR(50) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `unit_cost` DECIMAL(12,2) DEFAULT 0.00,
  `date_acquired` DATE DEFAULT NULL,
  `inventory_item_no` VARCHAR(100) DEFAULT NULL,
  `estimated_useful_life` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ics_id` (`ics_id`),
  FOREIGN KEY (`ics_id`) REFERENCES `ics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;