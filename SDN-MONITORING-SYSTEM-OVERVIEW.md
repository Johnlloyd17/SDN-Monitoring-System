# SDN Monitoring System -- Project Overview

> **Purpose:** This document provides a complete, factual overview of the current state of the DICT-SDN Activity Management and Monitoring System for external review. It is intended to give another AI assistant sufficient context to design an improved Dashboard / infographic overview page.

**Office:** DICT Surigao del Norte Provincial Office (Ferdinand M. Ortiz St., Brgy. Washington, Surigao City)
**GitHub:** https://github.com/Johnlloyd17/SDN-Monitoring-System

---

## 1. PROJECT ARCHITECTURE SUMMARY

### 1.1 Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.2+ (procedural style, no framework) |
| Database Driver | `mysqli_*` functions |
| Database Engine | MySQL/MariaDB 10.4.32 |
| Database Name | `dict_proj` |
| Frontend Framework | AdminLTE (Bootstrap 3 skin, `skin-black`) |
| jQuery | 1.12.3 / 3.5.1 (mixed CDN versions) |
| DataTables | jQuery DataTables plugin (AJAX-driven, server-side) |
| Charts | Morris.js (donut, bar), Chart.js (doughnut, bar, line), JpGraph (PNG export) |
| Maps | Leaflet.js 1.9.4 + leaflet.heat 0.2.0 (OpenStreetMap tiles) |
| Other JS | Select2, Toastr.js |
| PDF/Barcode | TCPDF, JpGraph |
| Auth | Custom session-based (`$_SESSION['role']`, `$_SESSION['staff']`) |

### 1.2 Folder Structure

```
SDN Monitoring System/
|-- index.php                      # Redirects to login.php
|-- login.php                      # Authentication page
|-- logout.php
|-- deleteModal.php                # Shared delete-confirmation modal
|-- pages/
|   |-- connection.php             # DB connection ($con)
|   |-- head_css.php               # Global CSS/JS includes (Bootstrap, AdminLTE, Leaflet, etc.)
|   |-- header.php                 # Top navbar (DICT Surigao del Norte branding)
|   |-- sidebar-left.php           # Role-based navigation sidebar
|   |-- footer.php                 # Copyright footer + scripts include
|   |-- scripts.php                # jQuery/DataTables/AdminLTE/toastr + toast helpers
|   |-- activity/                  # Activity tracking pages (per program)
|   |-- participant/               # Participant tracking pages
|   |-- report/                    # Reports/Graphs pages (Actual vs Targets)
|   |-- planned_activities/        # Targets & Initiatives pages
|   |-- bpls_data/                 # eLGU BPLS data pages
|   |-- fw4a_data/                 # FreeWifi4All monitoring/data pages
|   |-- fwfa_letter/               # FreeWifi4All letter requests
|   |-- tech4ed/                   # Tech4Ed DTC centers pages
|   |-- property_records/          # Inventory management + Pass Slip
|   |-- property_items/            # Procurement tracking
|   |-- pass_slip/                 # Office equipment pass slips + ICS
|   |-- bills_monitoring/          # Bills monitoring
|   |-- letters_monitoring/        # Letters monitoring
|   |-- credentials/               # Staff account management (admin only)
|   |-- logs/                      # Activity logs viewer (admin only)
|   |-- dashboard/                 # Main dashboard page
|   |-- dashboard_data/            # Drill-down data pages for dashboard stat cards
|   |-- planned_activities/        # Planned activities / targets pages
|   |-- targets/                   # FW4A monitoring targets
|   |-- backup/                    # Database backup/restore (legacy, hardcoded to wrong DB)
|   |-- admin/                     # Zone Leader management (legacy, unused)
|-- ajax/                          # 30+ AJAX endpoints (data loading, CRUD, filters)
|-- db/                            # SQL dump + migration scripts
|-- migrations/                    # PHP migration scripts (001-009)
|-- css/ js/ fonts/ img/ less/     # Static assets
|-- include/                       # TCPDF support files
|-- main/                          # Public landing page (municipal listing)
|-- pdfs/ screenshots/ uploads/    # Uploaded files
```

### 1.3 Page Template Pattern

Every module page follows this standard structure:

```php
<?php session_start();
if (!isset($_SESSION['role'])) { header("Location: ../../login.php"); exit(); }
else { ob_start(); include('../head_css.php'); } ?>
<body class="skin-black">
  <?php include "../connection.php"; include('../header.php'); ?>
  <div class="wrapper row-offcanvas row-offcanvas-left">
    <?php include('../sidebar-left.php'); ?>
    <aside class="right-side">
      <section class="content-header">...logo + title + live date-time...</section>
      <section class="content">
        ...info-box stats -> filter row -> DataTables table -> toolbar...
      </section>
    </aside></div>
<?php include "../scripts.php"; ?>
```

### 1.4 Sidebar Menu Structure

| # | Menu Item | Sub-Items | Access |
|---|-----------|-----------|--------|
| 1 | Dashboard | (single link) | All |
| 2 | Cybersecurity | Activities Conducted, Activity Participants, Reports/Graphs | All |
| 3 | eLGU BPLS | Monitoring Status, Activities Conducted, Database | All |
| 4 | FreeWifi4All | Monitoring, Strategy, Letter Requests, Activities Conducted | All |
| 5 | GECS | Activities Conducted | All |
| 6 | GovNet | **EMPTY -- placeholder only** | All |
| 7 | IIDB | Activities Conducted | All |
| 8 | ILCDB | Activities Conducted, Activity Participants, Tech4Ed DTC, Reports/Graphs | All |
| 9 | Property Management | Inventory Records, Office Equipment Pass Slip | All |
| 10 | Procurement | (single link) | All |
| 11 | Purchase Request | (single link) -- **Page Under Maintenance** | All |
| 12 | Bills Monitoring | (single link) | All |
| 13 | Letters Monitoring | (single link) | All |
| 14 | Credentials | (single link) | **Administrator only** |
| 15 | Logs | (single link) | **Administrator only** |

---

## 2. DATABASE SCHEMA

### 2.1 All Tables

The database `dict_proj` contains **34 tables** (31 from main SQL dump + 3 from migrations). Two backup tables exist but are not actively used.

### 2.2 Table Definitions

---

#### `bills_monitoring`
Tracks utility bills (Water, Internet, Electricity) for the SDN office.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK, AUTO_INCREMENT |
| date_received | date DEFAULT NULL | |
| type_of_billing | enum('Water Bill','Internet Bill','Electricity Bill') NOT NULL | Default: 'Water Bill' |
| link_to_file | text DEFAULT NULL | |
| amount | double DEFAULT NULL | |
| location_office | enum('SDN Provincial Office','SDN Hill Relay Station') DEFAULT NULL | |
| due_date | date DEFAULT NULL | |
| disconnection_date | date DEFAULT NULL | |
| status | tinyint(1) NOT NULL DEFAULT 0 | 1=Paid, 0=Unpaid |
| date_paid | date DEFAULT NULL | |
| remarks | text DEFAULT NULL | |
| link_to_or | text DEFAULT NULL | Official Receipt link |
| created_at | timestamp NOT NULL DEFAULT current_timestamp() | |

