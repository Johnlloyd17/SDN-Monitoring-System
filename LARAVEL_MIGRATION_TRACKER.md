# LARAVEL MIGRATION TRACKER
## DICT-SDN Activity Management & Monitoring System
### Vanilla PHP → Laravel Migration Plan

---

## PROJECT OVERVIEW

| Detail | Value |
|--------|-------|
| **Project Name** | DICT-SDN Activity Management & Monitoring System |
| **Current Stack** | Vanilla PHP, MariaDB, AdminLTE, jQuery |
| **Target Stack** | Laravel 11, Livewire, Blade, AdminLTE 3, Bootstrap 5 |
| **Total Estimated Hours** | 40-55 hours |
| **Total Estimated Days** | 4-6 full work days |
| **Start Date** | ________ |
| **Target Completion** | ________ |

---

## CONFIRMED TECH STACK

### Backend

| Component | Choice | Package/Version |
|-----------|--------|-----------------|
| **Framework** | Laravel | 11.x |
| **PHP** | PHP | 8.1+ |
| **Database** | MariaDB | 10.4 (keep existing) |
| **Auth** | Laravel Breeze | `laravel/breeze` |
| **ORM** | Eloquent | Built-in |
| **Roles** | Custom (single login) | Merge Admin + Zone Leader into one login with `role` column |

### Frontend

| Component | Choice | Package/Version |
|-----------|--------|-----------------|
| **Templating** | Blade | Built-in |
| **Dynamic UI** | Livewire | `livewire/livewire` |
| **Reactivity** | Alpine.js | `alpinejs/alpinejs` |
| **CSS Framework** | Bootstrap 5 | `npm install bootstrap` |
| **Admin Template** | AdminLTE 3 | `admin-lte/admin-lte` |
| **Icons** | Font Awesome 6 | CDN or `@fortawesome/fontawesome-free` |
| **Build Tool** | Vite | Built-in with Laravel 11 |
| **Package Manager** | npm | — |

### Data Display

| Component | Choice | Package/Version |
|-----------|--------|-----------------|
| **DataTables** | Yajra DataTables | `yajra/laravel-datatables` |
| **Dropdowns** | Select2 | `select2/select2` (npm) |
| **Charts** | ApexCharts | `apexcharts/apexcharts` |

### Packages

| Component | Choice | Package/Version |
|-----------|--------|-----------------|
| **PDF Generation** | DomPDF | `barryvdh/laravel-dompdf` |
| **Import/Export** | Laravel Excel | `maatwebsite/excel` |
| **Backup** | Spatie Backup | `spatie/laravel-backup` |
| **Images** | Intervention Image | `intervention/image` |

### Architecture Decisions

| Decision | Details |
|----------|---------|
| **Login System** | Single login page with role-based access (Admin + Zone Leader merged) |
| **Pass Slip Forms** | Livewire component for dynamic add/remove items |
| **Cascading Filters** | Livewire for real-time filtering (no page reloads) |
| **AJAX Username Check** | Livewire component |
| **CSV Import** | Laravel Excel with queue support |
| **CSV Export** | Laravel Excel or Yajra DataTables export |
| **Reports/Charts** | ApexCharts (replacing Morris.js) |
| **Print Layouts** | DomPDF for pass slip and forms |

---

## CURRENT PROJECT STATISTICS

| Metric | Count |
|--------|-------|
| Custom PHP Files | ~120 |
| Total Custom Code | ~15,000-20,000 lines |
| Database Tables | 22+ |
| Functional Modules | 12+ |
| CSS Files | 93 |
| JS Files | 268 |
| SQL Schema Lines | 3,768 |

---

## PHASE 1: LARAVEL SETUP & DATABASE FOUNDATION

### Day 1 Target: ~6-8 hours

---

### Step 1.1: Install Laravel & Configure Environment
**Estimated Time:** 1 hour
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Task | Status | Notes |
|------|--------|-------|
| Create Laravel project via composer | [ ] | `composer create-project laravel/laravel dict-sdn-monitoring` |
| Configure `.env` database connection | [ ] | Connect to existing `dict_data` MariaDB |
| Set timezone to `Asia/Manila` | [ ] | In `config/app.php` |
| Set locale and other app settings | [ ] | |
| Test database connection | [ ] | `php artisan tinker` → `DB::connection()->getPdo()` |

---

### Step 1.2: Create All Database Migrations
**Estimated Time:** 3-4 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Migration File | Table | Status | Notes |
|----------------|-------|--------|-------|
| `create_tblusers_table` | `tbluser` | [ ] | Admin accounts (id, username, password, type) |
| `create_tblstaff_table` | `tblstaff` | [ ] | Staff accounts (id, name, username, password) |
| `create_tblzones_table` | `tblzone` | [ ] | Zone accounts (id, zone, username, password) |
| `create_tblactivities_table` | `tblactivity` | [ ] | 22 columns (start, end, project, subproject, indicator, activity, training, municipality, barangay, district, agency, mode, sector, person, resource, participants, completers, male, female, approved, mov, remarks) |
| `create_tblactivityphotos_table` | `tblactivityphoto` | [ ] | File attachments (id, activityid, filename) |
| `create_tblparticipants_table` | `tblparticipant` | [ ] | Participant records (id, start, end, activity, indicator, fullname, sex, contact, email, mode, agency, sector, project, person, remarks) |
| `create_tblprojects_table` | `tblproject` | [ ] | Municipality/project reference |
| `create_tblbrgies_table` | `tblbrgy` | [ ] | Barangay reference |
| `create_tbllocalities_table` | `tbllocality` | [ ] | Locality reference |
| `create_tblmunicipals_table` | `tblmunicipal` | [ ] | Municipality reference |
| `create_tbllogs_table` | `tbllogs` | [ ] | Audit trail (user, logdate, action) |
| `create_inventories_table` | `inventory` | [ ] | 16 columns (project, item, classification, quantity, unit, description, received, property, ics, serial, date, officer, cost, life, transferred, remarks) |
| `create_pass_slips_table` | `pass_slip` | [ ] | 19 columns (pass_slip_no, inventory_id, item_description, qty, unit, pullout_date, return_date, status, purpose, condition_out, condition_return, requested_by_out, inspected_by_out, approved_by_out, remarks, created_by) |
| `create_classifications_table` | `classifications` | [ ] | ICT equipment taxonomy (category, sub_item, status) |
| `create_tblbpls_table` | `tblbpls` | [ ] | ~30 columns (province, district, municipality, lgu, class, system, action, + 20 YN/status pairs) |
| `create_tblbplsmonitorings_table` | `tblbplsmonitoring` | [ ] | BPLS municipality master list |
| `create_tblfwfas_table` | `tblfwfa` | [ ] | FreeWifi4All site data (18 columns: locality, barangay, district, transport_location, transport_type, locations, type, date_of_activation, current_date_of_acceptance, latitude, longitude, code, nationwide_id, strategy, status, reason, remarks) |
| `create_locationrequests_table` | `locationrequests` | [ ] | Letter requests (11 columns) |
| `create_tbltech4eds_table` | `tbltech4ed` | [ ] | Tech4Ed centers (48 columns) |
| `create_cybersecurity_metrics_table` | `cybersecurity_metrics` | [ ] | Targets (category, subcategory, year, target) |
| `create_targets_initiatives_table` | `targets_initiatives` | [ ] | Planned activities (23 columns) |
| `create_tblsdns_table` | `tblsdn` | [ ] | SDN municipality reference |
| `create_tbltypes_table` | `tbltype` | [ ] | Site type reference |

---

### Step 1.3: Create Eloquent Models
**Estimated Time:** 2 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Model | Table | Relationships | Status |
|-------|-------|---------------|--------|
| `User` | `tbluser` | — | [ ] |
| `Staff` | `tblstaff` | — | [ ] |
| `Zone` | `tblzone` | — | [ ] |
| `Activity` | `tblactivity` | hasMany(ActivityPhoto), hasMany(Participant) | [ ] |
| `ActivityPhoto` | `tblactivityphoto` | belongsTo(Activity) | [ ] |
| `Participant` | `tblparticipant` | hasMany(ActivityPhoto) | [ ] |
| `Project` | `tblproject` | — | [ ] |
| `Brgy` | `tblbrgy` | — | [ ] |
| `Locality` | `tbllocality` | — | [ ] |
| `Municipal` | `tblmunicipal` | — | [ ] |
| `Log` | `tbllogs` | — | [ ] |
| `Inventory` | `inventory` | hasMany(PassSlip), hasMany(ActivityPhoto) | [ ] |
| `PassSlip` | `pass_slip` | belongsTo(Inventory) | [ ] |
| `Classification` | `classifications` | — | [ ] |
| `Bpls` | `tblbpls` | hasMany(ActivityPhoto) | [ ] |
| `BplsMonitoring` | `tblbplsmonitoring` | — | [ ] |
| `Fwfa` | `tblfwfa` | — | [ ] |
| `LocationRequest` | `locationrequests` | hasMany(ActivityPhoto) | [ ] |
| `Tech4ed` | `tbltech4ed` | — | [ ] |
| `CybersecurityMetric` | `cybersecurity_metrics` | — | [ ] |
| `TargetInitiative` | `targets_initiatives` | hasMany(ActivityPhoto) | [ ] |
| `Sdn` | `tblsdn` | — | [ ] |
| `Type` | `tbltype` | — | [ ] |

