-- =====================================================
-- MIGRATION: Drop classifications table
-- Database: dict_proj
-- Run this SQL on the `dict_proj` database
-- =====================================================

-- Drop indexes on classifications table first
DROP INDEX IF EXISTS `idx_classifications_status` ON `classifications`;
DROP INDEX IF EXISTS `idx_classifications_category` ON `classifications`;
DROP INDEX IF EXISTS `idx_classifications_cat_sub` ON `classifications`;

-- Drop the classifications table
DROP TABLE IF EXISTS `classifications`;
