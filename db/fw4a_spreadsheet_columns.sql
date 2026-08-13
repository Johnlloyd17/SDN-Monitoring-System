-- Align tblfwfa schema with the FW4A.xlsx spreadsheet columns
-- Run this SQL on the dict_proj database.

ALTER TABLE `tblfwfa`
  MODIFY COLUMN `district` varchar(255) NULL DEFAULT NULL,
  ADD COLUMN `transport_location` varchar(255) NULL AFTER `district`,
  ADD COLUMN `transport_type` varchar(255) NULL AFTER `transport_location`,
  ADD COLUMN `nationwide_id` varchar(255) NULL AFTER `code`,
  ADD COLUMN `date_of_activation` date NULL AFTER `type`,
  ADD COLUMN `current_date_of_acceptance` date NULL AFTER `date_of_activation`,
  ADD COLUMN `latitude` decimal(10,7) NULL AFTER `current_date_of_acceptance`,
  ADD COLUMN `longitude` decimal(10,7) NULL AFTER `latitude`;