---

### Step 1.4: Database Seeders
**Estimated Time:** 1 hour
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Seeder | Source | Record Count | Status |
|--------|--------|--------------|--------|
| `ClassificationsSeeder` | `migration_classifications.sql` | 134 sub-items, 12 categories | [ ] |
| `CybersecurityMetricsSeeder` | From `dict_final.sql` | TBD | [ ] |
| `SdnSeeder` | `tblsdn` data | TBD | [ ] |
| `LocalitySeeder` | `tbllocality` data | TBD | [ ] |
| `TypeSeeder` | `tbltype` data | TBD | [ ] |
| `BrgySeeder` | `tblbrgy` data | TBD | [ ] |
| `BplsMonitoringSeeder` | `tblbplsmonitoring` data | TBD | [ ] |

---

### Day 1 Checklist
- [ ] Laravel project created and running (`php artisan serve`)
- [ ] Database connection verified
- [ ] All 23 migrations created and tested (`php artisan migrate`)
- [ ] All 23 models created with correct relationships
- [ ] All 7 seeders created and tested (`php artisan db:seed`)
- [ ] Data verified in database matches original

---

## PHASE 2: CORE INFRASTRUCTURE

### Day 1-2 Target: ~8-10 hours

---

### Step 2.1: Authentication System
**Estimated Time:** 3 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Task | Status | Notes |
|------|--------|-------|
| Configure dual guard (users + staff) | [ ] | Two providers in `config/auth.php` |
| Create custom User model for `tbluser` | [ ] | Implements `Authenticatable` |
| Create custom Staff model for `tblstaff` | [ ] | Implements `Authenticatable` |
| Create LoginController | [ ] | Role-based redirect after login |
| Create login Blade view | [ ] | Match current `login.php` design |
| Create profile update controller | [ ] | Replaces `header.php` change account modal |
| Create password hashing migration | [ ] | One-time script to hash existing plaintext passwords |
| Test login as Administrator | [ ] | Should redirect to dashboard |
| Test login as Staff | [ ] | Should redirect to dashboard with limited sidebar |
| Test logout | [ ] | Should destroy session and redirect to login |

**Password Migration Script:**
```php
// database/seeders/HashExistingPasswordsSeeder.php
// Iterate tbluser and tblstaff, hash plaintext passwords
// Run ONCE during migration
```

---

### Step 2.2: Layout & Template System
**Estimated Time:** 3-4 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status | Notes |
|-----------|------|--------|-------|
| Master Layout | `resources/views/layouts/app.blade.php` | [ ] | Head, header, sidebar, footer skeleton |
| Head CSS Component | `resources/views/components/head-css.blade.php` | [ ] | Bootstrap 3, AdminLTE, Font Awesome, DataTables, Select2 CSS |
| Header Component | `resources/views/components/header.blade.php` | [ ] | Top navbar + profile modal |
| Sidebar Component | `resources/views/components/sidebar.blade.php` | [ ] | Role-based navigation |
| Footer Component | `resources/views/components/footer.blade.php` | [ ] | JS includes + utility functions |
| Stat Box Component | `resources/views/components/stat-box.blade.php` | [ ] | Reusable info box |
| Delete Modal Component | `resources/views/components/delete-modal.blade.php` | [ ] | Reusable confirmation |
| Added Notification | `resources/views/components/notifs/added.blade.php` | [ ] | Success toast |
| Edit Notification | `resources/views/components/notifs/edit.blade.php` | [ ] | Edit toast |
| Delete Notification | `resources/views/components/notifs/delete.blade.php` | [ ] | Delete toast |
| Duplicate Notification | `resources/views/components/notifs/duplicate.blade.php` | [ ] | Error toast |

**Sidebar Navigation Items:**
| Menu Item | Icon | Sub-Items | Admin | Staff |
|-----------|------|-----------|-------|-------|
| Dashboard | `fa-tachometer-alt` | — | [ ] | [ ] |
| Cybersecurity | `fa-shield-alt` | Activities, Participants, Reports | [ ] | [ ] |
| DREAMS | `fa-project-diagram` | (empty) | [ ] | [ ] |
| eLGU BPLS | `fa-cogs` | Monitoring, Activities, Database | [ ] | [ ] |
| FreeWifi4All | `fa-wifi` | Monitoring, Strategy, Letters, Activities | [ ] | [ ] |
| GECS | `fa-clipboard-list` | Activities | [ ] | [ ] |
| GovNet | `fa-network-wired` | (empty) | [ ] | [ ] |
| IIDB | `fa-database` | Activities | [ ] | [ ] |
| ILCDB | `fa-desktop` | Activities, Participants, Tech4Ed, Reports | [ ] | [ ] |
| Procurement | `fa-cart-plus` | — | [ ] | [ ] |
| Property Management | `fa-archive` | Inventory, Classification, Pass Slip, Purchase Request | [ ] | [ ] |
| Credentials | `fa-user-cog` | — | [ ] | ❌ |
| Logs | `fa-history` | — | [ ] | ❌ |

---

### Step 2.3: Route Definitions
**Estimated Time:** 2 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Route Group | Routes | Status |
|-------------|--------|--------|
| Auth (login/logout/profile) | 4 routes | [ ] |
| Dashboard | 1 route + 8 drill-down | [ ] |
| Activities (all projects) | 7 routes × 6 projects | [ ] |
| Participants | 7 routes (resource) | [ ] |
| Inventory | 7 routes + import/export | [ ] |
| Classifications | 7 routes (resource) | [ ] |
| Pass Slips | 7 routes + return/print/history | [ ] |
| BPLS | 7 routes + monitoring | [ ] |
| FreeWifi4All | 7 routes + strategy/active/inactive/penetration/barangay | [ ] |
| Letter Requests | 7 routes (resource) | [ ] |
| Tech4Ed | 7 routes (resource) | [ ] |
| Targets | 7 routes (resource) | [ ] |
| Reports | 2 routes | [ ] |
| Admin (zones/credentials/logs/backup) | 4 routes | [ ] |

---

### Step 2.4: Middleware
**Estimated Time:** 1 hour
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Middleware | Purpose | Status |
|-----------|---------|--------|
| `role:admin` | Check Administrator role | [ ] |
| `role:staff` | Check staff role | [ ] |
| `permission:write` | Check write access (username-based) | [ ] |
| `audit` | Auto-log operations to tbllogs | [ ] |
| `ForceHttps` | HTTPS enforcement | [ ] |

---

### Day 2 Checklist
- [ ] Authentication working (login/logout/profile update)
- [ ] Passwords hashed in database
- [ ] Layout renders correctly (header, sidebar, footer)
- [ ] Sidebar shows correct items for Administrator
- [ ] Sidebar shows correct items for Staff
- [ ] All routes defined and accessible
- [ ] Middleware protecting admin routes
- [ ] Dashboard page loads with layout

---

## PHASE 3: MODULE-BY-MODULE MIGRATION

### Day 2-4 Target: ~20-24 hours

---

### Module 1: Dashboard
**Estimated Time:** 3-4 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `DashboardController.php` | [ ] |
| Main View | `dashboard/index.blade.php` | [ ] |
| Activities Data View | `dashboard/activities-data.blade.php` | [ ] |
| Participants Data View | `dashboard/participants-data.blade.php` | [ ] |
| Sectors Data View | `dashboard/sectors-data.blade.php` | [ ] |
| Agencies Data View | `dashboard/agencies-data.blade.php` | [ ] |
| Municipalities Data View | `dashboard/municipalities-data.blade.php` | [ ] |
| Barangays Data View | `dashboard/barangays-data.blade.php` | [ ] |
| District1 Data View | `dashboard/district1-data.blade.php` | [ ] |
| District2 Data View | `dashboard/district2-data.blade.php` | [ ] |
| Edit Modal Component | `dashboard/edit-modal.blade.php` | [ ] |

