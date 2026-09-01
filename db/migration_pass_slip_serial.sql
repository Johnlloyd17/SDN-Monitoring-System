-- =====================================================
-- MIGRATION: Add serial_no column to pass_slip table
-- Run this SQL on the `dict_data` database
-- =====================================================

-- 1. Add serial_no column after item_description
ALTER TABLE `pass_slip`
ADD COLUMN `serial_no` VARCHAR(255) DEFAULT NULL
AFTER `unit`;
