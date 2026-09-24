-- =====================================================
-- MIGRATION: Link uat_items equipment rows to the specific
-- Pass Slip item (pass_slip.id) that was pulled out, forming
-- the traceable chain: Inventory -> Pass Slip -> UAT.
-- Database: dict_proj
--
-- ON DELETE RESTRICT is intentional: once a pass slip item has a
-- confirmed UAT installation record, its pass slip can no longer be
-- deleted (deleting it would remove evidence behind an installation).
-- The pass slip delete flow (pages/pass_slip/function.php, btn_delete)
-- was updated to report blockages clearly.
--
-- The UNIQUE key enforces 1:1: each pass slip item can be linked to
-- AT MOST ONE uat_items row (cannot be "confirmed installed" twice).
-- NULL links (manual/free-typed rows) are allowed and unlimited.
-- =====================================================

ALTER TABLE `uat_items`
  ADD COLUMN `pass_slip_item_id` INT(11) DEFAULT NULL AFTER `uat_id`,
  ADD UNIQUE KEY `uq_uat_items_pass_slip_item` (`pass_slip_item_id`),
  ADD CONSTRAINT `fk_uat_items_pass_slip_item`
    FOREIGN KEY (`pass_slip_item_id`) REFERENCES `pass_slip` (`id`) ON DELETE RESTRICT;