**Business Logic to Preserve:**
- [ ] 8 stat boxes with drill-down links
- [ ] Municipality/City cross-tabulation DataTable
- [ ] Triple cascading filters (project → year → month)
- [ ] DataTables with all columns
- [ ] Live date/time display

**Database Tables:** `tblactivity`, `tblparticipant`, `tblproject`

---

### Module 2: Activities by Project
**Estimated Time:** 4-5 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `ActivityController.php` | [ ] |
| Form Request (Store) | `StoreActivityRequest.php` | [ ] |
| Form Request (Update) | `UpdateActivityRequest.php` | [ ] |
| Index View | `activities/index.blade.php` | [ ] |
| Add Modal | `activities/add-modal.blade.php` | [ ] |
| Edit Modal | `activities/edit-modal.blade.php` | [ ] |
| View Modal (Files) | `activities/view-modal.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `index($project)` | [ ] | Filter by project (cybersecurity, elgu, fwfa, gecs, iidb, ilcdb) |
| `store(StoreActivityRequest)` | [ ] | Create + file upload + audit log |
| `update(UpdateActivityRequest, Activity)` | [ ] | Update + audit log |
| `destroy(Request)` | [ ] | Bulk delete + audit log |
| `addImage(Request)` | [ ] | Upload photos to tblactivityphoto |
| `removeImage(Request)` | [ ] | Remove photos from tblactivityphoto |
| `import(Request)` | [ ] | CSV import to tblactivity |
| `export($project, Request)` | [ ] | CSV export filtered by project |

**Project-Specific Filters:**
| Project | Filter By | Status |
|---------|-----------|--------|
| Cybersecurity | indicator, sector, municipality, barangay | [ ] |
| eLGU BPLS | mode, sector, municipality, barangay | [ ] |
| FWFA | mode, sector, municipality, barangay | [ ] |
| GECS | mode, sector, municipality, barangay | [ ] |
| IIDB | mode, sector, municipality, barangay | [ ] |
| ILCDB | indicator, subproject, municipality, barangay | [ ] |

**Import/Export Files:**
| File | Status |
|------|--------|
| `import.php` → Laravel CSV import | [ ] |
| `exportgecs.php` → `export(GECS)` | [ ] |
| `exportfwfa.php` → `export(FWFA)` | [ ] |
| `exportelgu.php` → `export(eLGU BPLS)` | [ ] |
| `export_cyber.php` → `export(Cybersecurity)` | [ ] |
| `exportiidb.php` → `export(IIDB)` | [ ] |
| `export_ilcdb.php` → `export(ILCDB)` | [ ] |

**Database Tables:** `tblactivity`, `tblactivityphoto`, `tbllogs`

---

### Module 3: Property Records
**Estimated Time:** 3-4 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `InventoryController.php` | [ ] |
| Classification Controller | `ClassificationController.php` | [ ] |
| Index View | `inventory/index.blade.php` | [ ] |
| Add Modal | `inventory/add-modal.blade.php` | [ ] |
| Edit Modal | `inventory/edit-modal.blade.php` | [ ] |
| View Modal (Files) | `inventory/view-modal.blade.php` | [ ] |
| Classification View | `inventory/classification-list.blade.php` | [ ] |
| Purchase Request View | `inventory/purchase-request.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `index()` | [ ] | 6 stat boxes + DataTable |
| `store()` | [ ] | Create + file upload + cost formatting |
| `update()` | [ ] | Update + cost formatting |
| `destroy()` | [ ] | Bulk delete + audit log |
| `import()` | [ ] | CSV import to inventory |
| `export()` | [ ] | CSV export with filters |
| `addImage()` | [ ] | Upload photos |
| `removeImage()` | [ ] | Remove photos |

**Classification Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `index()` | [ ] | DataTable of classifications |
| `store()` | [ ] | Create with duplicate check |
| `update()` | [ ] | Update with duplicate check |
| `destroy()` | [ ] | Bulk delete |

**Special Logic:**
- [ ] Cost field: `str_replace(',', '', $cost)` for numeric storage
- [ ] Select2 dropdown with optgroup by category
- [ ] 6 stat boxes: Total Items, Total Categories, Currently Borrowed, Overdue Returns, Total Asset Value, Consumable Supplies

**Database Tables:** `inventory`, `classifications`, `tblactivityphoto`, `pass_slip`

---

### Module 4: Pass Slip System
**Estimated Time:** 4-5 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `PassSlipController.php` | [ ] |
| Index View | `pass-slip/index.blade.php` | [ ] |
| Add Modal | `pass-slip/add-modal.blade.php` | [ ] |
| View Modal | `pass-slip/view-modal.blade.php` | [ ] |
| Return Modal | `pass-slip/return-modal.blade.php` | [ ] |
| Print View | `pass-slip/print.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `index()` | [ ] | Grouped DataTable by pass_slip_no |
| `store()` | [ ] | Auto-generate PS-{YEAR}-{NNNN} + inventory decrement |
| `return($id)` | [ ] | Process return + inventory restore |
| `destroy(Request)` | [ ] | Bulk delete + inventory restore for borrowed items |
| `print($id)` | [ ] | PDF generation |
| `history($inventoryId)` | [ ] | AJAX JSON endpoint |
| `itemDetails($passSlipNo)` | [ ] | AJAX JSON endpoint |

**Critical Business Logic:**
- [ ] Auto-numbering: `PS-{YEAR}-{NNNN}` (e.g., PS-2025-0001)
- [ ] Inventory quantity validation before borrow
- [ ] Database transactions for decrement/increment
- [ ] Only restore quantity for `status = 'borrowed'` on delete/return
- [ ] Mark overdue: `status = 'overdue'` when `return_date < CURDATE()`

**Status Labels:**
| Status | Color | CSS Class |
|--------|-------|-----------|
| Borrowed | Yellow | `label-warning` |
| Returned | Green | `label-success` |
| Overdue | Red | `label-danger` |

**Database Tables:** `pass_slip`, `inventory`, `tbllogs`

---

### Module 5: eLGU BPLS
**Estimated Time:** 2-3 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `BplsController.php` | [ ] |
| Database View | `bpls/database.blade.php` | [ ] |
| Monitoring View | `bpls/monitoring.blade.php` | [ ] |
| Dict Detail View | `bpls/dict-detail.blade.php` | [ ] |
| Add/Edit Modal | `bpls/add-edit-modal.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `index()` | [ ] | Database with filters |
| `monitoring()` | [ ] | Dashboard with stat boxes |
| `dictDetail()` | [ ] | 27-column detail table |
| `store()` | [ ] | Create BPLS record |
| `update()` | [ ] | Update BPLS record |
| `destroy()` | [ ] | Bulk delete |

**BPLS System Types:**
1. `DICT (eLGU BPLS)` — V1
2. `DICT (eLGU)` — V2
3. `For Training`
4. `With own system/manual`

**Database Tables:** `tblbpls`, `tblbplsmonitoring`, `tblactivityphoto`

---

### Module 6: FreeWifi4All
**Estimated Time:** 3-4 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `Fw4aController.php` | [ ] |
| Monitoring View | `fw4a/monitoring.blade.php` | [ ] |
| Strategy View | `fw4a/strategy.blade.php` | [ ] |
| Active View | `fw4a/active.blade.php` | [ ] |
| Inactive View | `fw4a/inactive.blade.php` | [ ] |
| Penetration View | `fw4a/penetration.blade.php` | [ ] |
| Barangay View | `fw4a/barangay.blade.php` | [ ] |
| Add/Edit Modal | `fw4a/add-edit-modal.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `index()` | [ ] | Monitoring dashboard with stat boxes |
| `strategy()` | [ ] | Dual pivot/crosstab tables |
| `active()` | [ ] | Active access points with filters |
| `inactive()` | [ ] | Inactive access points with filters |
| `penetration()` | [ ] | Cross-table calculation (tblsdn + tblfwfa) |
| `barangay()` | [ ] | Binary Yes/No coverage per barangay |
| `store()` | [ ] | Create FW4A record |
| `update()` | [ ] | Update FW4A record |
| `destroy()` | [ ] | Bulk delete |

**Strategy Pivot Queries:**
- [ ] By Municipality/City (rows) × Strategy (columns)
- [ ] By Site Type (rows) × Strategy (columns)
- [ ] 7 strategies: PICS MUN, PICS-PP, PICS-SUC, RIS-WISPS, RIS-PICS MUN, CoRe-FW4A_UNDP-VSAT, CoRe-FW4A_Phase 4

**Penetration Formula:**
```
LGU Penetration Rate = (Distinct Municipalities with FW4A / Total Municipalities in tblsdn) × 100%
Barangay Penetration Rate = (Distinct Barangays with FW4A / Total Barangays in tblbrgy) × 100%
```

**Database Tables:** `tblfwfa`, `tbllocality`, `tbltype`, `tblsdn`, `tblbrgy`

---

### Module 7: Letter Requests / Targets
**Estimated Time:** 2 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `LetterRequestController.php` | [ ] |
| Target Controller | `TargetController.php` | [ ] |
| Letters View | `letters/index.blade.php` | [ ] |
| Targets View | `targets/index.blade.php` | [ ] |
| Add/Edit Modal | `letters/add-edit-modal.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `index()` | [ ] | DataTable of letter requests |
| `store()` | [ ] | Create + file upload |
| `update()` | [ ] | Update + file upload |
| `destroy()` | [ ] | Bulk delete + audit log |
| `import()` | [ ] | CSV import |

