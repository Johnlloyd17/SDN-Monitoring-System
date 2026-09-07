-- =====================================================
-- MIGRATION: Bills Monitoring feature
-- Run this SQL on the `dict_proj` database
-- =====================================================

CREATE TABLE IF NOT EXISTS `bills_monitoring` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `date_received` DATE NOT NULL,
  `type_of_billing` VARCHAR(100) NOT NULL DEFAULT '',
  `link_to_file` VARCHAR(500) DEFAULT NULL,
  `amount` DECIMAL(12,2) DEFAULT NULL,
  `location_office` VARCHAR(255) DEFAULT NULL,
  `due_date` DATE DEFAULT NULL,
  `disconnection_date` DATE DEFAULT NULL,
  `status` ENUM('Pending','Paid','Overdue','Disconnected') NOT NULL DEFAULT 'Pending',
  `date_paid` DATE DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `link_to_or` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `date_received` (`date_received`),
  KEY `due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
