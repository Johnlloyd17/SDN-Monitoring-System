-- =====================================================
-- MIGRATION: Letters Monitoring feature
-- Run this SQL on the `dict_proj` database
-- =====================================================

CREATE TABLE IF NOT EXISTS `letters_monitoring` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `type` ENUM('Incoming','Outgoing') NOT NULL DEFAULT 'Incoming',
  `subject` VARCHAR(255) NOT NULL DEFAULT '',
  `fw4a` ENUM('Request','Provision') NOT NULL DEFAULT 'Request',
  `link_incoming` VARCHAR(500) DEFAULT NULL,
  `for_response` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `date_responded` DATE DEFAULT NULL,
  `link_outgoing` VARCHAR(500) DEFAULT NULL,
  `responsible_person` VARCHAR(255) DEFAULT NULL,
  `who_attended` TEXT DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `post_activity_report` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `type` (`type`),
  KEY `for_response` (`for_response`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
