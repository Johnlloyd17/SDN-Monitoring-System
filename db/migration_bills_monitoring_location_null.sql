-- MIGRATION: Allow bills_monitoring.location_office to be NULL
-- So empty Location/Office rows can be imported/stored as blank.

ALTER TABLE `bills_monitoring`
  MODIFY `location_office` enum('SDN Provincial Office','SDN Hill Relay Station')
  DEFAULT NULL;