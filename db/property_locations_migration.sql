-- =====================================================
-- MIGRATION: Create the property_locations lookup table
-- for the fixed location dropdown used by Format B
-- property numbers (YYYY-CC-NNN-ABBR).
-- Database: dict_proj
-- Run this SQL on the `dict_proj` database
--
-- Reused as-is (NOT touched by this migration):
--   property_categories   - category codes (01, 02)
--   property_no_counter   - shared per-year sequence (PK: seq_year)
--
-- No existing rows, columns, or tables are modified or deleted.
-- =====================================================

-- 1. Location lookup: code is the primary key (uppercase letters
--    and numbers only; values come ONLY from the list you provide).
--    `name` is the label shown in the dropdown.
CREATE TABLE `property_locations` (
  `code` varchar(16) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Seed rows - PLACEHOLDER: waiting for the exact code + name list.
--    Nothing is seeded until you supply it. Example of the shape only:
--
-- INSERT INTO `property_locations` (`code`, `name`) VALUES
-- ('XXX', 'XXX NAME');
