-- =====================================================
-- MIGRATION: Add Pass Slip Attachments table
-- Run this SQL on the `dict_data` database
-- =====================================================

CREATE TABLE `pass_slip_attachments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pass_slip_no` VARCHAR(50) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `uploaded_by` VARCHAR(255) DEFAULT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pass_slip_no` (`pass_slip_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
