# Heatmap & Locators Feature Plan

## Project
**SDN Monitoring System - FreeWifi4All (FW4A)**
**Target File:** `pages/fw4a_data/fw4a_data.php`

---

## Overview

Add an interactive Leaflet.js map with **heatmap overlay** and **locator markers** to the FreeWifi4All data dashboard. The map sits between the filter dropdowns and the data table, syncs with existing filters, and shows popups with access point details on marker click.

---

## Technology Choices

| Component | Selection | Reason |
|-----------|-----------|--------|
| Map Library | **Leaflet.js v1.9.4** | Free, open-source, no API key required |
| Tile Provider | **OpenStreetMap** | Free, no usage limits |
| Heatmap Plugin | **leaflet.heat v0.2.0** | Lightweight, official Leaflet plugin |
| Coordinate Source | `tblfwfa.latitude` / `tblfwfa.longitude` | Already stored in existing database |

---

## Files to Modify / Create

| # | File Path | Action | Purpose |
|---|-----------|--------|---------|
| 1 | `pages/head_css.php` | **Edit** | Add Leaflet CSS + JS + heatmap plugin CDN |
| 2 | `ajax/fw4a_map_data.php` | **Create** | New AJAX endpoint returning filtered coordinate data |
| 3 | `pages/fw4a_data/fw4a_data.php` | **Edit** | Add map HTML panel, JS logic, and CSS |

---

## Step 1: Add Leaflet.js CDN to `pages/head_css.php`

### What to add
Add the following CDN links before the closing `</head>` tags:

```html
<!-- Leaflet.js CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<!-- Leaflet Heatmap Plugin -->
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
```

Add the Leaflet JS **after** jQuery is loaded (after line 24 in head_css.php):

```html
<!-- Leaflet.js JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
```

### Why
Leaflet and its heatmap plugin must be loaded globally so all pages can use them. The JS must come after jQuery since the project depends on jQuery.

---

## Step 2: Create AJAX Endpoint `ajax/fw4a_map_data.php`

### Full File Content

```php
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

// Accept same filter parameters as fw4a_data.php
$strategy = isset($_GET['strategy']) ? trim($_GET['strategy']) : '';
$type     = isset($_GET['type']) ? trim($_GET['type']) : '';
$locality = isset($_GET['locality']) ? trim($_GET['locality']) : '';
$barangay = isset($_GET['barangay']) ? trim($_GET['barangay']) : '';

// Build WHERE clause - only include records with valid coordinates
$where = ["latitude IS NOT NULL", "longitude IS NOT NULL", "latitude != 0", "longitude != 0"];
$params = [];
$types  = '';

if ($strategy !== '') {
    $where[] = "strategy = ?";
    $params[] = $strategy;
    $types .= 's';
}
if ($type !== '') {
    $where[] = "type = ?";
    $params[] = $type;
    $types .= 's';
}
if ($locality !== '') {
    $where[] = "locality = ?";
    $params[] = $locality;
    $types .= 's';
}
if ($barangay !== '') {
    $where[] = "barangay = ?";
    $params[] = $barangay;
    $types .= 's';
}

$whereClause = implode(' AND ', $where);

$query = "SELECT id, latitude, longitude, locality, barangay, district,
                 locations, type, code, nationwide_id, strategy, status, remarks
          FROM tblfwfa
          WHERE $whereClause
          ORDER BY locality ASC";

$stmt = mysqli_prepare($con, $query);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$points = [];
while ($row = mysqli_fetch_assoc($result)) {
    $points[] = [
        'id'          => intval($row['id']),
        'lat'         => floatval($row['latitude']),
        'lng'         => floatval($row['longitude']),
        'locality'    => $row['locality'],
        'barangay'    => $row['barangay'],
        'district'    => $row['district'],
        'locations'   => $row['locations'],
        'type'        => $row['type'],
        'code'        => $row['code'],
        'nationwide_id' => $row['nationwide_id'],
        'strategy'    => $row['strategy'],
        'status'      => $row['status'],
        'remarks'     => $row['remarks']
    ];
}
mysqli_stmt_close($stmt);

echo json_encode([
    'points' => $points,
    'total'  => count($points)
]);
```

