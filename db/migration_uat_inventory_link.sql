-- =====================================================
-- MIGRATION: Link uat_items equipment rows to an Inventory
-- row (inventory.id) as a second search source alongside the
-- existing Pass Slip link. Database: dict_proj
--
-- Unlike pass_slip_item_id, there is NO unique constraint:
-- one inventory row can represent several physical units, so
-- multiple UAT rows across locations may reference it.
--
-- ON DELETE RESTRICT mirrors the pass-slip FK: an inventory
-- row referenced by a UAT installation cannot be deleted.
--
-- XOR rule is enforced in application code (pages/uat/function.php):
-- a uat_items row links to EITHER pass_slip_item_id OR this
-- inventory_id, never both; rows with neither = manual typing.
-- =====================================================

ALTER TABLE `uat_items`
  ADD COLUMN `inventory_id` INT(11) DEFAULT NULL AFTER `pass_slip_item_id`,
  ADD KEY `idx_uat_items_inventory` (`inventory_id`),
  ADD CONSTRAINT `fk_uat_items_inventory`
    FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE RESTRICT;