---

#### `bills_monitoring_backup_005`
Backup of bills_monitoring before migration 005. Different schema (status was enum 'Pending'/'Paid'/'Overdue'/'Disconnected'). **Not actively used.**

---

#### `classifications`
Inventory item classification categories (Computer Systems, Peripherals, Printers, etc.).

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| category | varchar(255) NOT NULL | e.g. 'Computer Systems', 'Printers' |
| sub_item | varchar(255) NOT NULL | e.g. 'Desktop PCs', 'Laser Printer' |
| status | enum('active','inactive') DEFAULT 'active' | |
| date_created | datetime DEFAULT current_timestamp() | |

---

#### `cybersecurity_metrics`
Stores annual targets for cybersecurity and ILCDB performance metrics.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| category | varchar(255) NOT NULL | e.g. 'Cybersecurity Programs', 'PNPKI' |
| subcategory | varchar(255) NOT NULL | Specific metric description |
| year | int(11) NOT NULL | e.g. 2024, 2025 |
| target | int(11) DEFAULT NULL | Numeric target value |

---

#### `inventory`
Physical inventory of equipment and supplies across all projects.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| project | varchar(255) DEFAULT NULL | e.g. 'FreeWifi4All' |
| item | varchar(255) DEFAULT NULL | Item number |
| classification | varchar(255) DEFAULT NULL | e.g. 'Satellite', 'Access Point', 'COMBOX' |
| item_type | enum('equipment','consumable') DEFAULT 'equipment' | |
| quantity | int(11) DEFAULT NULL | |
| unit | varchar(50) DEFAULT NULL | |
| description | text DEFAULT NULL | Model/specs |
| received | varchar(255) DEFAULT NULL | Received from |
| property | varchar(255) DEFAULT NULL | Property number |
| ics | varchar(255) DEFAULT NULL | ICS/PAR number |
| serial | varchar(255) DEFAULT NULL | Serial number |
| date | date DEFAULT NULL | Date acquired |
| officer | varchar(255) DEFAULT NULL | Accountable officer |
| cost | text DEFAULT NULL | **Stored as comma-formatted string, not numeric** |
| life | int(11) DEFAULT NULL | Estimated useful life |
| transferred | varchar(255) DEFAULT NULL | Transferred to location |
| remarks | text DEFAULT NULL | |
| status | enum('Available','For Deployment','Deployed','Temporary Deployed','Defective','Replaced') DEFAULT 'Available' | |

---

#### `letters_monitoring`
Tracks incoming/outgoing office correspondence.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| date | date DEFAULT NULL | |
| type | enum('Incoming','Outgoing') NOT NULL | Default: 'Incoming' |
| subject | varchar(255) NOT NULL | |
| fw4a | enum('Request','Provision') DEFAULT NULL | FreeWifi4All classification |
| link_incoming | text DEFAULT NULL | |
| for_response | enum('Y','N') DEFAULT NULL | |
| date_responded | date DEFAULT NULL | |
| link_outgoing | text DEFAULT NULL | |
| responsible_person | varchar(255) DEFAULT NULL | |
| who_attended | text DEFAULT NULL | |
| remarks | text DEFAULT NULL | |
| post_activity_report | text DEFAULT NULL | |
| created_at | timestamp NOT NULL DEFAULT current_timestamp() | |

---

#### `locationrequests`
FreeWifi4All letter request status tracking.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| locality | varchar(100) DEFAULT NULL | |
| barangay | varchar(100) DEFAULT NULL | |
| district | int(11) NOT NULL | |
| location | varchar(150) DEFAULT NULL | |
| date | text DEFAULT NULL | Stored as text, not date |
| year | varchar(100) DEFAULT NULL | |
| type | varchar(50) DEFAULT NULL | |
| status | varchar(50) DEFAULT NULL | |
| accomplished | varchar(100) DEFAULT NULL | |
| remarks | text DEFAULT NULL | |

---

#### `pass_slip`
Office equipment borrowing/pass slip records.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| pass_slip_no | varchar(50) NOT NULL | |
| inventory_id | int(11) NOT NULL | FK to inventory.id |
| item_description | varchar(255) NOT NULL | |
| qty | int(11) NOT NULL | |
| unit | varchar(50) NOT NULL | |
| serial_no | varchar(255) DEFAULT NULL | |
| pullout_date | date NOT NULL | |
| requested_by_out | varchar(255) NOT NULL | |
| inspected_by_out | varchar(255) NOT NULL | |
| approved_by_out | varchar(255) NOT NULL | |
| return_date | date DEFAULT NULL | |
| requested_by_return | varchar(255) DEFAULT NULL | |
| inspected_by_return | varchar(255) DEFAULT NULL | |
| approved_by_return | varchar(255) DEFAULT NULL | |
| status | enum('borrowed','returned','overdue') DEFAULT 'borrowed' | |
| purpose | text DEFAULT NULL | |
| condition_out | varchar(100) DEFAULT NULL | |
| condition_return | varchar(100) DEFAULT NULL | |
| remarks | text DEFAULT NULL | |
| created_by | varchar(255) DEFAULT NULL | |
| created_at | timestamp NOT NULL DEFAULT current_timestamp() | |

---

#### `pass_slip_attachments`
Uploaded scanned copies for pass slips.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| pass_slip_no | varchar(50) NOT NULL | FK to pass_slip.pass_slip_no |
| filename | varchar(255) NOT NULL | |
| uploaded_by | varchar(255) DEFAULT NULL | |
| uploaded_at | timestamp NOT NULL DEFAULT current_timestamp() | |

---

#### `procurement`
Legacy product code/description reference list (separate from procurement_tracking).

| Column | Data Type | Notes |
|--------|-----------|-------|
| product_code | varchar(15) DEFAULT NULL | |
| description | varchar(50) DEFAULT NULL | |
| uom | varchar(6) DEFAULT NULL | Unit of measurement |
| unit_cost | varchar(9) DEFAULT NULL | Stored as string |
| qty | varchar(8) DEFAULT NULL | Stored as string |
| remarks | varchar(44) DEFAULT NULL | |

---

#### `procurement_tracking`
Active procurement records tracking purchase requests through payment.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| pr_no | varchar(255) DEFAULT NULL | Purchase Request number |
| activity_id | enum(80+ values) DEFAULT NULL | Codes like 'GOVNET-0001', 'FW4A-0001', 'CSB-0001', etc. |
| activity_name | varchar(255) DEFAULT NULL | |
| link_to_file | text DEFAULT NULL | |
| project_fund_source | varchar(255) DEFAULT NULL | |
| type_of_items_procured | enum('Meals','Fuel (Diesel)','Office Supplies','Plaque','Service') DEFAULT NULL | |
| amount | decimal(18,2) DEFAULT NULL | |
| name_of_supplier | varchar(255) DEFAULT NULL | |
| jo_po | varchar(255) DEFAULT NULL | Job Order / Purchase Order |
| link_to_attachments | text DEFAULT NULL | |
| personnel_in_charge | varchar(255) DEFAULT NULL | |
| date_forwarded_to_ro | date DEFAULT NULL | |
| transmittal_report | varchar(255) DEFAULT NULL | |
| payment_status | varchar(255) DEFAULT NULL | 'Paid', 'Pending', 'Partial', 'Cancelled' |
| remarks | text DEFAULT NULL | |
| date_created | datetime DEFAULT current_timestamp() | |