### Key Design Decisions
- Uses **prepared statements** (same pattern as existing `ajax/fw4a_data.php`)
- Filters out records with NULL or 0 coordinates
- Returns only the fields needed for map display (not all `SELECT *`)
- Respects the same 4 filter dropdowns (Strategy, Type, Municipality, Barangay)
- No pagination needed since all coordinate points are returned for the map

---

## Step 3: Add Map HTML Panel in `fw4a_data.php`

### Insertion Point
Between the closing `</div>` of the filter row (line ~317) and the toolbar `<div style="padding:10px;...">` (line ~321).

### HTML to Insert

```html
<!-- ========================= MAP SECTION ========================= -->
<div class="row" style="margin-top: 10px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
                 data-toggle="collapse" data-target="#mapCollapse">
                <span><i class="fa fa-map-marker"></i> Access Points Map</span>
                <div>
                    <button class="btn btn-xs btn-default" id="mapToggleView" title="Toggle Heatmap/Locators" onclick="event.stopPropagation();">
                        <i class="fa fa-fire"></i> Heatmap
                    </button>
                    <i class="fa fa-chevron-down"></i>
                </div>
            </div>
            <div id="mapCollapse" class="collapse in">
                <div class="panel-body" style="padding:0;">
                    <div id="fw4aMap" style="width:100%; height:450px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ========================= END MAP SECTION ========================= -->
```

### Behavior
- **Collapsible:** Click the panel heading to expand/collapse the map
- **Default state:** Collapsed open (`collapse in`)
- **Toggle button:** Switches between Heatmap and Locator view (does NOT toggle collapse)
- **Map height:** 450px fixed

---

## Step 4: Add Map JavaScript Logic in `fw4a_data.php`

### Insertion Point
Inside the existing IIFE `<script>` block (line ~507-1089), add the map functions. Insert the map initialization at the bottom near the `// ========== INIT ==========` section.

### JavaScript Code

