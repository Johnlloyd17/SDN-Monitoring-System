<?php
/**
 * Shared spreadsheet preview endpoint (.xlsx / .csv).
 *
 * Parses an uploaded attachment into JSON so the file-preview modals can
 * render it as an HTML table. Kept dependency-free on purpose: the app
 * already parses XLSX elsewhere (pages/fw4a_data/import.php) with
 * ZipArchive + SimpleXML, so we reuse that approach instead of pulling in
 * a client-side library over the network.
 *
 * GET src=<allowlisted key>  file=<basename>
 *
 * `src` selects one of the known upload directories; `file` is always
 * reduced to a basename and the resolved realpath is re-checked for
 * containment, so neither parameter can be used to read outside them.
 *
 * Response: { success, file, ext, sheets: [{ name, rows, cols, truncated }],
 *             truncated }
 */

require_once __DIR__ . '/../pages/auth_check.php';
require_auth_api();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

const MAX_ROWS    = 500;
const MAX_COLS    = 60;
const MAX_SHEETS  = 20;
const MAX_BYTES   = 20971520; // 20 MB

function sheet_fail($msg, $code = 400)
{
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

// ---------------------------------------------------------------------
// Allowlisted upload directories
// ---------------------------------------------------------------------
$ROOT = dirname(__DIR__);

$DIRS = [
    'property_records' => $ROOT . '/pages/property_records/photo',
    'bpls'             => $ROOT . '/pages/bpls_data/photo',
    'fw4a'             => $ROOT . '/pages/fw4a_data/photo',
    'letters'          => $ROOT . '/pages/fwfa_letter/photo',
    'participant'      => $ROOT . '/pages/participant/photo',
    'tech4ed'          => $ROOT . '/pages/tech4ed/photo',
    'targets'          => $ROOT . '/pages/targets/photo',
    'activity'         => $ROOT . '/pages/activity/photo',
    'planned'          => $ROOT . '/pages/planned_activities/photo',
    'pass_slip'        => $ROOT . '/uploads/pass_slip',
    'ics'              => $ROOT . '/uploads/ics',
];

$src  = isset($_GET['src']) ? (string)$_GET['src'] : '';
$file = isset($_GET['file']) ? (string)$_GET['file'] : '';

if ($src === '' || !isset($DIRS[$src])) {
    sheet_fail('Unknown file source.');
}
if ($file === '') {
    sheet_fail('No file specified.');
}

// ---------------------------------------------------------------------
// Path containment
// ---------------------------------------------------------------------
$base  = realpath($DIRS[$src]);
$name  = basename($file);
if ($base === false || $name === '' || $name === '.' || $name === '..') {
    sheet_fail('File not found.');
}

$path = realpath($base . DIRECTORY_SEPARATOR . $name);
if ($path === false || !is_file($path)) {
    sheet_fail('File not found.');
}
// Defence in depth: the resolved file must sit directly inside $base.
if (strpos($path, $base . DIRECTORY_SEPARATOR) !== 0) {
    sheet_fail('File not found.');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
if (!in_array($ext, ['xlsx', 'csv'], true)) {
    sheet_fail('This file type cannot be previewed as a spreadsheet.');
}
if (filesize($path) > MAX_BYTES) {
    sheet_fail('File is too large to preview (limit 20 MB).');
}

// ---------------------------------------------------------------------
// XLSX engine
// ---------------------------------------------------------------------
function sp_col_index($letter)
{
    $c = 0;
    for ($j = 0; $j < strlen($letter); $j++) {
        $c = $c * 26 + (ord($letter[$j]) - ord('A') + 1);
    }
    return $c - 1;
}

/** Concatenate a shared-string entry, which may be plain or rich-text runs. */
function sp_shared_text($si)
{
    if (isset($si->t)) {
        return (string)$si->t;
    }
    $t = '';
    if (isset($si->r)) {
        foreach ($si->r as $r) {
            $t .= (string)$r->t;
        }
    }
    return $t;
}

/**
 * Map style index -> true when that style renders as a date/time.
 * Conservative: only formats we positively recognise become dates, so an
 * unrecognised format falls through and the raw serial number is shown
 * rather than a wrong date.
 */
function sp_date_styles($zip)
{
    $dateStyles = [];
    $xml = $zip->getFromName('xl/styles.xml');
    if ($xml === false) {
        return $dateStyles;
    }
    $sx = @simplexml_load_string($xml);
    if ($sx === false || !isset($sx->cellXfs->xf)) {
        return $dateStyles;
    }

    $customDate = [];
    if (isset($sx->numFmts->numFmt)) {
        foreach ($sx->numFmts->numFmt as $nf) {
            $code = (string)$nf['formatCode'];
            // Strip literals/colour codes, then look for date/time tokens.
            $stripped = preg_replace('/\[[^\]]*\]|"[^"]*"/', '', $code);
            $stripped = preg_replace('/\\\\./', '', $stripped);
            if (preg_match('/[dmyhs]/i', $stripped)) {
                $customDate[(string)$nf['numFmtId']] = true;
            }
        }
    }

    $builtin = [14,15,16,17,18,19,20,21,22,27,30,36,45,46,47,50,57];

    $i = 0;
    foreach ($sx->cellXfs->xf as $xf) {
        $id = isset($xf['numFmtId']) ? (int)(string)$xf['numFmtId'] : 0;
        if (in_array($id, $builtin, true) || isset($customDate[(string)$id])) {
            $dateStyles[$i] = true;
        }
        $i++;
    }
    return $dateStyles;
}

/**
 * Excel serial -> formatted string.
 *
 * Excel's 1900 date system treats 1900 as a leap year, so serial 60 is a
 * phantom 1900-02-29. The usual "1899-12-30 + N" identity is therefore
 * correct only from serial 61 onwards; below that the base shifts by a day.
 */
function sp_serial_to_text($serial)
{
    $serial = (float)$serial;
    $days   = (int)floor($serial);
    $frac   = $serial - $days;

    if ($days === 60) {
        return '1900-02-29'; // the phantom day Excel still accepts
    }
    $base = ($days > 0 && $days < 60) ? 31 : 30;   // 1899-12-31 vs 1899-12-30

    $epoch = gmmktime(0, 0, 0, 12, 0, 1899);
    $secs  = $epoch + (($base + $days) * 86400) + (int)round($frac * 86400);

    $hasTime = $frac > 0.0000001;
    if ($days === 0) {
        return gmdate('H:i', $secs);
    }
    return gmdate('Y-m-d', $secs) . ($hasTime ? ' ' . gmdate('H:i', $secs) : '');
}

function sp_number_to_text($num)
{
    if (is_string($num) && $num === '') {
        return '';
    }
    if (!is_numeric($num)) {
        return (string)$num;
    }
    $f = (float)$num;
    if (abs($f - round($f)) < 0.0000001 && abs($f) < 1e15) {
        return (string)(int)round($f);
    }
    return rtrim(rtrim(sprintf('%.10F', $f), '0'), '.');
}

function sp_parse_xlsx($path)
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        sheet_fail('Unable to open the XLSX file.', 500);
    }

    // --- shared strings ---
    $shared = [];
    $sxRaw = $zip->getFromName('xl/sharedStrings.xml');
    if ($sxRaw !== false) {
        $sx = @simplexml_load_string($sxRaw);
        if ($sx !== false) {
            foreach ($sx->si as $si) {
                $shared[] = sp_shared_text($si);
            }
        }
    }

    $dateStyles = sp_date_styles($zip);

    // --- sheet name -> part path ---
    $sheets = [];
    $wb = @simplexml_load_string((string)$zip->getFromName('xl/workbook.xml'));
    if ($wb !== false && isset($wb->sheets->sheet)) {
        $rns = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $ridMap = [];
        $rels = @simplexml_load_string((string)$zip->getFromName('xl/_rels/workbook.xml.rels'));
        if ($rels !== false) {
            foreach ($rels->Relationship as $r) {
                $ridMap[(string)$r['Id']] = (string)$r['Target'];
            }
        }
        foreach ($wb->sheets->sheet as $s) {
            $rid = (string)$s->attributes($rns)['id'];
            $target = isset($ridMap[$rid]) ? $ridMap[$rid] : '';
            if ($target === '') {
                continue;
            }
            $sp = ($target[0] === '/') ? ltrim($target, '/') : 'xl/' . ltrim($target, '/');
            $sheets[] = ['name' => (string)$s['name'], 'sp' => $sp];
        }
    }

    // Fallback for workbooks with an unreadable/absent workbook.xml
    if (!$sheets) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $n = $zip->getNameIndex($i);
            if (preg_match('#^xl/worksheets/sheet\d+\.xml$#', (string)$n)) {
                $sheets[] = ['name' => 'Sheet' . count($sheets) + 1, 'sp' => $n];
            }
        }
        usort($sheets, function ($a, $b) {
            return strnatcasecmp($a['sp'], $b['sp']);
        });
    }

    if (!$sheets) {
        $zip->close();
        sheet_fail('No worksheets found in the XLSX file.', 500);
    }
    $sheetTruncated = false;
    if (count($sheets) > MAX_SHEETS) {
        $sheets = array_slice($sheets, 0, MAX_SHEETS);
        $sheetTruncated = true;
    }

    $out       = [];
    $truncated = $sheetTruncated;

    foreach ($sheets as $sheet) {
        $xml = $zip->getFromName($sheet['sp']);
        if ($xml === false) {
            $out[] = ['name' => $sheet['name'], 'rows' => [], 'cols' => 0, 'truncated' => false];
            continue;
        }
        $sx = @simplexml_load_string($xml);
        if ($sx === false) {
            $out[] = ['name' => $sheet['name'], 'rows' => [], 'cols' => 0, 'truncated' => false];
            continue;
        }

        $rows    = [];
        $rowSeen = 0;
        $rowTrunc = false;

        if (isset($sx->sheetData->row)) {
            foreach ($sx->sheetData->row as $row) {
                if ($rowSeen >= MAX_ROWS) {
                    $rowTrunc = true;
                    break;
                }
                $cells = [];
                if (isset($row->c)) {
                    foreach ($row->c as $c) {
                        $ref = (string)$c['r'];
                        if (!preg_match('/^([A-Z]+)(\d+)$/', $ref, $m)) {
                            continue;
                        }
                        $col  = sp_col_index($m[1]);
                        if ($col >= MAX_COLS) {
                            $rowTrunc = true;
                            continue;
                        }
                        $type = (string)$c['t'];

                        if ($type === 's') {
                            $idx = isset($c->v) ? (int)(string)$c->v : -1;
                            $val = ($idx >= 0 && isset($shared[$idx])) ? $shared[$idx] : '';
                        } elseif ($type === 'inlineStr') {
                            $val = isset($c->is->t) ? (string)$c->is->t : '';
                            if ($val === '' && isset($c->is->r)) {
                                foreach ($c->is->r as $r) {
                                    $val .= (string)$r->t;
                                }
                            }
                        } elseif ($type === 'b') {
                            $val = (isset($c->v) && (string)$c->v === '1') ? 'TRUE' : 'FALSE';
                        } elseif ($type === 'e') {
                            $val = isset($c->v) ? (string)$c->v : '';
                        } else {
                            // Numeric (or a formula's cached value).
                            $raw = isset($c->v) ? (string)$c->v : '';
                            $sIdx = isset($c['s']) ? (int)(string)$c['s'] : -1;
                            if ($raw !== '' && isset($dateStyles[$sIdx]) && is_numeric($raw)) {
                                $val = sp_serial_to_text($raw);
                            } else {
                                $val = sp_number_to_text($raw);
                            }
                        }
                        $cells[$col] = $val;
                    }
                }
                $rows[]  = $cells;
                $rowSeen++;
            }
        }

        // Normalise each row to a dense list so the table renders cleanly.
        // Use the highest populated column index, not count() (a sparse row
        // with cells in columns 0 and 9 must not collapse to 2 columns).
        $cols = 0;
        foreach ($rows as $cells) {
            if ($cells) {
                $cols = max($cols, max(array_keys($cells)) + 1);
            }
        }
        $cols = min($cols, MAX_COLS);
        $dense = [];
        foreach ($rows as $cells) {
            $line = [];
            for ($c = 0; $c < $cols; $c++) {
                $line[] = isset($cells[$c]) ? $cells[$c] : '';
            }
            $dense[] = $line;
        }

        if ($rowTrunc) {
            $truncated = true;
        }

        $out[] = [
            'name'      => $sheet['name'],
            'rows'      => $dense,
            'cols'      => $cols,
            'truncated' => $rowTrunc,
        ];
    }

    $zip->close();
    return ['sheets' => $out, 'truncated' => $truncated];
}

