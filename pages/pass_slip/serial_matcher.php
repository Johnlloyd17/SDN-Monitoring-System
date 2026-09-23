<?php
// ============================================================
// Reusable serial-number matcher for Pass Slip views.
// Builds a normalized set of known serials from inventory and
// UAT Management so serials can be flagged with a "Matched" badge.
// ============================================================

function normalize_serial($s) {
    return mb_strtoupper(trim((string)$s));
}

function load_known_serials($con) {
    $known = array();

    $r = @mysqli_query($con, "SELECT serial FROM inventory WHERE serial IS NOT NULL AND TRIM(serial) <> ''");
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $k = normalize_serial($row['serial']);
            if ($k !== '') $known[$k] = true;
        }
    }

    $r = @mysqli_query($con, "SELECT serial_numbers FROM uat_items WHERE serial_numbers IS NOT NULL AND TRIM(serial_numbers) <> ''");
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            foreach (explode(',', $row['serial_numbers']) as $tok) {
                $k = normalize_serial($tok);
                if ($k !== '') $known[$k] = true;
            }
        }
    }

    return $known;
}

function is_matched_serial(array $known, $serial) {
    if ($serial === null || trim((string)$serial) === '') return false;
    return isset($known[normalize_serial($serial)]);
}

function load_uat_transport_locations($con) {
    $locations = array();

    $r = @mysqli_query($con, "SELECT u.transport_location AS transport_location, ui.serial_numbers AS serial_numbers
                              FROM uat_items ui
                              JOIN uat u ON ui.uat_id = u.id
                              WHERE ui.serial_numbers IS NOT NULL AND TRIM(ui.serial_numbers) <> ''");
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $loc = trim((string)$row['transport_location']);
            foreach (explode(',', $row['serial_numbers']) as $tok) {
                $k = normalize_serial($tok);
                if ($k !== '') $locations[$k] = $loc;
            }
        }
    }

    return $locations;
}