-- =====================================================
-- MIGRATION: Due-date push notification dismissals
-- Database: dict_proj
-- Run this SQL on the `dict_proj` database
-- =====================================================

CREATE TABLE IF NOT EXISTS `notification_dismissals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_id` INT(11) NOT NULL,
  `source_type` ENUM('bill','letter') NOT NULL,
  `source_id` INT(11) NOT NULL,
  `dismissed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dismissal` (`admin_id`,`source_type`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;