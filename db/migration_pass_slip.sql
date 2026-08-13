-- =====================================================
-- MIGRATION: Add Pass Slip feature to Property Records
-- Run this SQL on the `dict_data` database
-- =====================================================

-- 1. Add item_type column to inventory table
-- Distinguishes between equipment (returnable) and consumable supplies (non-returnable)
ALTER TABLE `inventory` 
ADD COLUMN `item_type` ENUM('equipment','consumable') DEFAULT 'equipment' 
AFTER `classification`;

-- 2. Update existing records based on classification
-- Equipment categories (returnable)
UPDATE `inventory` SET `item_type` = 'equipment' 
WHERE `classification` IN ('Satellite', 'Access Point', 'Router/Switch', 'UPS', 'COMBOX', 'Laptop', 'Desktop', 'Monitor', 'Printer', 'Projector', 'Camera', 'Speaker', 'Tablet');

-- Consumable/non-returnable categories
UPDATE `inventory` SET `item_type` = 'consumable' 
WHERE `classification` IN ('Battery', 'Toner', 'Cable', 'Paper', 'Ink', 'Cartridge', 'Supply', 'Consumable');

-- 3. Create pass_slip table for Office Equipment Pass Slip
CREATE TABLE `pass_slip` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pass_slip_no` VARCHAR(50) NOT NULL,
  `inventory_id` INT(11) NOT NULL,
  `item_description` VARCHAR(255) NOT NULL,
  `qty` INT(11) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  
  -- Pull-Out (Going Out) details
  `pullout_date` DATE NOT NULL,
  `requested_by_out` VARCHAR(255) NOT NULL,
  `inspected_by_out` VARCHAR(255) NOT NULL,
  `approved_by_out` VARCHAR(255) NOT NULL,
  
  -- Return details
  `return_date` DATE DEFAULT NULL,
  `requested_by_return` VARCHAR(255) DEFAULT NULL,
  `inspected_by_return` VARCHAR(255) DEFAULT NULL,
  `approved_by_return` VARCHAR(255) DEFAULT NULL,
  
  -- Status and tracking
  `status` ENUM('borrowed','returned','overdue') DEFAULT 'borrowed',
  `purpose` TEXT DEFAULT NULL,
  `condition_out` VARCHAR(100) DEFAULT NULL,
  `condition_return` VARCHAR(100) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_by` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `pass_slip_no` (`pass_slip_no`),
  FOREIGN KEY (`inventory_id`) REFERENCES `inventory`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Remove UNIQUE constraint on pass_slip_no to allow multiple items per slip
-- Run this ONLY if the pass_slip table already exists with a UNIQUE constraint
ALTER TABLE `pass_slip` DROP INDEX `pass_slip_no`;
ALTER TABLE `pass_slip` ADD INDEX `pass_slip_no` (`pass_slip_no`);
