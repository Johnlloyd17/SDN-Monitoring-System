-- =====================================================
-- MIGRATION: Add 'deployed' as a pass_slip status.
-- Database: dict_proj
--
-- Status semantics:
--   borrowed = returnable, out via Office Equipment Pass Slip
--   returned = back in stock
--   overdue  = returnable, past return_date
--   deployed = installed at a municipality/location (non-returnable)
--
-- A pass slip item becomes 'deployed' when it is confirmed
-- installed in a UAT record (see pages/uat/function.php). No
-- existing rows are changed. The member is appended so existing
-- ordinals stay stable.
-- =====================================================

ALTER TABLE `pass_slip`
  MODIFY COLUMN `status` ENUM('borrowed','returned','overdue','deployed') DEFAULT 'borrowed';