---

#### `targets_initiatives`
Planned activities / targets for each program.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| start | date NOT NULL | |
| end | date NOT NULL | |
| project | varchar(225) NOT NULL | |
| subproject | varchar(225) NOT NULL | |
| indicator | varchar(100) NOT NULL | |
| activity | varchar(225) NOT NULL | |
| training | varchar(225) NOT NULL | |
| municipality | varchar(225) NOT NULL | |
| district | varchar(50) NOT NULL | |
| barangay | varchar(100) NOT NULL | |
| agency | varchar(100) NOT NULL | |
| mode | varchar(225) NOT NULL | |
| sector | varchar(225) NOT NULL | |
| person | varchar(225) NOT NULL | |
| resource | varchar(225) NOT NULL | |
| participants | int(11) NOT NULL | |
| completers | int(11) NOT NULL | |
| male | text NOT NULL | |
| female | text NOT NULL | |
| approved | varchar(225) NOT NULL | |
| mov | varchar(225) NOT NULL | Means of Verification |
| remarks | varchar(225) NOT NULL | |
| type | varchar(100) NOT NULL | |

---

#### `tblactivity`
Core activity records across all programs (Cybersecurity, eLGU, FW4A, IIDB, ILCDB, GECS, GovNet).

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| start | date NOT NULL | |
| end | date NOT NULL | |
| project | varchar(225) NOT NULL | Program name |
| subproject | varchar(225) NOT NULL | |
| activity | varchar(225) NOT NULL | |
| indicator | varchar(100) NOT NULL | |
| training | varchar(225) NOT NULL | |
| municipality | varchar(225) NOT NULL | |
| district | varchar(50) NOT NULL | 'District 1 (Siargao)' or 'District 2 (Mainland)' |
| barangay | varchar(100) NOT NULL | |
| agency | varchar(100) NOT NULL | |
| mode | varchar(225) NOT NULL | Delivery mode |
| sector | varchar(225) NOT NULL | |
| person | varchar(225) NOT NULL | Person in charge |
| resource | varchar(225) NOT NULL | Resource speaker |
| participants | int(11) NOT NULL | |
| completers | int(11) NOT NULL | |
| male | text NOT NULL | |
| female | text NOT NULL | |
| approved | varchar(225) NOT NULL | |
| mov | varchar(225) NOT NULL | Means of Verification |
| remarks | varchar(225) NOT NULL | |

---

#### `tblactivityphoto`
Activity photo attachments.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| activityid | int(11) NOT NULL | FK to tblactivity.id |
| filename | text NOT NULL | |

---

#### `tblbpls`
eLGU Business Permit and Licensing System database (LGU-by-LGU records).

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| province | varchar(100) DEFAULT NULL | |
| district | varchar(100) DEFAULT NULL | |
| municipality | varchar(100) DEFAULT NULL | |
| lgu | varchar(100) DEFAULT NULL | LGU name |
| class | varchar(50) DEFAULT NULL | |
| system | varchar(100) DEFAULT NULL | System provider |
| action | text DEFAULT NULL | |
| businessyn | char(1) DEFAULT NULL | Y/N for Business Permit |
| businessstatus | varchar(100) DEFAULT NULL | |
| barangayyn | char(1) DEFAULT NULL | Y/N for Barangay Clearance |
| barangaystatus | varchar(100) DEFAULT NULL | |
| buildingyn | char(1) DEFAULT NULL | Y/N for Building Permit |
| buildingstatus | varchar(100) DEFAULT NULL | |
| workingyn | char(1) DEFAULT NULL | Y/N for Working Permit |
| workingstatus | varchar(100) DEFAULT NULL | |
| bfpyn | char(1) DEFAULT NULL | Y/N for BFP |
| bplyn | char(1) DEFAULT NULL | Y/N for BPLS |
| bplstatus | varchar(100) DEFAULT NULL | |
| ecedulayn | char(1) DEFAULT NULL | Y/N for eCedula |
| ecedulastatus | varchar(100) DEFAULT NULL | |
| elcryn | char(1) DEFAULT NULL | Y/N for eLCR |
| elcrstatus | varchar(100) DEFAULT NULL | |
| enewsyn | char(1) DEFAULT NULL | Y/N for eNEWS |
| enewsstatus | varchar(100) DEFAULT NULL | |
| remark | varchar(100) DEFAULT NULL | |

---

#### `tblbplsmonitoring`
eLGU BPLS LGU engagement status (training/implementation tracking).

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| municipality | varchar(225) NOT NULL | |
| elguone | int(11) NOT NULL | 1st District count |
| elgutwo | int(11) NOT NULL | 2nd District count |
| documentary | int(11) NOT NULL | Documentary requirements |
| manual | int(11) NOT NULL | Manual records |
| total | int(11) NOT NULL | |

---

#### `tblbrgy`
Barangay reference list (used for dropdowns).

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| municipality | varchar(100) NOT NULL | |
| barangay | varchar(100) NOT NULL | |

---

#### `tblfwfa`
FreeWifi4All access point deployment records.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| item_no | int(11) DEFAULT NULL | |
| locality | varchar(255) DEFAULT NULL | |
| barangay | varchar(255) DEFAULT NULL | |
| district | varchar(255) DEFAULT NULL | |
| transport_location | varchar(255) DEFAULT NULL | |
| transport_type | varchar(255) DEFAULT NULL | |
| site_locations | varchar(255) DEFAULT NULL | |
| transfer_new_locations | varchar(255) DEFAULT NULL | |
| site_type | varchar(255) DEFAULT NULL | |
| date_of_activation | date DEFAULT NULL | |
| current_date_of_acceptance | date DEFAULT NULL | |
| latitude | decimal(10,7) DEFAULT NULL | For map heatmap |
| longitude | decimal(10,7) DEFAULT NULL | For map heatmap |
| procurement_initiative | enum('Centrally Procured','Regional Procured') DEFAULT NULL | |
| installation_type | enum('Region Initiated','Manage Service') DEFAULT NULL | |
| uat | tinyint(1) DEFAULT NULL | User Acceptance Testing |
| conforme | tinyint(1) DEFAULT NULL | |
| site_code | varchar(255) DEFAULT NULL | |
| nationwide_id | varchar(255) DEFAULT NULL | |
| strategy | varchar(255) DEFAULT NULL | |
| status | enum('Active','Inactive','Ongoing','Assist','Terminated','Deactivated','Ongoing Acceptance','For Installation','For Transfer') DEFAULT NULL | |
| link_type | varchar(255) DEFAULT NULL | |
| replacement_form_file | text DEFAULT NULL | |
| conforme_file | text DEFAULT NULL | |
| uat_file | text DEFAULT NULL | |
| additional_uat | text DEFAULT NULL | |
| site_coordinator_name | varchar(255) DEFAULT NULL | |
| contact_details | varchar(255) DEFAULT NULL | |
| remarks | text DEFAULT NULL | |

