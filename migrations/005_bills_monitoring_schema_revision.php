<?php
/**
 * Migration 005: Bills Monitoring schema revision
 * - type_of_billing: VARCHAR(100) -> ENUM('Water Bill','Internet Bill','Electricity Bill')
 * - link_to_file: VARCHAR(500) -> TEXT
 * - amount: DECIMAL(12,2) -> DOUBLE
 * - location_office: VARCHAR(255) -> ENUM('SDN Provincial Office','SDN Hill Relay Station')
 * - status: ENUM('Pending','Paid','Overdue','Disconnected') -> TINYINT(1) (1=Paid, 0=Unpaid)
 * - link_to_or: VARCHAR(500) -> TEXT
 * Backup: bills_monitoring_backup_005
 */
$done = [];
$fail = [];
$changes = [
    "ALTER TABLE bills_monitoring MODIFY COLUMN type_of_billing ENUM('Water Bill','Internet Bill','Electricity Bill') NOT NULL DEFAULT 'Water Bill'",
    "ALTER TABLE bills_monitoring MODIFY COLUMN link_to_file TEXT NULL",
    "ALTER TABLE bills_monitoring MODIFY COLUMN amount DOUBLE NULL",
    "ALTER TABLE bills_monitoring MODIFY COLUMN location_office ENUM('SDN Provincial Office','SDN Hill Relay Station') NOT NULL DEFAULT 'SDN Provincial Office'",
    "ALTER TABLE bills_monitoring MODIFY COLUMN status TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Paid,0=Unpaid'",
    "ALTER TABLE bills_monitoring MODIFY COLUMN link_to_or TEXT NULL",
];
foreach ($changes as $sql) {
    if ($c->query($sql)) { $done[] = $sql; } else { $fail[] = $c->error.' | '.$sql; }
}
echo json_encode(['done' => count($done), 'fail' => count($fail), 'errors' => $fail]);
