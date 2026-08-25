-- =====================================================
-- MIGRATION: Add performance indexes for query optimization
-- Database: dict_proj
-- Run this SQL on the `dict_proj` database
-- =====================================================

-- -------------------------------------------------------
-- HIGH PRIORITY: inventory table (6,652+ rows)
-- Columns used in WHERE, ORDER BY, LIKE search, and filter dropdowns
-- -------------------------------------------------------
ALTER TABLE `inventory` ADD INDEX `idx_inventory_project` (`project`);
ALTER TABLE `inventory` ADD INDEX `idx_inventory_ics` (`ics`);
ALTER TABLE `inventory` ADD INDEX `idx_inventory_date` (`date`);
ALTER TABLE `inventory` ADD INDEX `idx_inventory_item_type` (`item_type`);
ALTER TABLE `inventory` ADD INDEX `idx_inventory_classification` (`classification`);
ALTER TABLE `inventory` ADD INDEX `idx_inventory_remarks` (`remarks`(100));

-- -------------------------------------------------------
-- HIGH PRIORITY: tblactivityphoto (FK lookup with NO index)
-- Queried on every activity photo load: WHERE activityid = ?
-- -------------------------------------------------------
ALTER TABLE `tblactivityphoto` ADD INDEX `idx_activityphoto_activityid` (`activityid`);

-- -------------------------------------------------------
-- HIGH PRIORITY: pass_slip table
-- status filtered on every dashboard count, return_date for overdue detection
-- -------------------------------------------------------
ALTER TABLE `pass_slip` ADD INDEX `idx_pass_slip_status` (`status`);
ALTER TABLE `pass_slip` ADD INDEX `idx_pass_slip_return_date` (`return_date`);
ALTER TABLE `pass_slip` ADD INDEX `idx_pass_slip_pullout_date` (`pullout_date`);

-- -------------------------------------------------------
-- HIGH PRIORITY: tbllogs (continuously growing, unbounded reads)
-- -------------------------------------------------------
ALTER TABLE `tbllogs` ADD INDEX `idx_logs_logdate` (`logdate`);
ALTER TABLE `tbllogs` ADD INDEX `idx_logs_user` (`user`);

-- -------------------------------------------------------
-- HIGH PRIORITY: tblfwfa (1,777 rows, status filtered)
-- -------------------------------------------------------
ALTER TABLE `tblfwfa` ADD INDEX `idx_fwfa_status` (`status`);

-- -------------------------------------------------------
-- HIGH PRIORITY: tblactivity (8,087 rows, heavily filtered)
-- Used in dashboard N+1 queries, all 6 project pages, dashboard_data pages
-- -------------------------------------------------------
ALTER TABLE `tblactivity` ADD INDEX `idx_activity_project` (`project`);
ALTER TABLE `tblactivity` ADD INDEX `idx_activity_municipality` (`municipality`);
ALTER TABLE `tblactivity` ADD INDEX `idx_activity_district` (`district`);
ALTER TABLE `tblactivity` ADD INDEX `idx_activity_sector` (`sector`);
ALTER TABLE `tblactivity` ADD INDEX `idx_activity_agency` (`agency`);
ALTER TABLE `tblactivity` ADD INDEX `idx_activity_barangay` (`barangay`);
ALTER TABLE `tblactivity` ADD INDEX `idx_activity_start` (`start`);
-- Composite indexes for common filter patterns
ALTER TABLE `tblactivity` ADD INDEX `idx_activity_muni_proj` (`municipality`, `project`);
ALTER TABLE `tblactivity` ADD INDEX `idx_activity_dist_muni` (`district`, `municipality`);

-- -------------------------------------------------------
-- HIGH PRIORITY: tblparticipant (7,558 rows)
-- -------------------------------------------------------
ALTER TABLE `tblparticipant` ADD INDEX `idx_participant_project` (`project`);
ALTER TABLE `tblparticipant` ADD INDEX `idx_participant_start` (`start`);
ALTER TABLE `tblparticipant` ADD INDEX `idx_participant_mode` (`mode`);

-- -------------------------------------------------------
-- MEDIUM PRIORITY: classifications (small table but frequently queried)
-- -------------------------------------------------------
ALTER TABLE `classifications` ADD INDEX `idx_classifications_status` (`status`);
ALTER TABLE `classifications` ADD INDEX `idx_classifications_category` (`category`);
ALTER TABLE `classifications` ADD INDEX `idx_classifications_cat_sub` (`category`, `sub_item`);