// ---------------------------------------------------------------------
// CSV engine
// ---------------------------------------------------------------------
function sp_parse_csv($path)
{
    $fh = @fopen($path, 'rb');
    if ($fh === false) {
        sheet_fail('Unable to open the CSV file.', 500);
    }

    // UTF-8 BOM
    $bom = fread($fh, 3);
    $dataStart = 0;
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($fh);
    } else {
        $dataStart = 3;
    }

    // Delimiter sniff: pick whichever candidate appears most on line 1.
    $first = fgets($fh);
    if ($first === false) {
        fclose($fh);
        return ['sheets' => [['name' => 'CSV', 'rows' => [], 'cols' => 0, 'truncated' => false]], 'truncated' => false];
    }
    fseek($fh, $dataStart);

    $best = ',';
    $bestCount = 0;
    foreach ([',', ';', "\t", '|'] as $cand) {
        $n = substr_count($first, $cand);
        if ($n > $bestCount) {
            $bestCount = $n;
            $best = $cand;
        }
    }

    $rows = [];
    $truncated = false;
    $n = 0;
    while (($fields = fgetcsv($fh, 0, $best)) !== false) {
        if ($n >= MAX_ROWS) {
            $truncated = true;
            break;
        }
        if (count($fields) > MAX_COLS) {
            $fields = array_slice($fields, 0, MAX_COLS);
            $truncated = true;
        }
        $rows[] = array_map(function ($v) {
            return (string)$v;
        }, $fields);
        $n++;
    }
    fclose($fh);

    $cols = 0;
    foreach ($rows as $r) {
        $cols = max($cols, count($r));
    }

    return ['sheets' => [['name' => 'CSV', 'rows' => $rows, 'cols' => $cols, 'truncated' => $truncated]], 'truncated' => $truncated];
}

// ---------------------------------------------------------------------
// Dispatch
// ---------------------------------------------------------------------
$result = ($ext === 'csv') ? sp_parse_csv($path) : sp_parse_xlsx($path);

echo json_encode([
    'success'   => true,
    'file'      => $name,
    'ext'       => $ext,
    'sheets'    => $result['sheets'],
    'truncated' => $result['truncated'],
]);