---

#### `tblfwfa_backup` / `tblfwfa_preimport_backup`
Backup tables with same schema as tblfwfa. Created by migration 006. **Not actively queried.**

---

#### `tbllocality`
Locality summary data (municipality-level counts by type).

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| locality | varchar(255) NOT NULL | |
| mun | int(11) DEFAULT 0 | Municipal |
| pp | int(11) DEFAULT 0 | Public Place |
| suc | int(11) DEFAULT 0 | SUC |
| wisps | int(11) DEFAULT 0 | WISPs |
| vsat | int(11) DEFAULT 0 | VSAT |
| phase | int(11) DEFAULT 0 | |

---

#### `tbllogs`
System activity audit logs.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| user | varchar(50) NOT NULL | |
| logdate | datetime NOT NULL | |
| action | text NOT NULL | |

---

#### `tblmunicipal`
Municipality/City reference data with demographics.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| name | varchar(100) NOT NULL | |
| type | varchar(20) NOT NULL | e.g. 'Municipality', 'City' |
| population_2020 | varchar(225) DEFAULT NULL | |
| population_2015 | varchar(225) DEFAULT NULL | |
| growth_rate | varchar(225) DEFAULT NULL | |
| area_km2 | varchar(225) DEFAULT NULL | |
| density_2020 | varchar(225) DEFAULT NULL | |
| brgy_count | int(11) DEFAULT NULL | |
| official_link | varchar(225) DEFAULT NULL | |
| lgu_profile_link | varchar(225) DEFAULT NULL | |

---

#### `tblparticipant`
Activity participant records.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| start | date NOT NULL | |
| end | date NOT NULL | |
| activity | varchar(225) NOT NULL | |
| indicator | varchar(100) NOT NULL | |
| fullname | varchar(100) NOT NULL | |
| sex | varchar(100) NOT NULL | |
| contact | varchar(100) NOT NULL | |
| email | varchar(100) NOT NULL | |
| mode | varchar(100) NOT NULL | |
| agency | varchar(100) NOT NULL | |
| sector | varchar(225) NOT NULL | |
| project | varchar(225) NOT NULL | |
| person | varchar(225) NOT NULL | |
| remarks | varchar(225) NOT NULL | |

---

#### `tblproject`
Pre-computed activity counts per municipality per project.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| municity | varchar(225) NOT NULL | Municipality name |
| cyber | int(11) DEFAULT 0 | Cybersecurity activity count |
| elgu | int(11) DEFAULT 0 | eLGU BPLS activity count |
| fwfa | int(11) DEFAULT 0 | FreeWifi4All activity count |
| iidb | int(11) DEFAULT 0 | IIDB activity count |
| ilcdb | int(11) DEFAULT 0 | ILCDB activity count |
| gecs | int(11) DEFAULT 0 | GECS activity count |
| dream | int(11) DEFAULT 0 | DREAM activity count |
| govnet | int(11) DEFAULT 0 | GovNet activity count |

---

#### `tblsdn`
Surigao del Norte municipality + barangay count reference.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| municipality | varchar(50) NOT NULL | |
| barangay_count | int(11) NOT NULL | |

---

#### `tblsite`
FreeWifi4All detailed site information (legacy/extended).

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| province_huc | varchar(255) NOT NULL | |
| locality | varchar(255) NOT NULL | |
| district | varchar(255) NOT NULL | |
| barangay | varchar(255) NOT NULL | |
| site_code | varchar(100) NOT NULL | |
| site_name | varchar(255) NOT NULL | |
| updated_site | varchar(255) DEFAULT NULL | |
| site_type | varchar(100) DEFAULT NULL | |
| former_site | varchar(100) DEFAULT NULL | |
| strategy | varchar(100) DEFAULT NULL | |
| batch | varchar(100) DEFAULT NULL | |
| lot_no | varchar(100) DEFAULT NULL | |
| supplier | varchar(255) DEFAULT NULL | |
| cir_mbps | decimal(10,2) DEFAULT NULL | |
| site_aux | tinyint(1) DEFAULT NULL | |
| moa_conforme_status | varchar(50) DEFAULT NULL | |
| site_status | varchar(50) DEFAULT NULL | |
| current_site_status | varchar(50) DEFAULT NULL | |
| project_status | varchar(50) DEFAULT NULL | |
| last_seen | date DEFAULT NULL | |
| online_date | date DEFAULT NULL | |
| integ_date | date DEFAULT NULL | |
| year_integrated | year(4) DEFAULT NULL | |
| date_of_termination | date DEFAULT NULL | |
| year_terminated | year(4) DEFAULT NULL | |
| strategy_status | varchar(50) DEFAULT NULL | |
| latitude | decimal(9,6) DEFAULT NULL | |
| longitude | decimal(9,6) DEFAULT NULL | |
| mac_address | varchar(17) DEFAULT NULL | |

---

#### `tblstaff`
Staff user accounts.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| name | varchar(100) NOT NULL | |
| username | varchar(20) NOT NULL | |
| password | varchar(20) NOT NULL | **Plaintext** |
| type | varchar(100) NOT NULL | Role identifier |

---

#### `tbltech4ed`
Tech4Ed Digital Transformation Centers data.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| region | varchar(255) DEFAULT NULL | |
| province | varchar(255) DEFAULT NULL | |
| district | varchar(255) DEFAULT NULL | |
| municipality | varchar(255) DEFAULT NULL | |
| barangay | varchar(255) DEFAULT NULL | |
| street | varchar(255) DEFAULT NULL | |
| location | varchar(255) DEFAULT NULL | |
| cname | varchar(255) DEFAULT NULL | Center name |
| host | varchar(255) DEFAULT NULL | Host institution |
| category | varchar(255) DEFAULT NULL | LGU/private/NGA/school/RIS/etc. |
| longitude | decimal(9,6) DEFAULT NULL | |
| latitude | decimal(9,6) DEFAULT NULL | |
| cmanager | varchar(255) DEFAULT NULL | Center manager name |
| cemail | varchar(255) DEFAULT NULL | |
| cmobile | varchar(20) DEFAULT NULL | |
| clandline | varchar(20) DEFAULT NULL | |
| cgender | varchar(10) DEFAULT NULL | |
| amanager | varchar(255) DEFAULT NULL | Assistant manager |
| aemail | varchar(255) DEFAULT NULL | |
| amobile | varchar(20) DEFAULT NULL | |
| alandline | varchar(20) DEFAULT NULL | |
| agender | varchar(10) DEFAULT NULL | |
| launch | date DEFAULT NULL | |
| registration | date DEFAULT NULL | |
| operation | varchar(50) DEFAULT NULL | |
| visited | date DEFAULT NULL | |
| desktop | int(11) DEFAULT NULL | Desktop count |
| laptop | int(11) DEFAULT NULL | Laptop count |
| printer | int(11) DEFAULT NULL | Printer count |
| scanner | int(11) DEFAULT NULL | Scanner count |
| status | varchar(50) DEFAULT NULL | Operational/Non-Operational |
| network | varchar(255) DEFAULT NULL | |
| connectivity | varchar(255) DEFAULT NULL | |
| speed | varchar(50) DEFAULT NULL | |
| cmtmale | int(11) DEFAULT NULL | |
| cmtfemale | int(11) DEFAULT NULL | |
| straining | date DEFAULT NULL | Training start |
| etraining | date DEFAULT NULL | Training end |
| signing | date DEFAULT NULL | |
| partner | varchar(255) DEFAULT NULL | |
| expiration | date DEFAULT NULL | |
| donation | varchar(255) DEFAULT NULL | |
| datedonation | date DEFAULT NULL | |
| tcms | varchar(255) DEFAULT NULL | |
| key_one | varchar(255) DEFAULT NULL | |
| identifier | varchar(255) DEFAULT NULL | |