```javascript
// ========== MAP ==========
var fw4aMap = null;
var mapMarkersLayer = null;
var mapHeatLayer = null;
var mapMode = 'heatmap'; // 'heatmap' or 'markers'

function initMap() {
    // Default center: Philippines
    fw4aMap = L.map('fw4aMap', {
        center: [12.8797, 121.7740],
        zoom: 6,
        scrollWheelZoom: true
    });

    // OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(fw4aMap);

    // Initialize empty layers
    mapMarkersLayer = L.layerGroup();
    mapHeatLayer = L.heatLayer([], {
        radius: 25,
        blur: 15,
        maxZoom: 17,
        gradient: {
            0.2: 'blue',
            0.4: 'cyan',
            0.6: 'lime',
            0.8: 'yellow',
            1.0: 'red'
        }
    });

    loadMapData();
}

function loadMapData() {
    var f = getFilters();
    var params = 'strategy=' + encodeURIComponent(f.strategy) +
                 '&type=' + encodeURIComponent(f.type) +
                 '&locality=' + encodeURIComponent(f.locality) +
                 '&barangay=' + encodeURIComponent(f.barangay);

    $.getJSON(basePath + 'fw4a_map_data.php?' + params, function(res) {
        renderMapPoints(res.points || []);
    }).fail(function() {
        console.error('Failed to load map data');
    });
}

function renderMapPoints(points) {
    // Clear existing layers
    fw4aMap.removeLayer(mapMarkersLayer);
    fw4aMap.removeLayer(mapHeatLayer);
    mapMarkersLayer.clearLayers();

    if (points.length === 0) return;

    var heatData = [];
    var markers = [];

    points.forEach(function(p) {
        // --- Markers ---
        var statusColor = p.status === 'Active' ? '#27ae60' : '#e74c3c';
        var marker = L.circleMarker([p.lat, p.lng], {
            radius: 7,
            fillColor: statusColor,
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        });

        var popupContent =
            '<div style="font-size:13px; line-height:1.6;">' +
                '<b style="color:darkblue; font-size:14px;">' + escHtml(p.locations || 'N/A') + '</b><br>' +
                '<b>Locality:</b> ' + escHtml(p.locality) + '<br>' +
                '<b>Barangay:</b> ' + escHtml(p.barangay) + '<br>' +
                '<b>District:</b> ' + escHtml(p.district || 'N/A') + '<br>' +
                '<b>Code:</b> ' + escHtml(p.code || 'N/A') + '<br>' +
                '<b>Type:</b> ' + escHtml(p.type) + '<br>' +
                '<b>Strategy:</b> ' + escHtml(p.strategy) + '<br>' +
                '<b>Status:</b> <span style="color:' + statusColor + '; font-weight:bold;">' + escHtml(p.status) + '</span><br>' +
                '<b>Coordinates:</b> ' + p.lat.toFixed(6) + ', ' + p.lng.toFixed(6) +
                (p.remarks ? '<br><b>Remarks:</b> ' + escHtml(p.remarks) : '') +
            '</div>';

        marker.bindPopup(popupContent, { maxWidth: 300, className: 'fw4a-popup' });
        mapMarkersLayer.addLayer(marker);
        markers.push(marker);

        // --- Heatmap data ---
        heatData.push([p.lat, p.lng, 1]);
    });

    // Rebuild heatmap layer with new data
    mapHeatLayer = L.heatLayer(heatData, {
        radius: 25,
        blur: 15,
        maxZoom: 17,
        gradient: {
            0.2: 'blue',
            0.4: 'cyan',
            0.6: 'lime',
            0.8: 'yellow',
            1.0: 'red'
        }
    });

    // Show active layer
    if (mapMode === 'heatmap') {
        mapHeatLayer.addTo(fw4aMap);
    } else {
        mapMarkersLayer.addTo(fw4aMap);
    }

    // Auto-zoom to fit all markers
    if (markers.length > 0) {
        var group = new L.featureGroup(markers);
        fw4aMap.fitBounds(group.getBounds().pad(0.1));
    }

    // Invalidate map size in case it was hidden/collapsed
    setTimeout(function() { fw4aMap.invalidateSize(); }, 300);
}

// Toggle between Heatmap and Markers
$('#mapToggleView').on('click', function() {
    if (mapMode === 'heatmap') {
        fw4aMap.removeLayer(mapHeatLayer);
        mapMarkersLayer.addTo(fw4aMap);
        mapMode = 'markers';
        $(this).html('<i class="fa fa-map-marker"></i> Locators');
    } else {
        fw4aMap.removeLayer(mapMarkersLayer);
        mapHeatLayer.addTo(fw4aMap);
        mapMode = 'heatmap';
        $(this).html('<i class="fa fa-fire"></i> Heatmap');
    }
});

// Invalidate map size when collapse is toggled (Bootstrap collapse event)
$('#mapCollapse').on('shown.bs.collapse', function() {
    if (fw4aMap) fw4aMap.invalidateSize();
});
$('#mapCollapse').on('hidden.bs.collapse', function() {
    if (fw4aMap) fw4aMap.invalidateSize();
});
```

### Changes to Existing Init Section
Replace the `// ========== INIT ==========` block (lines ~1084-1086) with:

```javascript
// ========== INIT ==========
loadFilters();
loadData(1);
initMap();
```

### Changes to Existing Filter Handler
Update the filter change handler (lines ~743-748) to also refresh the map:

```javascript
$('#strategySelect, #typeSelect, #localitySelect, #barangaySelect').on('change', function() {
    clearSelection();
    loadData(1);
    loadFilters();
    loadStats();
    loadMapData();  // <-- ADD THIS LINE
});
```

---

## Step 5: Add Map CSS in `fw4a_data.php`

### Insertion Point
Inside the existing `<style>` block (line ~1091-1303), add at the end before the closing `</style>` tag.

### CSS to Add

```css
/* ========== MAP STYLES ========== */
#fw4aMap {
    z-index: 1;
}

.leaflet-popup-content-wrapper {
    border-radius: 8px;
    font-size: 13px;
    box-shadow: 0 3px 14px rgba(0,0,0,0.3);
}

.leaflet-popup-content {
    margin: 10px 15px;
    line-height: 1.5;
}

.leaflet-popup-content b {
    color: darkblue;
}

.fw4a-popup .leaflet-popup-content {
    max-height: 300px;
    overflow-y: auto;
}

.panel-heading:hover {
    background-color: #f5f5f5;
}

#mapToggleView {
    margin-right: 10px;
}
```

---

## Behavior Summary

| Feature | Behavior |
|---------|----------|
| **Default view** | Heatmap mode showing density of all access points |
| **Toggle button** | Switches between Heatmap and Locator (circle marker) view |
| **Marker colors** | Green = Active, Red = Inactive |
| **Filter sync** | Map auto-updates when Strategy / Type / Municipality / Barangay filters change |
| **Collapse** | Map panel is collapsible (open by default, click heading to toggle) |
| **Marker popup** | Shows locality, barangay, district, code, type, strategy, status, coordinates, remarks |
| **Auto-zoom** | Map bounds auto-adjust to fit all visible markers with 10% padding |
| **Invalid coords** | Records with NULL or 0 latitude/longitude are excluded from the map |
| **No API key** | Uses free Leaflet.js + OpenStreetMap tiles |
| **Responsive** | Map recalculates size when panel is collapsed/expanded |

---

## Data Flow Diagram

```
User changes filter dropdown
         |
         v
getFilters() collects filter values
         |
         v
loadMapData() fires AJAX GET to fw4a_map_data.php
         |
         v
ajax/fw4a_map_data.php queries tblfwfa with WHERE clause
         |
         v
Returns JSON: { points: [{id, lat, lng, locality, ...}], total: N }
         |
         v
renderMapPoints(points) clears & rebuilds map layers
    |                         |
    v                         v
mapMarkersLayer           mapHeatLayer
(circle markers)          (heat intensity)
    |                         |
    v                         v
Displayed based on current mapMode ('markers' or 'heatmap')
         |
         v
Auto-zoom: fitBounds() to show all points
```

---

## Estimated Effort

| Step | Complexity | Time |
|------|------------|------|
| Step 1: CDN links in head_css.php | Low | 5 min |
| Step 2: Create ajax/fw4a_map_data.php | Medium | 15 min |
| Step 3: Add map HTML panel | Low | 10 min |
| Step 4: Add map JavaScript logic | High | 30 min |
| Step 5: Add map CSS | Low | 5 min |
| **Total** | | **~65 min** |

---

## Testing Checklist

- [ ] Map loads and shows OpenStreetMap tiles
- [ ] Heatmap displays with color gradient
- [ ] Toggle button switches to locator markers
- [ ] Markers show correct colors (green=Active, red=Inactive)
- [ ] Clicking a marker opens a popup with correct details
- [ ] Changing Municipality filter updates the map
- [ ] Changing Strategy filter updates the map
- [ ] Changing Type filter updates the map
- [ ] Changing Barangay filter updates the map
- [ ] Map auto-zooms to fit all visible points
- [ ] Map panel collapses and expands correctly
- [ ] Map recalculates size after collapse/expand
- [ ] Records without valid coordinates do not cause errors
- [ ] Empty filter results show an empty map without errors
- [ ] Existing page functionality (CRUD, pagination, search) still works
