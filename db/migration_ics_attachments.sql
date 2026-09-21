-- =====================================================
-- MIGRATION: Add ICS Attachments table
-- Run this SQL on the project database
-- =====================================================

CREATE TABLE IF NOT EXISTS `ics_attachments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ics_no` VARCHAR(50) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `uploaded_by` VARCHAR(255) DEFAULT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ics_no` (`ics_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;