---

#### `tbltype`
Locality type summary counts.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| type | varchar(255) NOT NULL | |
| mun | int(11) DEFAULT 0 | |
| pp | int(11) DEFAULT 0 | |
| suc | int(11) DEFAULT 0 | |
| wisps | int(11) DEFAULT 0 | |
| ris | int(11) NOT NULL | |
| vsat | int(11) DEFAULT 0 | |
| phase | int(11) DEFAULT 0 | |

---

#### `tbluser`
Admin login accounts.

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL | PK |
| username | varchar(20) NOT NULL | |
| password | varchar(20) NOT NULL | **Plaintext** |
| type | varchar(20) NOT NULL | |

---

#### `ics` (created by migration 008)

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL AUTO_INCREMENT | PK |
| ics_no | varchar(50) NOT NULL | |
| date_issued | date NOT NULL | |
| received_by | varchar(255) NOT NULL | |
| received_by_position | varchar(255) NOT NULL | |
| received_from | varchar(255) NOT NULL | |
| received_from_position | varchar(255) NOT NULL | |
| date_received | date NOT NULL | |
| total | decimal(12,2) NOT NULL DEFAULT 0.00 | |
| created_by | varchar(100) NOT NULL | |
| created_at | timestamp NOT NULL DEFAULT current_timestamp() | |

---

#### `ics_items` (created by migration 008)

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL AUTO_INCREMENT | PK |
| ics_id | int(11) NOT NULL | FK to ics.id ON DELETE CASCADE |
| qty | int(11) NOT NULL | |
| unit | varchar(50) NOT NULL | |
| description | varchar(255) NOT NULL | |
| unit_cost | decimal(12,2) NOT NULL | |
| date_acquired | date DEFAULT NULL | |
| inventory_item_no | varchar(100) DEFAULT NULL | |
| estimated_useful_life | varchar(50) DEFAULT NULL | |

---

#### `ics_attachments` (created by migration 009)

| Column | Data Type | Notes |
|--------|-----------|-------|
| id | int(11) NOT NULL AUTO_INCREMENT | PK |
| ics_no | varchar(50) NOT NULL | |
| filename | varchar(255) NOT NULL | |
| uploaded_by | varchar(100) DEFAULT NULL | |
| uploaded_at | timestamp NOT NULL DEFAULT current_timestamp() | |

---

### 2.3 Key Relationships

| Relationship | Type | Description |
|-------------|------|-------------|
| `pass_slip.inventory_id` -> `inventory.id` | FK (logical, no formal constraint) | Pass slips reference inventory items |
| `ics_items.ics_id` -> `ics.id` | FK with ON DELETE CASCADE | ICS line items |
| `pass_slip_attachments.pass_slip_no` -> `pass_slip.pass_slip_no` | FK (logical) | Scanned copies for pass slips |
| `tblactivityphoto.activityid` -> `tblactivity.id` | FK (logical) | Activity photos |
| `tblactivity.project` links to `tblproject` columns | Logical | Activity counts per municipality |
| `tblfwfa` and `inventory` share location data | No FK | Both track FW4A deployment items |

---

## 3. EXISTING MODULES

### 3.1 Dashboard

**Page:** `pages/dashboard/dashboard.php`

**Stat Cards (8):**
| Label | Source Table | Description |
|-------|-------------|-------------|
| Total Activities | tblactivity | COUNT(*) |
| Total Participants | tblparticipant | COUNT(*) |
| Total Sectors | tblactivity | COUNT(DISTINCT sector) |
| Total Agencies | tblactivity | COUNT(DISTINCT agency) |
| District 1 | tblactivity | Count where district = 'District 1' or 'District 1 (Siargao)' |
| District 2 | tblactivity | Count where district = 'District 2' or 'District 2 (Mainland)' |
| Total Municipalities | tblactivity | COUNT(DISTINCT municipality) |
| Total Barangays | tblactivity | COUNT(DISTINCT barangay) |

Each stat card links to a drill-down page in `pages/dashboard_data/` showing filtered DataTables.

**Charts (4):**
| Chart | Type | Data Visualized |
|-------|------|----------------|
| Activities by District | Doughnut | District 1 vs District 2 counts |
| Activities per Project | Horizontal Bar | Activity count by project name |
| Top 10 Municipalities by Activity | Vertical Bar | Top 10 municipalities |
| Activities by Sector | Pie | Activity count by sector |

**Table:**
- Title: "Municipality/City Data"
- Columns: Municipality/City | Cybersecurity | eLGU | FWFA | IIDB | ILCDB | GECS | DREAM | GOVNET
- Cross-tabulation of municipality x project activity counts

**Import/Export:** None

---

### 3.2 Cybersecurity

#### Activities Conducted (`pages/activity/cybersecurity.php`)

**Stat Cards (8):**
| Label | Description |
|-------|-------------|
| Total Activities | Cybersecurity activity count |
| Total Municipalities | Distinct municipalities |
| Total Barangay | Distinct barangays |
| Total Sectors | Distinct sectors |
| Planned Activities - Cybersecurity | Link to planned_activities_cyber.php |
| Total Completers | SUM(completers) |
| Male Completers | SUM(male) |
| Female Completers | SUM(female) |

**Charts:** None on this page (charts are on Reports/Graphs page)

**Table Columns:** Checkbox, No., Start Date, End Date, Project, Sub-Project, Activity, Indicator, Training, Municipality, District, Barangay, Agency, Mode, Sector, Resource Speaker, Persons In-Charge, Participants, Completers, Male, Female, Approved, MOV, Remarks, Option (Edit/View)

**Import/Export:** Yes (Import: CSV/XLSX, Export: CSV)

#### Activity Participants (`pages/participant/cybersecurity.php`)

**Table Columns:** Checkbox, No., Start Date, End Date, Activity, Indicator, Full Name, Sex, Contact, Email, Mode, Agency, Sector, Project, Person In-Charge, Remarks, Option

**Import/Export:** Yes

#### Reports/Graphs (`pages/report/cybersecurity.php`)

**Charts (5 Morris.Bar charts, Actual vs Target):**
1. CSB/Advocacy Awareness conducted (face-to-face) -- count
2. Individuals/reach (face-to-face) -- sum of completers
3. PKI Awareness Campaigns -- count
4. Issued Digital Certificates -- sum of completers
5. PNPKI Users Training -- count

