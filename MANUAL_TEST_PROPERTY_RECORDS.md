# Sample Data — Property No. Series Register Test

Paste this into phpMyAdmin (database `dict_proj` → SQL tab). It covers every part of the Series Register flow.

```sql
-- Same person, same year (2025) -> tests running-number order inside one year
INSERT INTO `inventory` (`project`, `item`, `quantity`, `unit`, `description`, `received`, `inventory_item_no`, `assigned_to`, `serial`, `date`, `cost`) VALUES
('TEST', 'Laptop', 1, 'Pcs', 'Test Laptop A', 'Juan Dela Cruz', '2025-01-001-SDN', 'Juan Dela Cruz', 'LTP-2025-001', '2025-03-10', '25000.00'),
('TEST', 'Laptop', 1, 'Pcs', 'Test Laptop B', 'Juan Dela Cruz', '2025-01-002-SDN', 'Juan Dela Cruz', 'LTP-2025-002', '2025-03-10', '25000.00');

-- Same person, new year (2026) + different category code (02 = Office Equipment)
INSERT INTO `inventory` (`project`, `item`, `quantity`, `unit`, `description`, `received`, `inventory_item_no`, `assigned_to`, `serial`, `date`, `cost`) VALUES
('TEST', 'Printer', 1, 'Pcs', 'Test Printer A', 'Juan Dela Cruz', '2026-02-001-SDN', 'Juan Dela Cruz', 'PRN-2026-001', '2026-02-01', '8000.00');

-- Second person -> tests the per-person banner grouping
INSERT INTO `inventory` (`project`, `item`, `quantity`, `unit`, `description`, `received`, `inventory_item_no`, `assigned_to`, `serial`, `date`, `cost`) VALUES
('TEST', 'Router', 1, 'Pcs', 'Test Router A', 'Maria Santos', '2026-01-005-SDN', 'Maria Santos', 'RTR-2026-005', '2026-01-20', '12000.00'),
('TEST', 'Router', 1, 'Pcs', 'Test Router B', 'Maria Santos', '2026-01-006-SDN', 'Maria Santos', 'RTR-2026-006', '2026-01-20', '12000.00');

-- Different location code (ADS) -> tests the Location filter
INSERT INTO `inventory` (`project`, `item`, `quantity`, `unit`, `description`, `received`, `inventory_item_no`, `assigned_to`, `serial`, `date`, `cost`) VALUES
('TEST', 'Camera', 1, 'Pcs', 'Test Camera ADS', 'Pedro Cruz', '2026-01-001-ADS', 'Pedro Cruz', 'CAM-ADS-001', '2026-04-05', '9500.00');

-- Non-standard numbers -> appear under "Other formats" section
INSERT INTO `inventory` (`project`, `item`, `quantity`, `unit`, `description`, `received`, `inventory_item_no`, `assigned_to`, `serial`, `date`, `cost`) VALUES
('TEST', 'Switch', 1, 'Pcs', 'Test Switch old-format', 'Juan Dela Cruz', 'SDN-2026-045', 'Juan Dela Cruz', 'SW-OLD-045', '2026-05-02', '15000.00'),
('TEST', 'Cable', 1, 'box', 'Test Cable old-format', 'Maria Santos', 'PR-12345', 'Maria Santos', 'CBL-OLD-12345', '2026-05-03', '3000.00');

-- No Property No. -> must NOT appear in the register at all
INSERT INTO `inventory` (`project`, `item`, `quantity`, `unit`, `description`, `serial`, `date`) VALUES
('TEST', 'Mouse', 2, 'Pcs', 'Test item with no property number', 'MSE-NO-001', '2026-05-04');
```

## What to test afterward

1. Open **Inventory Records** — the 8 new rows appear; the "Mouse" row shows "–" (no Property No. link).
2. Click any new Property No. link → register opens showing **only that person's** items, with the "Showing only: …" bar and a **Show all** button.
3. **Show all** → items grouped by person banner, then by Year, in running-number order.
4. Filters: Year, Category (01/02), Location (SDN/ADS), search box, and the "Show only new-style numbers" checkbox.
5. "Other formats" section lists `SDN-2026-045` and `PR-12345` (only when the Format-B checkbox is off).

## Cleanup after testing

```sql
DELETE FROM `inventory` WHERE `project` = 'TEST';
```

> Note: the register only reads `inventory_item_no`, `description`, `serial`, `quantity`, `date`, `received`, `assigned_to` — the `project = 'TEST'` tag is just so you can delete the rows easily.
