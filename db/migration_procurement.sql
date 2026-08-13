-- Migration: Create procurement table

CREATE TABLE IF NOT EXISTS `procurement_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pr_no` varchar(255) DEFAULT NULL,
  `activity_id` varchar(255) DEFAULT NULL,
  `activity_name` varchar(255) DEFAULT NULL,
  `link_to_file` text DEFAULT NULL,
  `project_fund_source` varchar(255) DEFAULT NULL,
  `type_of_items_procured` varchar(255) DEFAULT NULL,
  `amount` decimal(18,2) DEFAULT NULL,
  `name_of_supplier` varchar(255) DEFAULT NULL,
  `jo_po` varchar(255) DEFAULT NULL,
  `link_to_attachments` text DEFAULT NULL,
  `personnel_in_charge` varchar(255) DEFAULT NULL,
  `date_forwarded_to_ro` date DEFAULT NULL,
  `transmittal_report` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