Data source: `tblactivity` + `cybersecurity_metrics` targets, filtered by year.

---

### 3.3 eLGU BPLS

#### Monitoring Status (`pages/bpls_data/bpls_monitoring.php`)

**Stat Cards (6):**
| Label | Description |
|-------|-------------|
| Total No. of LGUs | Total municipalities tracked |
| LGUs Availing DICT | LGUs with DICT engagement |
| Implementation Rate (%) | Percentage of implementation |
| 1st District Count | District 1 municipalities |
| 2nd District Count | District 2 municipalities |
| Engagement Rate (%) | Engagement percentage |

**Table Columns:** Municipality/City, V1 (1st District count), V2 (2nd District count), For Training, Own System, totals row

**Import/Export:** Yes

#### Activities Conducted (`pages/activity/elgu.php`)

**Stat Cards (8):** Same pattern as Cybersecurity activities (Total Activities, Municipalities, Barangay, Sectors, Planned Activities link, Total Completers, Male/Female Completers)

**Table Columns:** Same as Cybersecurity activities table

**Import/Export:** Yes

#### Database (`pages/bpls_data/bpls.php`)

**Filters:** Select System (dropdown)

**Table Columns (26):** No., Province, Congressional District, City/Municipality, LGU Name, Class, System Provider, Remarks/Action, then Y/N + Status pairs for: Business Permit (BP), Barangay Clearance (BC), Building Permit (BPCO), Working Permit (WP), FSIC, BPLS, eCedula, eLCR, eNEWS, Remarks, Option

**Import/Export:** Yes

---

### 3.4 FreeWifi4All

#### Monitoring (`pages/fw4a_data/fw4a_data.php`)

**Stat Cards (4):**
| Label | Description |
|-------|-------------|
| LGU Penetration Rate | % of LGUs with FW4A |
| Barangay Penetration Rate | % of barangays with FW4A |
| Active Access Points | Count of Active status |
| Inactive Access Points | Count of Inactive status |

**Filters:** Select Strategy, Select Type, Select Municipality, Select Barangay

**Map:** Leaflet.js heatmap + locators (`#fw4aMap`) showing access point locations with lat/long data from `ajax/fw4a_map_data.php`

**Table Columns:** Checkbox, No., Item No., Locality, Barangay, District, Transport Location, Transport Type, Site Location, Transfer/New Location, Site Type, Date of Activation, Date of Acceptance, Strategy, Status, Procurement Initiative, Installation Type, UAT, Conforme, Site Code, Nationwide ID, Remarks, Option

**Import/Export:** Yes (Import with template at `uploads/fw4a/FW4A.xlsx`)

#### Strategy (`pages/fw4a_data/fw4a_strategy.php`)
Strategy breakdown view for FW4A access points.

#### Letter Requests (`pages/fwfa_letter/letters.php`)

**Stat Cards (4):**
| Label | Description |
|-------|-------------|
| Localities | Count of unique localities |
| Barangays | Count of unique barangays |
| Requests | Count where type = 'Request' |
| Provisions | Count where type = 'Provision' |

**Filters:** Locality, Barangay, Type, Year

**Table Columns:** Checkbox, No., Locality, Barangay, District, Location Name, Date Requested, Year, Type, Status, Accomplished Date, Remarks, Option

**Import/Export:** Yes

#### Activities Conducted (`pages/activity/fwfa.php`)

**Stat Cards (8):** Same 8-card pattern as other activity pages

**Import/Export:** Yes

---

### 3.5 GECS

#### Activities Conducted (`pages/activity/gecs.php`)

**Stat Cards (8):** Same 8-card pattern as other activity pages

**Import/Export:** Yes

---

### 3.6 GovNet

**Status: EMPTY / Not implemented.** The sidebar menu item exists but the submenu is a placeholder comment only. No data pages, no table, no tracking functionality exists.

---

### 3.7 IIDB

#### Activities Conducted (`pages/activity/iidb.php`)

**Stat Cards (8):** Same 8-card pattern as other activity pages

**Import/Export:** Yes

---

### 3.8 ILCDB

#### Activities Conducted (`pages/activity/ilcdb.php`)

**Stat Cards (8):** Same 8-card pattern as other activity pages

**Import/Export:** Yes

#### Activity Participants (`pages/participant/ilcdb.php`)

**Table Columns:** Same participant table pattern as Cybersecurity participants

**Import/Export:** Yes

#### Tech4Ed DTC (`pages/tech4ed/tech4ed.php`)

**Stat Cards (8):**
| Label | Description |
|-------|-------------|
| Total LGU Centers | Category = LGU |
| Total Schools | Category = School |
| Total NGAs | Category = NGA |
| Total Private | Category = Private |
| Total RIS | Category = RIS |
| Provincial Center | Provincial-level center |
| Operational | Status = Operational |
| Non-Operational | Status = Non-Operational |

**Filters:** Category, Municipality, Barangay, Year

**Table Columns (47):** No., Region, Province, District, Municipality, Barangay, Street, Location, Center Name, Host, Category, Longitude, Latitude, Center Manager, C-Email, C-Mobile, C-Landline, C-Gender, Asst. Manager, A-Email, A-Mobile, A-Landline, A-Gender, Launch Date, Registration Date, Operation, Visited, Desktop, Laptop, Printer, Scanner, Status, Network, Connectivity, Speed, CMT Male, CMT Female, Training Start, Training End, Signing, Partner, Expiration, Donation, Date Donation, TCMS, Key, Identifier, Option

**Import/Export:** Yes

#### Reports/Graphs (`pages/report/ilcdb.php`)

**Charts (6 Morris.Bar charts, Actual vs Target):**
1. ICT Proficiency Diagnostic Examination -- count
2. Examinees -- count
3. SPARK Technical Training -- count
4. SPARK Completers -- count
5. Capacity Development (DLT) -- count
6. Digital Transformative Technologies -- count

Data source: `tblactivity` + `cybersecurity_metrics` targets

---

### 3.9 Property Management

#### Inventory Records (`pages/property_records/property_records.php`)

**Stat Cards (6):**
| Label | Description |
|-------|-------------|
| Total Items | Count where project != '' |
| Total Asset Value | SUM(cost * quantity) |
| Currently Borrowed | Pass slips with status = 'borrowed' |
| Overdue Returns | Pass slips where return_date < CURDATE() |
| Non-Consumable | Inventory where item_type = 'equipment' |
| Consumable Supplies | Inventory where item_type = 'consumable' |

**Filters:** Project, ICS, Year, Remarks, Status

**Table Columns (22):** Checkbox, No., Project, Item No., Classification, Type (Equipment/Consumable badge), Quantity, Remaining Stock, Unit, Description/Model, Received From, Property Number, ICS/PAR Number, Serial Number, Date Acquired, Accountable Officer, Unit Cost, Estimated Useful Life, Received/Transferred, Remarks, Status (Available/For Deployment/Deployed/Temp. Deployed/Defective/Replaced badge), Option (Edit, View Files, Pass Slip History, Print)