**Database Tables:** `locationrequests`, `tblactivityphoto`, `cybersecurity_metrics`

---

### Module 8: Tech4Ed
**Estimated Time:** 2-3 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `Tech4edController.php` | [ ] |
| Index View | `tech4ed/index.blade.php` | [ ] |
| Category Views | `tech4ed/center.blade.php`, `school.blade.php`, `nga.blade.php`, `lgu.blade.php`, `private.blade.php` | [ ] |
| Add/Edit Modal | `tech4ed/add-edit-modal.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `index()` | [ ] | Overview with 5 stat boxes |
| `show($category)` | [ ] | Filtered by category |
| `store()` | [ ] | Create (48 fields!) |
| `update()` | [ ] | Update |
| `destroy()` | [ ] | Bulk delete |
| `import()` | [ ] | CSV import |
| `export()` | [ ] | CSV export |

**Tech4Ed Categories:**
1. Center
2. School
3. NGA (National Government Agency)
4. LGU (Local Government Unit)
5. Private

**Security Fix:** Current code has NO `mysqli_real_escape_string` — Laravel's Eloquent fixes this automatically.

**Database Tables:** `tbltech4ed`

---

### Module 9: Planned Activities / Targets
**Estimated Time:** 2-3 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `TargetInitiativeController.php` | [ ] |
| Index View (per project) | `targets/cybersecurity.blade.php`, `elgu.blade.php`, etc. | [ ] |
| Add Modal | `targets/add-modal.blade.php` | [ ] |
| Edit Modal | `targets/edit-modal.blade.php` | [ ] |
| View Modal (Files) | `targets/view-modal.blade.php` | [ ] |

**Critical Business Rule:**
```php
// Dual-insert: If remarks not empty, also create Activity record
if (!empty($request->remarks)) {
    Activity::create([
        // ... all fields from target_initiative
        'indicator' => $request->type, // type becomes indicator
    ]);
}
```

**Status:** [ ] Business rule implemented and tested

**Database Tables:** `targets_initiatives`, `tblactivity`, `tblactivityphoto`

---

### Module 10: Participants
**Estimated Time:** 2 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `ParticipantController.php` | [ ] |
| Cybersecurity View | `participants/cybersecurity.blade.php` | [ ] |
| ILCDB View | `participants/ilcdb.blade.php` | [ ] |
| Category Views | `participants/cyber-*.blade.php` (8 files) | [ ] |
| Add/Edit Modal | `participants/add-edit-modal.blade.php` | [ ] |
| View Modal (Files) | `participants/view-modal.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `index($project)` | [ ] | Project-specific dashboard |
| `store()` | [ ] | Create + file upload |
| `update()` | [ ] | Update |
| `destroy()` | [ ] | Bulk delete + audit log |
| `addImage()` | [ ] | Upload photos |
| `removeImage()` | [ ] | Remove photos |

**Cybersecurity Participant Sub-pages:**
1. `cyber_participant.php` → Total Participants
2. `cyber_male.php` → Male Participants
3. `cyber_female.php` → Female Participants
4. `cyber_awareness.php` → Awareness Programs
5. `cyber_facetoface.php` → Face-to-Face
6. `cyber_virtual.php` → Virtual
7. `cyber_orientation.php` → Orientation
8. `cyber_training.php` → Training

**Database Tables:** `tblparticipant`, `tblactivityphoto`, `tbllogs`

---

