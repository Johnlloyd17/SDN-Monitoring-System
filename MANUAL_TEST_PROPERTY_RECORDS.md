# Manual Test — Inventory Records (Property No. / Layout)

This guide lets you reproduce the data samples and checks by hand in the browser
(`pages/property_records/property_records.php`). No backend or DB changes are needed —
everything is done through the page's own UI.

---

## 0. Sample data used in this guide

| Item | Value |
|---|---|
| Category (existing) | `01 - ICT Equipment` |
| Category to add | `03 - Test Equipment` |
| Location to add | `MAIN` → name "Main Office" |
| Location to add | `BR` → name "Storage Branch" |
| Project to use | any project that already exists (e.g. the first one in the Project dropdown) |
| Format B sample number | `2026-01-001-MAIN` |

> Note: `property_locations` starts empty, so you MUST create the two locations first (step 1).
> Categories already exist (`01`); add `03` if you want to prove the counter is shared per year.

---

## 1. Preflight — add test data

1. Open the Inventory Records page.
2. Click **Manage Locations** (toolbar under the Category/Location list, the two-column "List" layout):
   - Add code `MAIN`, name `Main Office`.
   - Add code `BR`, name `Storage Branch`.
3. Click **Manage Categories**:
   - If `03` does not exist, add code `03`, name `Test Equipment`.
4. Confirm both dropdowns now show the new values (Project / Year / Remarks are untouched).

**Expected:** Category and Location dropdowns (filters and in the Add modal) list the new entries.

---

## 2. Number generation tests (Add modal)

Use **Add Record**. Fill the required fields (Project, Item No., Quantity, Unit, Description).
Date Acquired is required for generation. Set Inventory Item no. exactly as each test says.

| # | Test | Inventory Item no. field | Date Acquired | Category | Location | Expected saved number |
|---|------|--------------------------|---------------|----------|----------|------------------------|
| 1 | Auto-generate (fresh) | *(empty)* | 2026-03-10 | 01 | MAIN | `2026-01-001-MAIN` |
| 2 | Shared counter across categories | *(empty)* | 2026-04-01 | 03 | MAIN | `2026-03-002-MAIN` (second of the year, not 001) |
| 3 | Manual number kept verbatim | `GRP-P6-MANUAL` | 2026-04-02 | 01 | MAIN | `GRP-P6-MANUAL` (exactly as typed) |
| 4 | Generator skips an occupied slot | first add `2026-01-003-MAIN` manually (Date 2026), then add another with empty number | 2026-05-01 | 01 | MAIN | `2026-01-004-MAIN` (003 already taken) |
| 5 | Year reset | *(empty)* | 2027-01-15 | 01 | BR | `2027-01-001-BR` (new year → 001 again) |
| 6 | Unknown location (grid/Add panel) | *(empty)* | 2026-06-01 | 01 | *(none/unknown)* | Error: unknown/generic — record is NOT saved |
| 7 | Preview matches real save | in Live Preview, pick Category 01, Location MAIN, Date 2026-07-01, empty number | 2026-07-01 | 01 | MAIN | Preview `2026-01-005-MAIN` then real save produces the same |

**Expected behavior for all:**
- Auto numbers are `YYYY-CC-NNN-LOC` where `CC` = category code, `NNN` = 3-digit zero-padded sequence, `LOC` = location code.
- The sequence counter is **one per year**, shared across categories and locations.
- Typing an Inventory Item no. saves it **verbatim**; generation only runs when the field is **empty** and a Category + Location are chosen.

---

## 3. Group / Units tests

1. On the main table, click a **group** row (blue cube icon in a column) to open the group modal ("Units").
2. In the group modal's **Add Unit** area:
   - Pick Category `01`, Location `MAIN`, leave Inventory Item no. empty, set a date in 2026.
   - Save. **Expected:** the unit gets its own generated number (`2026-01-00X-MAIN`), NOT a copy of the group's number.
3. Re-open the same group → **Edit Group Details**:
   - Change a shared field (e.g. Description), click **Save Changes**.
   - **Expected:** group values update and every unit's `inventory_item_no` is unchanged.
4. Add a unit with a manual number (e.g. `UNIT-MANUAL`).
   - **Expected:** stored verbatim; later auto units skip nothing (manual value doesn't match `YYYY-CC-NNN-LOC`).

---

## 4. Series Register modal tests

1. Click the **Inventory Item no.** link (`fa-list-ol` + number) in any row.
   - Expected: the "Property No. Series Register" modal opens, Year dropdown pre-set to that number's year, the number listed once (no yellow highlight anymore).
2. Year dropdown: change to a different year → only that year's series show; "All years" shows everything.
3. Category dropdown: pick `01 - ICT Equipment` → only category-01 series show, Category column shows e.g. `01 - ICT Equipment`.
4. Location dropdown: pick `MAIN` → only MAIN series show.
5. Search box: type part of a number/description/serial → filters instantly.
6. **Clear** button: resets search + all dropdowns in one click.
7. "Show only new-style numbers" checkbox: only hides/shows the "Other formats" section. If there are NO other-format numbers, the checkbox is hidden entirely.
8. Header summary reads correctly, e.g. `8 records · 2 years · 1 other format` (proper plurals).

---

## 5. Table / layout tests

1. **Filters row:** Project, Year, Remarks, Search share one row, same height, plus **Clear filters**.
   - Pick a Project / Year / Remarks, type a search term → each filters the list.
   - **Clear filters** resets all four and reloads page 1.
2. **Action buttons row:** Add Record + Delete Selected on the left; Import + Export on the right; equal heights. (Add/Delete hidden for staff role.)
3. **Per-page:** "Show N records per page" now sits at the bottom next to "Showing X to Y of Z entries"; changing it reloads page 1. Pagination arrows still work.
4. **Option column:** it is the **last** column (after Remarks), NOT floating. Scroll horizontally:
   - every column is reachable (checkbox … Remarks … Option);
   - header row stays aligned with the cells at every scroll position;
   - no column overlaps another.
5. **Compact buttons:** Edit, View Files, Pass Slip, Print are comfortably small and on one line.
6. **First row actions:** Edit opens modal, View Files opens file list, Print opens the sticker, Pass Slip shows history.
7. **Group rows:** clicking a group row still opens its units.
8. Header text reads **"Est. Useful Life"** (shortened) and is not cut off.

---

## 6. Cleanup (after tests)

- Delete the test records via the row checkboxes + **Delete Selected** (or the group modal's unit delete), keeping only real data.
- `MAIN` / `BR` locations and category `03` can stay for future tests, or be removed from **Manage Locations / Manage Categories**.
- Serial-search and filters must reflect the deletion immediately after reload.

---

## 7. Notes on intended behavior

- The per-year counter increments **before** the row is inserted, so a number can be skipped if an insert fails — this is by design.
- Numbers are the only thing that must be unique-looking; the generator skips slots already occupied by a matching `YYYY-CC-NNN-LOC` value (loop-guarded, aborts after 1000 tries).
- Manual numbers are never reformatted.