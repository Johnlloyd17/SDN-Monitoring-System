-- =====================================================
-- MIGRATION: Restructure classifications table into a flat
-- category lookup (Name + Description) seeded with the
-- 4 core government ICT classification categories.
-- Database: dict_proj
-- Run this SQL on the `dict_proj` database
-- =====================================================

-- 1. Add description column (holds the category explanation shown to
--    admins when entering inventory records)
ALTER TABLE `classifications`
  ADD COLUMN `description` TEXT DEFAULT NULL AFTER `sub_item`;

-- 2. Remove legacy seed rows (this table is referenced by no page or
--    query in the codebase; the DICT Annex sub-item list is replaced
--    by the flat category lookup below)
DELETE FROM `classifications`;

-- 3. Enforce unique classification names so the lookup stays clean
ALTER TABLE `classifications`
  ADD UNIQUE KEY `uq_classifications_category` (`category`);

-- 4. Seed the 4 core categories (worded exactly as specified)
INSERT INTO `classifications` (`category`, `sub_item`, `description`, `status`) VALUES
('Hardware', '', 'Physical devices including desktop computers, laptops, servers, tablets, mobile phones, and hardware parts', 'active'),
('Peripherals and Accessories', '', 'Input and output devices such as printers, scanners, monitors, keyboards, mice, and external storage drives', 'active'),
('Network and Connectivity Equipment', '', 'Data communication tools including routers, switches, modems, hubs, and communication stations/networks', 'active'),
('Software and Digital Assets', '', 'Operating systems, productivity applications, computer software licenses, and databases', 'active');