### Module 11: Reports
**Estimated Time:** 2-3 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Controller | `ReportController.php` | [ ] |
| Cybersecurity Report View | `reports/cybersecurity.blade.php` | [ ] |
| ILCDB Report View | `reports/ilcdb.blade.php` | [ ] |
| Chart Data Endpoints | `reports/cyber-chart-data.blade.php`, `ilcdb-chart-data.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `cybersecurity()` | [ ] | 5 Morris.js bar charts |
| `ilcdb()` | [ ] | 6 Morris.js bar charts |
| `cyberChartData($year)` | [ ] | JSON for AJAX chart loading |
| `ilcdbChartData($year)` | [ ] | JSON for AJAX chart loading |

**Cybersecurity Charts:**
1. [ ] No. of Cybersecurity Awareness (Face-to-Face)
2. [ ] No. of Individuals Reached (Face-to-Face)
3. [ ] No. of PKI Awareness Campaigns
4. [ ] No. of Issued Digital Certificates
5. [ ] Number of PNPKI Users Training conducted

**ILCDB Charts:**
1. [ ] ICT Proficiency Diagnostic Examination
2. [ ] Diagnostic Examination Examinees
3. [ ] SPARK Technical Training Conducted
4. [ ] SPARK Technical Training Completers
5. [ ] Capacity Development
6. [ ] Training on Digital Transformative Technologies

**Chart Configuration:**
- Bar Colors: `['#00008b', '#00008b']` (navy) or `['#8B0000', '#00008b']` (dark red + navy)
- Plugin: Morris.js + Raphael.js

**Database Tables:** `tblactivity`, `cybersecurity_metrics`

---

### Module 12: Admin, Logs, Backup
**Estimated Time:** 2 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Component | File | Status |
|-----------|------|--------|
| Zone Controller | `ZoneController.php` | [ ] |
| Credential Controller | `CredentialController.php` | [ ] |
| Log Controller | `LogController.php` | [ ] |
| Backup Controller | `BackupController.php` | [ ] |
| Zone View | `admin/zones.blade.php` | [ ] |
| Credential View | `admin/credentials.blade.php` | [ ] |
| Log View | `admin/logs.blade.php` | [ ] |
| Backup View | `admin/backup.blade.php` | [ ] |

**Controller Methods:**
| Method | Status | Notes |
|--------|--------|-------|
| `zones.index()` | [ ] | DataTable of zone accounts |
| `zones.store()` | [ ] | Create with duplicate check |
| `zones.update()` | [ ] | Update with duplicate check |
| `zones.destroy()` | [ ] | Bulk delete |
| `credentials.index()` | [ ] | DataTable of staff accounts |
| `credentials.store()` | [ ] | Create with duplicate check + password hash |
| `credentials.update()` | [ ] | Update with duplicate check + password hash |
| `credentials.destroy()` | [ ] | Bulk delete + audit log |
| `logs.index()` | [ ] | Read-only DataTable |
| `backup.index()` | [ ] | Backup/Restore interface |

**Bugs to Fix:**
- [ ] `credentials/function.php` duplicate `btn_add` block
- [ ] `credentials/function.php` update duplicate check doesn't exclude current record
- [ ] `admin/function.php` line 39: undefined `$txt_edit_busname`

**Replace:** Legacy `backup_restore_class.php` (uses deprecated `mysql_*`) → Laravel DB dump or Spatie package

**Database Tables:** `tblzone`, `tblstaff`, `tbllogs`

---

### Day 3-4 Checklist
- [ ] All 12 controllers created and functional
- [ ] All Blade views rendered correctly
- [ ] All CRUD operations working
- [ ] All file uploads working
- [ ] All import/export working
- [ ] Pass Slip auto-numbering working
- [ ] Pass Slip inventory tracking working
- [ ] Reports/Charts rendering
- [ ] All business logic preserved
- [ ] All bugs from original code fixed

---

## PHASE 4: SECURITY HARDENING

### Day 4-5 Target: ~4-6 hours

---

### Security Fixes Checklist
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Issue | Current State | Laravel Fix | Status |
|-------|---------------|-------------|--------|
| Plaintext passwords | Stored/compared as plain text | `Hash::make()` + `Hash::check()` | [ ] |
| SQL Injection (tech4ed) | No escaping at all | Eloquent parameter binding | [ ] |
| SQL Injection (header.php) | Direct `$_POST` into SQL | Laravel Form Request validation | [ ] |
| No CSRF protection | Zero CSRF tokens | `@csrf` in all Blade forms | [ ] |
| Debug output | `echo $query;` in fwfa_letter | Remove; use `Log::info()` | [ ] |
| Undefined variables | 6+ bugs in log messages | PHP strict mode + proper scoping | [ ] |
| Duplicate logic | credentials has duplicate block | Single handler with validation | [ ] |
| No input validation | Only `mysqli_real_escape_string` | Laravel Form Request classes | [ ] |
| File upload vulnerabilities | Minimal validation | `File::validate()` with MIME + size | [ ] |
| No HTTPS enforcement | HTTP by default | `ForceHttps` middleware | [ ] |
| Session security | Basic `session_start()` | Laravel encrypted sessions | [ ] |

---

### Additional Security Improvements
| Improvement | Status | Notes |
|-------------|--------|-------|
| Rate limiting on login | [ ] | Laravel `throttle` middleware |
| Remember me tokens | [ ] | Laravel `remember` functionality |
| Proper RBAC | [ ] | Replace username checks with permission system |
| Audit logging via Observers | [ ] | Eloquent model observers |
| Secure HTTP headers | [ ] | Custom middleware |

---

### Day 5 Checklist
- [ ] All passwords hashed in database
- [ ] CSRF tokens on all forms
- [ ] No SQL injection vulnerabilities
- [ ] No debug output in production
- [ ] All undefined variable bugs fixed
- [ ] Input validation on all forms
- [ ] File upload validation working
- [ ] HTTPS enforced
- [ ] Rate limiting on login

---

## PHASE 5: TESTING & DEPLOYMENT

### Day 5 Target: ~4-6 hours

---

### Testing Checklist
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

#### Feature Tests
| Test Case | Status | Notes |
|-----------|--------|-------|
| Login as Administrator | [ ] | Full sidebar visible |
| Login as Staff | [ ] | Limited sidebar, no Add/Delete |
| Login with wrong credentials | [ ] | Error message displayed |
| Logout | [ ] | Session destroyed, redirect to login |
| Create activity | [ ] | Audit log entry created |
| Edit activity | [ ] | Changes saved, audit log |
| Delete activity (bulk) | [ ] | Records removed, audit log |
| Import CSV (activities) | [ ] | Records created in database |
| Export CSV (activities) | [ ] | Correct data filtered by project |
| Create inventory item | [ ] | Record created |
| Create pass slip | [ ] | Inventory decremented, slip number auto-generated |
| Process return | [ ] | Status changed to 'returned', inventory restored |
| Delete pass slip (borrowed) | [ ] | Inventory restored |
| Delete pass slip (returned) | [ ] | Inventory NOT restored |
| Create BPLS record | [ ] | Record created |
| Create FW4A record | [ ] | Record created |
| FW4A strategy pivot tables | [ ] | Correct counts |
| FW4A penetration rate | [ ] | Correct calculation |
| Tech4Ed CRUD | [ ] | All 48 fields saved |
| Planned activities dual-insert | [ ] | Both target_initiative and activity created |
| Reports/Charts render | [ ] | Morris.js charts display |
| Backup database | [ ] | SQL file generated |
| Restore database | [ ] | Database restored from file |

#### Unit Tests
| Test Case | Status | Notes |
|-----------|--------|-------|
| Pass slip auto-numbering | [ ] | PS-{YEAR}-{NNNN} increments correctly |
| Inventory decrement | [ ] | Quantity decreases on borrow |
| Inventory restore | [ ] | Quantity increases on return |
| Penetration rate formula | [ ] | Correct percentage calculation |
| Activity export filter | [ ] | Correct project data exported |
| Duplicate username check | [ ] | Prevents duplicates |

#### Browser Tests (Optional - Laravel Dusk)
| Test Case | Status | Notes |
|-----------|--------|-------|
| Full login flow | [ ] | |
| Dashboard loads with stats | [ ] | |
| Activity CRUD flow | [ ] | |
| Pass Slip borrow/return flow | [ ] | |

---

### Deployment Checklist
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

| Task | Status | Notes |
|------|--------|-------|
| Run all migrations on production | [ ] | `php artisan migrate --force` |
| Run password hashing seeder | [ ] | One-time script |
| Seed classification data | [ ] | `php artisan db:seed --class=ClassificationsSeeder` |
| Set `APP_ENV=production` | [ ] | In `.env` |
| Set `APP_DEBUG=false` | [ ] | In `.env` |
| Configure file permissions | [ ] | `storage/` and `public/` writable |
| Set up queue worker | [ ] | If using background jobs |
| Test all 12 modules | [ ] | Full regression test |
| Verify Import/Export | [ ] | Test CSV upload/download |
| Verify Pass Slip print | [ ] | PDF layout correct |
| Verify Reports/Charts | [ ] | Morris.js renders |
| Verify backup/restore | [ ] | Full backup + restore cycle |
| Remove old vanilla PHP files | [ ] | After verification |
| Update DNS/server config | [ ] | Point to Laravel public/ |

---

## BUGS TO FIX DURING MIGRATION

| Bug | Location | Fix | Status |
|-----|----------|-----|--------|
| Undefined `$activityName` | `property_records/function.php:118` | Use `$itemName` | [ ] |
| Undefined `$item_no` | `fwfa_letter/function.php:92` | Use correct variable | [ ] |
| Undefined `$item_no` | `targets/function.php:89` | Use correct variable | [ ] |
| Undefined `$item_no` | `tech4ed/function.php:200` | Use correct variable | [ ] |
| Undefined `$txt_edit_busname` | `admin/function.php:39` | Use `$txt_edit_zone` | [ ] |
| Duplicate `btn_add` block | `credentials/function.php:1-54` | Remove duplicate | [ ] |
| Debug `echo $query` | `fwfa_letter/function.php:27` | Remove | [ ] |
| No SQL escaping | `tech4ed/function.php` | Use Eloquent | [ ] |
| Duplicate username check | `credentials/function.php` update | Exclude current record | [ ] |
| Municipality shows project | `dashboard_data/activities_data.php` | Use `$row['municipality']` | [ ] |

---

## FILE MAPPING: OLD → NEW

| Old File (Vanilla PHP) | New File (Laravel) |
|------------------------|-------------------|
| `index.php` | `routes/web.php` → redirect |
| `login.php` | `resources/views/auth/login.blade.php` |
| `logout.php` | `AuthController@logout` |
| `pages/connection.php` | `.env` database config |
| `pages/header.php` | `resources/views/components/header.blade.php` |
| `pages/sidebar-left.php` | `resources/views/components/sidebar.blade.php` |
| `pages/footer.php` | `resources/views/components/footer.blade.php` |
| `pages/head_css.php` | `resources/views/components/head-css.blade.php` |
| `pages/dashboard/dashboard.php` | `DashboardController@index` + `dashboard/index.blade.php` |
| `pages/activity/cybersecurity.php` | `ActivityController@index('Cybersecurity')` |
| `pages/activity/function.php` | `ActivityController@store/update/destroy` |
| `pages/activity/add_modal.php` | `activities/add-modal.blade.php` |
| `pages/activity/edit_modal.php` | `activities/edit-modal.blade.php` |
| `pages/activity/view_modal.php` | `activities/view-modal.blade.php` |
| `pages/activity/import.php` | `ActivityController@import` |
| `pages/activity/export_*.php` | `ActivityController@export` |
| `pages/property_records/property_records.php` | `InventoryController@index` |
| `pages/property_records/function.php` | `InventoryController@store/update/destroy` |
| `pages/property_records/classification_list.php` | `ClassificationController@index` |
| `pages/pass_slip/pass_slip.php` | `PassSlipController@index` |
| `pages/pass_slip/function.php` | `PassSlipController@store/return/destroy` |
| `pages/pass_slip/print_slip.php` | `PassSlipController@print` |
| `pages/bpls_data/bpls.php` | `BplsController@index` |
| `pages/bpls_data/bpls_monitoring.php` | `BplsController@monitoring` |
| `pages/fw4a_data/fw4a_data.php` | `Fw4aController@index` |
| `pages/fw4a_data/fw4a_strategy.php` | `Fw4aController@strategy` |
| `pages/fw4a_data/fw4a_active.php` | `Fw4aController@active` |
| `pages/fw4a_data/fw4a_inactive.php` | `Fw4aController@inactive` |
| `pages/fw4a_data/fw4a_penetration.php` | `Fw4aController@penetration` |
| `pages/fw4a_data/fw4a_brgy.php` | `Fw4aController@barangay` |
| `pages/fwfa_letter/letters.php` | `LetterRequestController@index` |
| `pages/tech4ed/tech4ed.php` | `Tech4edController@index` |
| `pages/targets/targets.php` | `TargetController@index` |
| `pages/planned_activities/*.php` | `TargetInitiativeController@index` |
| `pages/participant/cybersecurity.php` | `ParticipantController@index('Cybersecurity')` |
| `pages/report/cybersecurity.php` | `ReportController@cybersecurity` |
| `pages/report/ilcdb.php` | `ReportController@ilcdb` |
| `pages/logs/logs.php` | `LogController@index` |
| `pages/backup/backup.php` | `BackupController@index` |
| `pages/admin/admin.php` | `ZoneController@index` |
| `pages/credentials/credentials.php` | `CredentialController@index` |

---

## DAILY PROGRESS LOG

### Day 1: ________ (Date)
**Hours Worked:** ________
**Phase Completed:** ________
**Tasks Completed:**
- 
- 
- 

**Issues Encountered:**
- 
- 

**Notes:**
- 

---

### Day 2: ________ (Date)
**Hours Worked:** ________
**Phase Completed:** ________
**Tasks Completed:**
- 
- 
- 

**Issues Encountered:**
- 
- 

**Notes:**
- 

---

### Day 3: ________ (Date)
**Hours Worked:** ________
**Phase Completed:** ________
**Tasks Completed:**
- 
- 
- 

**Issues Encountered:**
- 
- 

**Notes:**
- 

---

### Day 4: ________ (Date)
**Hours Worked:** ________
**Phase Completed:** ________
**Tasks Completed:**
- 
- 
- 

**Issues Encountered:**
- 
- 

**Notes:**
- 

---

### Day 5: ________ (Date)
**Hours Worked:** ________
**Phase Completed:** ________
**Tasks Completed:**
- 
- 
- 

**Issues Encountered:**
- 
- 

**Notes:**
- 

---

## FINAL VERIFICATION CHECKLIST

### All Modules Working
- [ ] Dashboard (stats + drill-downs)
- [ ] Cybersecurity Activities
- [ ] Cybersecurity Participants
- [ ] Cybersecurity Reports
- [ ] eLGU BPLS Monitoring
- [ ] eLGU BPLS Activities
- [ ] eLGU BPLS Database
- [ ] FreeWifi4All Monitoring
- [ ] FreeWifi4All Strategy
- [ ] FreeWifi4All Letter Requests
- [ ] FreeWifi4All Activities
- [ ] GECS Activities
- [ ] IIDB Activities
- [ ] ILCDB Activities
- [ ] ILCDB Participants
- [ ] ILCDB Tech4Ed
- [ ] ILCDB Reports
- [ ] Procurement
- [ ] Property Records
- [ ] Classification List
- [ ] Purchase Request
- [ ] Pass Slip
- [ ] Planned Activities (all projects)
- [ ] Credentials
- [ ] Logs
- [ ] Backup/Restore

### All Security Fixes Applied
- [ ] Passwords hashed
- [ ] CSRF protection
- [ ] SQL injection prevented
- [ ] Input validation
- [ ] File upload validation
- [ ] No debug output
- [ ] HTTPS enforced

### All Bugs Fixed
- [ ] 10 known bugs from original code

### All Features Preserved
- [ ] Import/Export (CSV)
- [ ] Pass Slip auto-numbering
- [ ] Pass Slip print layout
- [ ] Reports/Charts (Morris.js)
- [ ] Role-based access
- [ ] Audit logging
- [ ] File management

---

## PHASE 6: ADDITIONAL PAGES & FEATURES (Missing from Initial Plan)

### Estimated Time: ~4-6 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

---

### Step 6.1: Missing PHP Pages (15 files)

| File | Purpose | Controller Method | Status |
|------|---------|-------------------|--------|
| `main/index.php` | Public municipality directory page | `PublicController@index` | [ ] |
| `main/about.php` | Static about page | `PublicController@about` | [ ] |
| `main/detailsModal.php` | Barangay detail modal | `PublicController@details` | [ ] |
| `pages/property_records/property.php` | Procurement page | `PropertyController@index` | [ ] |
| `pages/property_records/canvas_form1.php` | Canvas/RFQ form | `PropertyController@canvasForm` | [ ] |
| `pages/fwfa_letter/fw4a_requests.php` | FW4A requests page | `LetterRequestController@requests` | [ ] |
| `pages/fwfa_letter/fw4a_provision.php` | FW4A provision page | `LetterRequestController@provision` | [ ] |
| `pages/fwfa_letter/fw4a_localities.php` | FW4A localities page | `LetterRequestController@localities` | [ ] |
| `pages/fwfa_letter/fw4a_barangays.php` | FW4A barangays page | `LetterRequestController@barangays` | [ ] |
| `pages/admin/login.php` | Zone leader login | `AuthController@zoneLogin` | [ ] |
| `pages/admin/check_username.php` | AJAX username check (tblzone) | `ZoneController@checkUsername` | [ ] |
| `pages/credentials/check_username.php` | AJAX username check (tblstaff) | `CredentialController@checkUsername` | [ ] |
| `pages/targets/targetss.php` | Duplicate of targets.php | Merge into TargetController | [ ] |
| `ajax/dashboard-boxrefresh-demo.php` | Demo AJAX endpoint | Remove (dead code) | [ ] |
| `pages/dashboard/phpinfo.php` | phpinfo() utility | Remove (dev only) | [ ] |

---

### Step 6.2: Additional BPLS Pages (6 files)

| File | Purpose | Controller Method | Status |
|------|---------|-------------------|--------|
| `pages/bpls_data/bpls_1st.php` | 1st District detail | `BplsController@firstDistrict` | [ ] |
| `pages/bpls_data/bpls_2nd.php` | 2nd District detail | `BplsController@secondDistrict` | [ ] |
| `pages/bpls_data/bpls_lgu.php` | LGU engagement detail | `BplsController@lguEngagement` | [ ] |
| `pages/bpls_data/bpls_implem.php` | Implementation detail | `BplsController@implementation` | [ ] |
| `pages/bpls_data/bpls_engage.php` | Engagement detail | `BplsController@engagement` | [ ] |
| `pages/bpls_data/bpls_dict.php` | DICT service detail (27 columns) | `BplsController@dictDetail` | [ ] |

---

### Step 6.3: Additional Tech4Ed Pages (8 files)

| File | Purpose | Controller Method | Status |
|------|---------|-------------------|--------|
| `pages/tech4ed/tech4ed_center.php` | Tech4Ed Centers | `Tech4edController@byCategory('center')` | [ ] |
| `pages/tech4ed/tech4ed_school.php` | Tech4Ed Schools | `Tech4edController@byCategory('school')` | [ ] |
| `pages/tech4ed/tech4ed_nga.php` | Tech4Ed NGA | `Tech4edController@byCategory('nga')` | [ ] |
| `pages/tech4ed/tech4ed_lgu.php` | Tech4Ed LGU | `Tech4edController@byCategory('lgu')` | [ ] |
| `pages/tech4ed/tech4ed_private.php` | Tech4Ed Private | `Tech4edController@byCategory('private')` | [ ] |
| `pages/tech4ed/tech4ed_ris.php` | Tech4Ed RIS | `Tech4edController@byCategory('ris')` | [ ] |
| `pages/tech4ed/tech4ed_operational.php` | Tech4Ed Operational | `Tech4edController@operational` | [ ] |
| `pages/tech4ed/tech4ed_nonoperational.php` | Tech4Ed Non-Operational | `Tech4edController@nonOperational` | [ ] |

---

### Step 6.4: Additional Participant Pages (8 files)

| File | Purpose | Controller Method | Status |
|------|---------|-------------------|--------|
| `pages/participant/cyber_participant.php` | Total Cybersecurity Participants | `ParticipantController@cyberTotal` | [ ] |
| `pages/participant/cyber_male.php` | Male Participants | `ParticipantController@cyberMale` | [ ] |
| `pages/participant/cyber_female.php` | Female Participants | `ParticipantController@cyberFemale` | [ ] |
| `pages/participant/cyber_awareness.php` | Awareness Programs | `ParticipantController@cyberAwareness` | [ ] |
| `pages/participant/cyber_facetoface.php` | Face-to-Face | `ParticipantController@cyberFaceToFace` | [ ] |
| `pages/participant/cyber_virtual.php` | Virtual | `ParticipantController@cyberVirtual` | [ ] |
| `pages/participant/cyber_orientation.php` | Orientation | `ParticipantController@cyberOrientation` | [ ] |
| `pages/participant/cyber_training.php` | Training | `ParticipantController@cyberTraining` | [ ] |

---

### Step 6.5: Additional Activity Data Pages (~15 files)

| File Pattern | Purpose | Status |
|--------------|---------|--------|
| `pages/activity/sectors_data_*.php` | Sector-specific drill-down per project | [ ] |
| `pages/activity/municipalities_data_*.php` | Municipality-specific drill-down per project | [ ] |
| `pages/activity/barangays_data_*.php` | Barangay-specific drill-down per project | [ ] |
| `pages/activity/activities_data_*.php` | Activity-specific drill-down per project | [ ] |

**Note:** These are filter-specific drill-down pages for each of the 6 projects (cybersecurity, elgu, fwfa, gecs, iidb, ilcdb). They follow the same pattern as the main activity pages but with pre-applied filters.

---

### Step 6.6: Zone Leader Login System

**Current state:** Separate login at `pages/admin/login.php` sets `$_SESSION['role'] = "Zone Leader"`

**Laravel implementation:**
- [ ] Add `role:zone` guard or extend existing auth
- [ ] Create zone login view
- [ ] Add zone-specific sidebar navigation
- [ ] Handle session conflict with main login

---

## PHASE 7: JAVASCRIPT MIGRATION

### Estimated Time: ~6-8 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

---

### Step 7.1: Core JS Components to Create

| Component | Current Location | Laravel Implementation | Status |
|-----------|------------------|----------------------|--------|
| `alert.js` notification plugin | `js/alert.js` (219 lines) | Include in `public/js/alert.js` or convert to Blade component | [ ] |
| `updateDateTime()` live clock | Copy-pasted in 70+ pages | Single Blade component `@include('components.clock')` | [ ] |
| Select All checkbox logic | Copy-pasted in 60+ pages | Single Blade component `@include('components.select-all')` | [ ] |
| DataTable initialization | Copy-pasted in 40+ pages | Single Blade component `@include('components.datatable')` | [ ] |
| Select2 initialization | 14 pages | Single Blade component `@include('components.select2')` | [ ] |

---

### Step 7.2: CSV Import Logic (18 pages)

**Current pattern:** Hidden file input + fetch API to `import.php`

| Page | Import Target | Status |
|------|---------------|--------|
| `activity/cybersecurity.php` | `tblactivity` | [ ] |
| `activity/elgu.php` | `tblactivity` | [ ] |
| `activity/fwfa.php` | `tblactivity` | [ ] |
| `activity/gecs.php` | `tblactivity` | [ ] |
| `activity/ilcdb.php` | `tblactivity` | [ ] |
| `activity/iidb.php` | `tblactivity` | [ ] |
| `tech4ed/tech4ed.php` | `tbltech4ed` | [ ] |
| `bpls_data/bpls.php` | `tblbpls` | [ ] |
| `targets/targetss.php` | `targets_initiatives` | [ ] |
| `targets/targets.php` | `cybersecurity_metrics` | [ ] |
| `fw4a_data/fw4a_data.php` | `tblfwfa` | [ ] |
| `fw4a_data/fw4a_database.php` | `tblfwfa` | [ ] |
| `fwfa_letter/letters.php` | `locationrequests` | [ ] |
| `dashboard_data/activities_data.php` | `tblactivity` | [ ] |
| `property_records/property_records.php` | `inventory` | [ ] |
| `participant/ilcdb.php` | `tblparticipant` | [ ] |
| `participant/cybersecurity.php` | `tblparticipant` | [ ] |
| `planned_activities/*.php` | `targets_initiatives` | [ ] |

**Laravel implementation:** Single `ImportController` with CSV parsing via `League\Csv` or Laravel Excel

---

### Step 7.3: CSV Export Logic (17 pages)

**Current pattern:** `window.location.href` redirect with filter params

| Page | Export Script | Filter Params | Status |
|------|---------------|---------------|--------|
| `activity/cybersecurity.php` | `export_cyber.php` | indicator, sector, municipality, barangay | [ ] |
| `activity/elgu.php` | `export_elgu.php` | indicator, sector, municipality, barangay | [ ] |
| `activity/fwfa.php` | `export_fwfa.php` | indicator, sector, municipality, barangay | [ ] |
| `activity/gecs.php` | `export_gecs.php` | indicator, sector, municipality, barangay | [ ] |
| `activity/ilcdb.php` | `export_ilcdb.php` | indicator, sector, municipality, barangay | [ ] |
| `activity/iidb.php` | `export_iidb.php` | indicator, sector, municipality, barangay | [ ] |
| `tech4ed/tech4ed.php` | `exporttech4ed.php` | municipality | [ ] |
| `bpls_data/bpls.php` | `export.php` | system | [ ] |
| `targets/targetss.php` | `exportdata.php` | locality, barangay, type, year | [ ] |
| `targets/targets.php` | `export.php` | locality, barangay, type, year | [ ] |
| `fw4a_data/fw4a_data.php` | `export.php` | locality, barangay, type, strategy | [ ] |
| `fw4a_data/fw4a_database.php` | `export.php` | locality, barangay, type, year | [ ] |
| `fwfa_letter/letters.php` | `export.php` | project, locality, year, status | [ ] |
| `dashboard_data/activities_data.php` | `export.php` | (none) | [ ] |
| `property_records/property_records.php` | `export.php` | ics, project, year, remarks | [ ] |
| `participant/ilcdb.php` | `exportilcdb.php` | sector | [ ] |
| `participant/cybersecurity.php` | `exportcyber.php` | project, mode, indicator, sex | [ ] |

**Laravel implementation:** Single `ExportController` with CSV generation

---

### Step 7.4: Pass Slip Complex JavaScript

| Feature | Current Location | Laravel Implementation | Status |
|---------|------------------|----------------------|--------|
| Dynamic item row add/remove | `pass_slip/add_modal.php` | Livewire component or Alpine.js | [ ] |
| Auto-fill from inventory select | `pass_slip/add_modal.php` | Livewire `wire:model` or AJAX | [ ] |
| Pass slip view modal (iframe) | `pass_slip/pass_slip.php` | Blade modal with embedded print view | [ ] |
| Return modal with AJAX loading | `pass_slip/pass_slip.php` | Livewire component | [ ] |
| Print slip (iframe window.print) | `pass_slip/pass_slip.php` | New route + print-optimized Blade | [ ] |
| Pass slip history modal | `property_records/property_records.php` | AJAX endpoint + Blade modal | [ ] |
| PHP-to-JS data injection (`passSlipData`) | `pass_slip/pass_slip.php` | `@json` directive or Livewire | [ ] |

---

### Step 7.5: AJAX Username Check

| File | Table | Status |
|------|-------|--------|
| `pages/admin/add_modal.php` | `tblzone` | [ ] |
| `pages/credentials/add_modal.php` | `tblstaff` | [ ] |

**Laravel implementation:** AJAX route returning JSON `{ available: true/false }`

---

### Step 7.6: Morris.js Chart Rendering

| File | Charts | Status |
|------|--------|--------|
| `pages/report/cyber_bar-chart.php` | 5 bar charts | [ ] |
| `pages/report/ilcdb_bar-chart.php` | 6 bar charts | [ ] |

**Laravel implementation:** JSON data endpoints + JavaScript chart initialization in Blade

---

### Step 7.7: Cascading Filter Logic

**Current pattern:** Server-side via `onchange="this.form.submit()"` (full page reload)

| Page | Filters | Status |
|------|---------|--------|
| `property_records/property_records.php` | project → ics → year → remarks | [ ] |
| `pass_slip/pass_slip.php` | status, date_from, date_to, search_borrower | [ ] |
| `participant/cybersecurity.php` | sector, mode, indicator, sex | [ ] |
| `tech4ed/tech4ed.php` | category, municipality, barangay, year | [ ] |
| `dashboard_data/participants_data.php` | mode | [ ] |

**Laravel implementation:** Keep server-side submission (simplest) or upgrade to Livewire/AJAX

---

## PHASE 8: CSS & ASSET MIGRATION

### Estimated Time: ~4-5 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

---

### Step 8.1: Consolidate Inline Styles (100+ files)

**Current state:** 100+ PHP files have embedded `<style>` blocks

| Style Pattern | Files Affected | Laravel Implementation | Status |
|---------------|----------------|----------------------|--------|
| Info-box icon overrides | 15+ main pages | `public/css/custom/info-box.css` | [ ] |
| DataTable export/print styles | 30+ activity pages | `public/css/custom/datatable-export.css` | [ ] |
| Login page styles | 2 files | `resources/views/auth/login.blade.php` inline or `public/css/auth.css` | [ ] |
| Notification toast styles | 4 files | `public/css/custom/notifications.css` | [ ] |
| Print slip styles (230+ lines) | 1 file | `public/css/custom/print-slip.css` | [ ] |
| Canvas form styles | 1 file | `public/css/custom/canvas-form.css` | [ ] |
| Strategy table styles | 1 file | `public/css/custom/strategy-table.css` | [ ] |
| Responsive table styles | 1 file | `public/css/custom/responsive-table.css` | [ ] |

---

### Step 8.2: Image Assets to Migrate

#### Root Logos (16 files)
| File | Purpose | Status |
|------|---------|--------|
| `img/dict1.jpeg` | Login background image | [ ] |
| `img/logofinal.png` | Primary logo (login, pass slip) | [ ] |
| `img/logo.png` | Logo variant (admin login) | [ ] |
| `img/logo 1.png` | Logo variant | [ ] |
| `img/logo1.png` | Logo variant (main/public navbar) | [ ] |
| `img/dream.png` | DREAMS program icon | [ ] |
| `img/elgu.png` | eLGU program icon | [ ] |
| `img/free.png` | FreeWifi program icon | [ ] |
| `img/gecs.png` | GECS program icon | [ ] |
| `img/govnet.png` | GovNet program icon | [ ] |
| `img/iid.png` | IIDB program icon | [ ] |
| `img/iidb.png` | IIDB program icon | [ ] |
| `img/ilc.png` | ILCDB program icon | [ ] |
| `img/ilcdb.png` | ILCDB program icon | [ ] |
| `img/lgu.png` | LGU icon | [ ] |
| `img/wifi.png` | WiFi icon | [ ] |

#### Dashboard Stat Icons (17 files)
| Directory | Files | Status |
|-----------|-------|--------|
| `pages/dashboard/icons/` | 9 files (dict_logo, activities, participants, sectors, agencies, district_1/2, municipalities, barangays) | [ ] |
| `pages/dashboard_data/icons/` | 8 files (total_activities, total_participants, total_sectors, total_agencies, district_1/2, total_municipalities, total_barangays) | [ ] |

#### Activity Icons (19 files)
| Directory | Files | Status |
|-----------|-------|--------|
| `pages/activity/img/icons/` | 12 files (activity, barangay, completers, female, male, municipality, sector, targets) | [ ] |
| `pages/activity/img/logo/` | 7 files (cybersecurity, dict, elgu, fw4a, gecs, iidb, ilcdb) | [ ] |

#### Module-Specific Icons (68 files)
| Directory | Files | Status |
|-----------|-------|--------|
| `pages/bpls_data/icons/` | 13 files | [ ] |
| `pages/fw4a_data/icons/` | 9 files | [ ] |
| `pages/fwfa_letter/icons/` | 9 files | [ ] |
| `pages/participant/icons/` | 18 files | [ ] |
| `pages/tech4ed/icons/` | 18 files | [ ] |
| `pages/planned_activities/icons/` | 1 file | [ ] |
| `pages/property_records/img/cyber/` | 20 files | [ ] |

**Total: 106+ custom icon images**

---

### Step 8.3: Font Files (50 files)

| Font | Files | Status |
|------|-------|--------|
| Open Sans (300, 300italic, 400, 400italic, 600, 600italic) | 30 files (.eot, .svg, .ttf, .woff, .woff2) | [ ] |
| Kaushan Script | 5 files | [ ] |
| Font Awesome | 6 files (.eot, .svg, .ttf, .woff, .woff2, .otf) | [ ] |
| Bootstrap Glyphicons | 5 files | [ ] |
| Ionicons | 4 files | [ ] |

**Recommendation:** Switch to CDN (Google Fonts, Font Awesome CDN) to reduce bundle size

---

### Step 8.4: PDF Generation Libraries

| Library | Current Location | Laravel Package | Status |
|---------|------------------|-----------------|--------|
| TCPDF | `include/` directory (10+ files) | `barryvdh/laravel-dompdf` or keep TCPDF | [ ] |
| JPGraph | `pages/jgraph/` (8 files) | Remove (barcode-only, use `milon/barcode` package) | [ ] |

---

### Step 8.5: Vendor JS/CSS to Migrate

| Library | Current Location | Laravel Implementation | Status |
|---------|------------------|----------------------|--------|
| jQuery 1.12.3 (local) | `js/jquery-1.12.3.js` | Remove (use CDN jQuery 3.x) | [ ] |
| jQuery 3.5.1 (CDN) | `head_css.php` | Keep CDN or install via npm | [ ] |
| Bootstrap 3 | `css/bootstrap.min.css` + `js/bootstrap.min.js` | Keep or upgrade to Bootstrap 5 | [ ] |
| DataTables | `js/jquery.dataTables.min.js` + plugins | CDN or npm | [ ] |
| Select2 | `js/select2.full.js` + `css/select2.css` | CDN or npm | [ ] |
| Morris.js + Raphael | `js/morris/` + CDN | CDN | [ ] |
| SlimScroll | `js/plugins/slimScroll/` | CDN or remove (modern CSS) | [ ] |
| AdminLTE | `js/AdminLTE/app.js` + `css/AdminLTE.css` | Keep or upgrade to AdminLTE 3.x | [ ] |
| CKEditor | `js/plugins/ckeditor/` (entire directory) | CDN | [ ] |
| jQuery UI | `js/jquery-ui-1.10.3.js` | CDN (if still needed) | [ ] |

---

## PHASE 9: DATA MIGRATION

### Estimated Time: ~2-3 hours
**Status:** [ ] Not Started | [ ] In Progress | [ ] Completed

---

### Step 9.1: Password Hashing Migration

**Current state:** Plaintext passwords in `tbluser`, `tblstaff`, `tblzone`

| Task | Status | Notes |
|------|--------|-------|
| Create migration script to hash existing passwords | [ ] | `Hash::make($plaintext)` |
| Run script on `tbluser` table | [ ] | |
| Run script on `tblstaff` table | [ ] | |
| Run script on `tblzone` table | [ ] | |
| Verify login still works after hashing | [ ] | |

---

### Step 9.2: User-Uploaded Files Migration

**Current state:** Files stored in `pages/*/photo/` directories

| Directory | File Count | Status |
|-----------|------------|--------|
| `pages/activity/photo/` | TBD | [ ] |
| `pages/targets/photo/` | TBD | [ ] |
| `pages/tech4ed/photo/` | TBD | [ ] |
| `pages/participant/photo/` | TBD | [ ] |
| `pages/bpls_data/photo/` | TBD | [ ] |
| `pages/fwfa_letter/photo/` | TBD | [ ] |
| `pages/planned_activities/photo/` | TBD | [ ] |
| `pages/property_records/photo/` | TBD | [ ] |

**Laravel implementation:** Move to `storage/app/public/` with symbolic link

---

### Step 9.3: Database Schema Adjustments

| Adjustment | Reason | Status |
|------------|--------|--------|
| Add `remember_token` to `tbluser` | Laravel auth "remember me" | [ ] |
| Add `remember_token` to `tblstaff` | Laravel auth "remember me" | [ ] |
| Add `email_verified_at` to `tbluser` | Laravel email verification (optional) | [ ] |
| Add `password` column rename if needed | Ensure column names match Laravel conventions | [ ] |

---

## PHASE 10: BROKEN SIDEBAR LINK FIX

### Status: [ ] Not Started | [ ] In Progress | [ ] Completed

**Issue:** Sidebar links to `property_items/property.php` but directory doesn't exist

| Current Link | Actual Location | Fix | Status |
|--------------|-----------------|-----|--------|
| `../property_items/property.php` | `pages/property_records/property.php` | Update sidebar route | [ ] |

---

## UPDATED PROJECT STATISTICS

| Metric | Original Count | After Audit |
|--------|----------------|-------------|
| Custom PHP Files | ~120 | ~155 (including 15 missing files) |
| Database Tables | 22+ | 22+ |
| Functional Modules | 12+ | 15+ (including Public, Zone Login, FW4A sub-pages) |
| Custom JS Logic Categories | 0 | 17 categories |
| Custom Icon Images | 0 | 106+ |
| Inline Style Blocks | 0 | 100+ |
| Font Files | 0 | 50 |
| PDF Libraries | 0 | 2 (TCPDF, JPGraph) |
| Import/Export Scripts | 0 | 35 (18 import + 17 export) |

---

**Document Created:** ________
**Last Updated:** ________
**Total Progress:** ___/100%
