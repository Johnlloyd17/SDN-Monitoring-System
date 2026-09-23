-- =====================================================
-- MIGRATION: Restructure inventory table to the fixed
-- 14-column list (Index Records).
-- Database: dict_proj
-- Run this SQL on the `dict_proj` database
-- =====================================================

-- Drop columns that no longer belong to the record structure.
-- Their indexes (idx_inventory_classification, idx_inventory_item_type,
-- idx_inventory_ics, idx_inventory_status) are dropped automatically
-- with the columns.
ALTER TABLE `inventory`
  DROP COLUMN `classification`,
  DROP COLUMN `item_type`,
  DROP COLUMN `property`,
  DROP COLUMN `ics`,
  DROP COLUMN `officer`,
  DROP COLUMN `transferred`,
  DROP COLUMN `status`,
  ADD COLUMN `inventory_item_no` varchar(255) DEFAULT NULL AFTER `received`,
  ADD COLUMN `assigned_to` varchar(255) DEFAULT NULL AFTER `inventory_item_no`,
  -- Total Cost is ALWAYS derived: Quantity x Unit Cost. A STORED generated
  -- column keeps it in sync automatically, even when Quantity is changed by
  -- the Pass Slip module or by CSV Import. It is never typed by the user.
  ADD COLUMN `total_cost` decimal(15,2) GENERATED ALWAYS AS (
    IF(
      `cost` IS NULL OR TRIM(`cost`) = '' OR `quantity` IS NULL,
      NULL,
      `quantity` * CAST(REPLACE(`cost`, ',', '') AS DECIMAL(15,2))
    )
  ) STORED AFTER `cost`,
  ADD INDEX `idx_inventory_inventory_item_no` (`inventory_item_no`),
  ADD INDEX `idx_inventory_assigned_to` (`assigned_to`);