**Import/Export:** Yes

**Notable Features:** View Files modal (photo/document upload), Pass Slip History modal per item, Print per row

#### Office Equipment Pass Slip (`pages/pass_slip/pass_slip.php`)

**Two Tabs:**

**Tab 1 -- Pass Slip Records:**

**Stat Cards (4):** Total Pass Slips, Currently Borrowed, Returned, Overdue

**Filters:** Status, Date From/To, Search Borrower

**Table Columns:** Checkbox, No., Pass Slip No., Items (truncated), # (item count), Pull-Out Date, Requested By, Status badge, Actions (View, Process Return, Print, Print Acknowledgement, Upload Scanned Copy)

**Tab 2 -- ICS Records:**

**Stat Cards (3):** Total ICS Issued, Total Value, Pending Signature

**Filters:** Date Issued From/To, Search ICS No.

**Table Columns:** Checkbox, No., ICS No., Item Count, Date Issued, Total, Date Received, Actions (Upload, Print)

**Import/Export:** None

---

### 3.10 Procurement

**Page:** `pages/property_items/property.php`

**Stat Cards (4):**
| Label | Description |
|-------|-------------|
| Total Records | COUNT(*) from procurement_tracking |
| Total Amount | SUM(amount) |
| Paid | Count where payment_status = 'Paid' |
| Pending | Count where payment_status = 'Pending' or empty |

**Filters:** Project Fund Source, Payment Status, Year Forwarded to RO

**Table Columns (18):** Checkbox, No., PR No., Activity ID, Activity Name, Link to File, Project Fund Source, Type of Items, Amount, Supplier, JO/PO, Link to Attachments, Personnel In-charge, Date Forwarded to RO, Transmittal Report, Payment Status (Paid/Pending/Partial/Cancelled badge), Remarks, Option

**Import/Export:** Yes

**Notable:** Activity ID is a large enum dropdown (~80+ values like GOVNET-0001, FW4A-0001, ILCDB-0001, etc.). Type of Items enum: Meals, Fuel (Diesel), Office Supplies, Plaque, Service.

---

### 3.11 Purchase Request

**Page:** `pages/property_records/purchase_request.php`

**Status: Page Under Maintenance.** Shows a wrench icon with "Page Under Maintenance" message. No functionality implemented.

---

### 3.12 Bills Monitoring

**Page:** `pages/bills_monitoring/bills_monitoring.php`

**Stat Cards (3):**
| Label | Description |
|-------|-------------|
| Total Amount | Sum of all bill amounts (PHP) with count |
| Paid Amount | Sum of paid bill amounts with count |
| Unpaid Amount | Sum of unpaid bill amounts with count |

Cards are clickable filters (clicking filters the table).

**Charts (2):**
| Chart | Type | Data |
|-------|------|------|
| Bills by Status | Doughnut | Paid vs Unpaid (amount-weighted, falls back to count) |
| Amounts by Type of Billing | Horizontal Bar | Amount per billing type (Water/Internet/Electricity) |

**Filters:** Status (All/Paid/Unpaid), Type (All/Water/Internet/Electricity), Location (All/SDN Provincial Office/SDN Hill Relay Station/No location), Date Received From/To

**Table Columns (14 admin, 13 staff):** Checkbox (admin), No., Date Received, Type of Billing, Link to File, Amount, Location/Office, Due Date, Disconnection Date, Status (Paid/Unpaid badge), Date Paid, Remarks, Link to OR, Option (admin)

**Import/Export:** Yes (admin only, CSV/XLSX)

---

### 3.13 Letters Monitoring

**Page:** `pages/letters_monitoring/letters_monitoring.php`

**Stat Cards (6):**
| Label | Description |
|-------|-------------|
| Total Letters | All letter records |
| Needs Response | for_response = Y and no response date |
| Responded | Has response date |
| Not Specified | for_response not specified |
| Request | fw4a = Request |
| Provision | fw4a = Provision |

Cards are clickable filters.

**Charts (3):**
| Chart | Type | Data |
|-------|------|------|
| Letters by Type | Doughnut | Incoming vs Outgoing |
| Response Status | Doughnut | Yes / No / Not Specified |
| FW4A Distribution | Bar | Letter counts per FW4A value |

**Filters:** Type (All/Incoming/Outgoing), For Response (All/Yes/No), Date From/To

**Table Columns (16 admin, 15 staff):** Checkbox (admin), No., Date, Type (Incoming/Outgoing badge), Subject, FW4A, Link Incoming, For Response? (Yes/No badge), Date Responded, Link Outgoing, Responsible Person, Who Attended, Remarks, Post Activity Report, Option (admin)

**Import/Export:** Yes (admin only, CSV/XLSX)

---

### 3.14 Credentials

**Page:** `pages/credentials/credentials.php`

**Description:** Staff account management page. Administrator only.

**Stat Cards:** None (panel heading "Statistics" exists but is empty -- only contains Add/Delete buttons)

**Table Columns (4):** Checkbox (select all for delete), Name, Username, Option (Edit button per row)

**Import/Export:** None

**Notable:** No charts, no stat data. Simple CRUD for `tblstaff` records.

---

### 3.15 Logs

**Page:** `pages/logs/logs.php`

**Description:** Read-only audit trail of user actions. Administrator only.

**Stat Cards:** None (panel heading exists but is empty)

**Table Columns (4):** No. (row number), User, Date, Action

**Import/Export:** None

**Notable:** Fully AJAX-loaded from `ajax/logs_data.php`. Read-only -- no CRUD operations.

---

## 4. CURRENT MAIN DASHBOARD

### 4.1 Does a top-level Dashboard exist?

**YES.** A dedicated Dashboard page exists at `pages/dashboard/dashboard.php`, separate from each module's internal stats.

### 4.2 What does it currently show?

The Dashboard serves as the landing page after login and displays:

1. **Header:** DICT Surigao del Norte Provincial Office logo, address, and live date/time
2. **8 Stat Cards:** Total Activities, Total Participants, Total Sectors, Total Agencies, District 1, District 2, Total Municipalities, Total Barangays
3. **4 Charts:**
   - Activities by District (Doughnut)
   - Activities per Project (Horizontal Bar)
   - Top 10 Municipalities by Activity (Vertical Bar)
   - Activities by Sector (Pie)
4. **Cross-tabulation Table:** Municipality/City data broken down by project (Cybersecurity, eLGU, FWFA, IIDB, ILCDB, GECS, DREAM, GOVNET)

### 4.3 Limitations of current Dashboard

- **Only covers activity/program data** from `tblactivity` and `tblparticipant`
- **Does NOT aggregate** data from:
  - Bills Monitoring (financial data)
  - Letters Monitoring (correspondence)
  - Procurement (spending)
  - Property Management / Inventory (asset data)
  - FreeWifi4All deployment status
  - eLGU BPLS implementation status
  - Pass Slip / ICS borrowing data
  - Cybersecurity/ILCDB actual vs target performance
