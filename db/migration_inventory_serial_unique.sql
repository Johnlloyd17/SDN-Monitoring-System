-- =====================================================
-- MIGRATION: Enforce unique serial numbers in inventory
-- at the database level.
-- Database: dict_proj
-- Run this SQL on the `dict_proj` database
-- =====================================================

-- A STORED generated column mirrors the serial number trimmed, but is
-- NULL whenever the serial is blank/whitespace. Because a UNIQUE index
-- treats NULLs as distinct, blank serials are always allowed while
-- non-empty duplicates are rejected by the database itself (regardless
-- of how the record is created: manual Add Record, Edit, or Import).
ALTER TABLE `inventory`
  ADD COLUMN `serial_unique` varchar(255) GENERATED ALWAYS AS (
    IF(`serial` IS NULL OR TRIM(`serial`) = '', NULL, TRIM(`serial`))
  ) STORED,
  ADD UNIQUE KEY `uq_inventory_serial_unique` (`serial_unique`);