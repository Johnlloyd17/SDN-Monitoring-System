-- =====================================================
-- MIGRATION: Make pass_slip.inventory_id nullable
-- Run this SQL on the `dict_data` database
--
-- Pass Slip items are now manual/free-text descriptions and
-- are no longer required to link to an inventory record.
-- =====================================================

-- 1. Drop the existing foreign key (name may vary per environment)
SET @fk_name := (
  SELECT CONSTRAINT_NAME
  FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'pass_slip'
    AND COLUMN_NAME = 'inventory_id'
    AND REFERENCED_TABLE_NAME = 'inventory'
  LIMIT 1
);

SET @sql := IF(@fk_name IS NULL,
  'SELECT 1',
  CONCAT('ALTER TABLE `pass_slip` DROP FOREIGN KEY `', @fk_name, '`')
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Make inventory_id nullable
ALTER TABLE `pass_slip` MODIFY `inventory_id` INT(11) DEFAULT NULL;

-- 3. Re-add the foreign key (keeps cascade for items still linked to inventory)
SET @sql := IF(@fk_name IS NULL,
  'SELECT 1',
  CONCAT('ALTER TABLE `pass_slip` ADD CONSTRAINT `', @fk_name,
         '` FOREIGN KEY (`inventory_id`) REFERENCES `inventory`(`id`) ON DELETE CASCADE')
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;