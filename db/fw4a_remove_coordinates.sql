-- Remove coordinates columns from tblfwfa (map pin backend removed)
-- Run this SQL on your database if you previously ran fw4a_add_coordinates.sql

ALTER TABLE `tblfwfa`
  DROP COLUMN `latitude`,
  DROP COLUMN `longitude`;
