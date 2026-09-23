-- =====================================================
-- MIGRATION: Allow NULL inventory_id on pass_slip
-- The Create Pass Slip form stores free-text description +
-- serial (no inventory picker), so inventory_id must be
-- nullable. All existing code already guards with
-- !empty($ps['inventory_id']).
-- =====================================================

ALTER TABLE `pass_slip`
MODIFY `inventory_id` INT(11) NULL;