- **No system-wide KPIs** or cross-module summary
- **No financial overview** (total spending, bill payments, procurement amounts)
- **No recent activity feed** or alerts
- The existing dashboard is essentially a **program activity summary**, not a comprehensive system overview

---

## 5. DATA VOLUME SNAPSHOT

Based on the SQL dump data (INSERT statements in `db/dict_proj.sql`) and page observations:

| Table | Approx. Row Count | Notes |
|-------|-------------------|-------|
| `tblactivity` | ~120 records | Core activity data across all programs |
| `tblparticipant` | ~500 records | Individual participant records |
| `bills_monitoring` | **26 records** | Bills from Jan-Sep 2026 |
| `letters_monitoring` | **~59 records** | Letters tracked |
| `procurement_tracking` | **~4 records** | Very few procurement records |
| `inventory` | **~55+ records** | Mostly FW4A equipment (Satellite kits, Access Points, Routers, UPS, COMBOX) |
| `tblfwfa` | **~300+ records** | FW4A access point deployments |
| `tbltech4ed` | **~55+ records** | Tech4Ed center records |
| `tblbpls` | **21 records** | One per municipality in Surigao del Norte |
| `tblbplsmonitoring` | **21 records** | One per municipality |
| `tblmunicipal` | **21 records** | 21 municipalities/cities |
| `tblbrgy` | **~300+ records** | Barangay reference data |
| `cybersecurity_metrics` | **26 records** | Annual targets |
| `pass_slip` | **Few records** (unknown exact count) | Equipment borrow records |
| `pass_slip_attachments` | **~2 records** | Scanned pass slip copies |
| `tbllogs` | **~100+ records** | System activity logs |
| `tbluser` | **2 records** | Admin accounts (dictsdn, user) |
| `tblstaff` | **6 records** | Staff accounts |
| `classifications` | **~90+ records** | Inventory classification categories |
| `procurement` | **~120+ records** | Legacy product code reference |
| `tbllocality` | **21 records** | Locality summary data |
| `tbltype` | **18 records** | Locality type summary |
| `tblproject` | **21 records** | Pre-computed activity counts per municipality |
| `tblsdn` | **21 records** | Municipality + barangay count |
| `tblsite` | **~50+ records** | FW4A detailed site data |
| `tblactivityphoto` | **~30+ records** | Activity photos |
| `locationrequests` | **18 records** | FW4A letter requests |
| `targets_initiatives` | **~30+ records** | Planned activities/targets |
| `tblfwfa_backup` | **~300+ records** | Backup (not actively used) |
| `tblfwfa_preimport_backup` | **~300+ records** | Backup (not actively used) |
| `bills_monitoring_backup_005` | **0 records** | Backup (empty) |
| `ics` | **UNKNOWN** (not in SQL dump, created by migration) | Needs live DB query |
| `ics_items` | **UNKNOWN** (not in SQL dump, created by migration) | Needs live DB query |
| `ics_attachments` | **UNKNOWN** (not in SQL dump, created by migration) | Needs live DB query |

**Modules with real data to visualize:** Bills (26), Letters (~59), Activities (~120), Participants (~500), Inventory (~55+), FW4A (~300+), Tech4Ed (~55+)

**Modules with minimal data:** Procurement (~4), Pass Slip (few), Logs (~100+)

**Modules still mostly empty:** Procurement tracking has very few records

---

## 6. KNOWN ISSUES OR INCONSISTENCIES

### 6.1 Security Issues
- **Plaintext passwords** in `tbluser` and `tblstaff` tables. Login queries compare raw password strings with no hashing.
- **SQL injection surface:** Multiple pages build SQL queries via string concatenation. While `mysqli_real_escape_string` is used in many places, the pattern is inconsistent across the codebase.

### 6.2 Schema Inconsistencies
- **`inventory.cost` is stored as TEXT** (comma-formatted string like "43,904.00") rather than a numeric type. This makes SUM/AVG queries difficult and requires CAST or string manipulation for calculations.
- **`letters_monitoring.for_response` uses enum('Y','N')** while the UI displays "Yes/No" badges -- the migration 001 attempted to change this but the SQL dump still shows Y/N.
- **`locationrequests.date` is stored as TEXT** instead of DATE type.
- **`tbluser.password` and `tblstaff.password` are varchar(20)** -- extremely short for any password, and stores plaintext.

### 6.3 Backup/Reference Table Confusion
- `bills_monitoring_backup_005` has a completely different schema than `bills_monitoring` (enum status vs tinyint status, decimal vs double amount). It is never queried by any page.
- `tblfwfa_backup` and `tblfwfa_preimport_backup` are full copies of `tblfwfa` data, never actively used.
- `procurement` table (product code reference list) appears to be legacy/unused -- the active procurement tracking uses `procurement_tracking`.

### 6.4 Incomplete/Placeholder Modules
- **GovNet:** Sidebar menu item exists but submenu is completely empty (only an HTML comment placeholder). No data table, no tracking page, no data.
- **Purchase Request:** Page exists but displays "Page Under Maintenance" with no functionality.
- **Credentials page:** The "Statistics" panel heading exists but contains no stat cards -- only Add/Delete buttons.
- **Logs page:** Same issue -- "Statistics" panel heading with no content.

### 6.5 Inconsistent Import/Export Coverage
- **Modules WITH Import/Export:** Cybersecurity activities/participants, eLGU BPLS (all 3 pages), FreeWifi4All (all 4 pages), GECS activities, IIDB activities, ILCDB (activities/participants/Tech4Ed), Procurement, Inventory Records, Bills Monitoring, Letters Monitoring
- **Modules WITHOUT Import/Export:** Dashboard, Pass Slip/ICS, Credentials, Logs, Reports/Graphs pages (cybersecurity and ILCDB reports)

### 6.6 Database Configuration Issues
- `pages/backup/backup.php` hardcodes `$db = 'db_grade'` instead of `dict_proj` -- this appears to be a leftover from a different project and would backup the wrong database.
- `db/dict_final.sql` targets database `dict_data` (old name) while the current system uses `dict_proj`.

### 6.7 Character Set Issues
- Several core tables (`tblactivity`, `tblparticipant`, `targets_initiatives`) use `latin1` collation while newer tables use `utf8mb4`. This can cause encoding mismatches when joining data across tables.

### 6.8 Duplicate Data Patterns
- Activity data exists in both `tblactivity` (actual) and `targets_initiatives` (planned) with identical column structures but no formal relationship between them.
- `tblproject` appears to store pre-computed activity counts that could become stale if `tblactivity` is updated without refreshing.

### 6.9 Missing Primary Keys / Auto-Increment
- `procurement` table has no PRIMARY KEY defined.
- Several tables define `id` as NOT NULL but the SQL dump does not show AUTO_INCREMENT for all of them.

### 6.10 Mixed Chart Libraries
- Some pages use **Morris.js** (activities, reports)
- Dashboard and Bills/Letters pages use **Chart.js**
- Report pages use **JpGraph** for PNG export
- This creates three different chart rendering approaches in the same application.

---

*Document generated: September 15, 2026*
*Source: Read-only analysis of project codebase and database schema*
*No code was modified during this documentation process.*
