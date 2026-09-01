-- =====================================================
-- MIGRATION: Add status column to inventory table
-- Run this SQL on the `dict_data` database
-- =====================================================

-- 1. Add status ENUM column after remarks
ALTER TABLE `inventory`
ADD COLUMN `status` ENUM('Available','For Deployment','Deployed','Temporary Deployed','Defective','Replaced') DEFAULT 'Available'
AFTER `remarks`;

-- 2. Migrate existing remarks data into the new status column
-- Map known values to the closest status
UPDATE `inventory` SET `status` = 'Deployed' WHERE `remarks` = 'Deployed';
UPDATE `inventory` SET `status` = 'Temporary Deployed' WHERE `remarks` LIKE '%Temporary Deployed%';
UPDATE `inventory` SET `status` = 'Available' WHERE `remarks` = 'Available';
UPDATE `inventory` SET `status` = 'Defective' WHERE `remarks` = 'Defective';
UPDATE `inventory` SET `status` = 'Replaced' WHERE `remarks` = 'Replaced';
UPDATE `inventory` SET `status` = 'For Deployment' WHERE `remarks` = 'For Deployment';

-- All other records stay at default 'Available'

-- 3. Add index for status filtering
ALTER TABLE `inventory` ADD INDEX `idx_inventory_status` (`status`);
