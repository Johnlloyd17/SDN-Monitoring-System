<?php
function sdn_backups_dir() {
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'backups';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir;
}

function sdn_backup_name($prefix) {
    $dir = sdn_backups_dir();
    $name = $prefix . date('Y-m-d_Hi') . '.sql';
    $i = 1;
    while (is_file($dir . DIRECTORY_SEPARATOR . $name) && $i < 100) {
        $name = $prefix . date('Y-m-d_Hi') . '-' . $i . '.sql';
        $i++;
    }
    return $name;
}

function sdn_is_backup_filename($name) {
    return (bool) preg_match('/^(sdn_backup_|safety_backup_)[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{4}(-[0-9]+)?\.sql$/i', $name);
}

function sdn_newest_backup() {
    $files = glob(sdn_backups_dir() . DIRECTORY_SEPARATOR . 'sdn_backup_*.sql');
    if (!$files) {
        return null;
    }
    rsort($files);
    return $files[0];
}

function sdn_list_backups() {
    $files = glob(sdn_backups_dir() . DIRECTORY_SEPARATOR . '*.sql');
    if (!$files) {
        return array();
    }
    $list = array();
    foreach ($files as $path) {
        $name = basename($path);
        if (!preg_match('/^(sdn_backup_|safety_backup_)[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{4}(-[0-9]+)?\.sql$/i', $name)) {
            continue;
        }
        $list[] = array(
            'filename' => $name,
            'size' => (int) @filesize($path),
            'mtime' => (int) @filemtime($path),
            'kind' => (strncmp($name, 'safety_', 7) === 0) ? 'safety' : 'backup'
        );
    }
    usort($list, function ($a, $b) {
        if ($a['mtime'] === $b['mtime']) {
            return strcmp($b['filename'], $a['filename']);
        }
        return $b['mtime'] - $a['mtime'];
    });
    return $list;
}

function sdn_db_name($con) {
    $res = mysqli_query($con, 'SELECT DATABASE()');
    if (!$res || !$row = mysqli_fetch_row($res)) {
        return '';
    }
    return $row[0];
}

function sdn_quote_name($name) {
    return '`' . str_replace('`', '``', $name) . '`';
}

function sdn_create_backup($con) {
    $filename = sdn_backup_name('sdn_backup_');
    $path = sdn_backups_dir() . DIRECTORY_SEPARATOR . $filename;

    $res = sdn_dump_database($con, $path);
    if (!$res['ok']) {
        @unlink($path);
        return array('ok' => false, 'error' => $res['error']);
    }
    return array('ok' => true, 'filename' => $filename, 'path' => $path);
}

function sdn_create_safety_backup($con) {
    $filename = sdn_backup_name('safety_backup_');
    $path = sdn_backups_dir() . DIRECTORY_SEPARATOR . $filename;

    $res = sdn_dump_database($con, $path);
    if (!$res['ok']) {
        @unlink($path);
        return array('ok' => false, 'error' => $res['error']);
    }
    return array('ok' => true, 'filename' => $filename, 'path' => $path);
}

function sdn_dump_database($con, $outFile) {
    $handle = @fopen($outFile, 'w');
    if (!$handle) {
        return array('ok' => false, 'error' => 'Could not create the backup file. The folder may not be writable.');
    }

    $db = sdn_db_name($con);
    fwrite($handle, "-- SDN Monitoring System database backup\n");
    fwrite($handle, "-- Marker: SDN_BACKUP_V1\n");
    fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
    fwrite($handle, "-- Database: " . sdn_quote_name($db) . "\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
    fwrite($handle, "SET NAMES utf8mb4;\n\n");

    $tables = array();
    $res = mysqli_query($con, 'SHOW TABLES');
    if (!$res) {
        fclose($handle);
        return array('ok' => false, 'error' => 'Could not read the list of system tables.');
    }
    while ($row = mysqli_fetch_row($res)) {
        $tables[] = $row[0];
    }
    mysqli_free_result($res);

    foreach ($tables as $table) {
        $tq = sdn_quote_name($table);

        fwrite($handle, "DROP TABLE IF EXISTS " . $tq . ";\n\n");

        $create = mysqli_query($con, 'SHOW CREATE TABLE ' . $tq);
        if (!$create) {
            fclose($handle);
            return array('ok' => false, 'error' => 'Could not read the structure of table ' . $table . '.');
        }
        $createRow = mysqli_fetch_row($create);
        fwrite($handle, $createRow[1] . ";\n\n");
        mysqli_free_result($create);

        $data = mysqli_query($con, 'SELECT * FROM ' . $tq);
        if (!$data) {
            fclose($handle);
            return array('ok' => false, 'error' => 'Could not read the data of table ' . $table . '.');
        }
        $numFields = mysqli_num_fields($data);

        fwrite($handle, "-- Data for table " . $table . "\n");

        $batch = array();
        while ($record = mysqli_fetch_row($data)) {
            $values = array();
            for ($j = 0; $j < $numFields; $j++) {
                $value = $record[$j];
                if ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . mysqli_real_escape_string($con, $value) . "'";
                }
            }
            $batch[] = '(' . implode(', ', $values) . ')';
            if (count($batch) >= 200) {
                fwrite($handle, "INSERT INTO " . $tq . " VALUES\n" . implode(",\n", $batch) . ";\n");
                $batch = array();
            }
        }
        if (count($batch) > 0) {
            fwrite($handle, "INSERT INTO " . $tq . " VALUES\n" . implode(",\n", $batch) . ";\n");
        }
        mysqli_free_result($data);

        fwrite($handle, "\n");
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($handle);

    $size = filesize($outFile);
    if ($size === false || $size <= 0) {
        @unlink($outFile);
        return array('ok' => false, 'error' => 'The backup came out empty, so it was not saved. Please try again.');
    }

    return array('ok' => true);
}

function sdn_validate_backup($path) {
    $handle = @fopen($path, 'rb');
    if (!$handle) {
        return false;
    }
    $head = (string) fread($handle, 4096);
    fclose($handle);
    return strpos($head, 'SDN_BACKUP_V1') !== false;
}

function sdn_statement_complete($sql) {
    $len = strlen($sql);
    $inStr = false;
    $inDq = false;
    $inBt = false;
    $esc = false;
    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        if ($esc) {
            $esc = false;
            continue;
        }
        if (($inStr || $inDq) && $ch === '\\') {
            $esc = true;
            continue;
        }
        if ($inStr) {
            if ($ch === "'") {
                if ($i + 1 < $len && $sql[$i + 1] === "'") {
                    $i++;
                } else {
                    $inStr = false;
                }
            }
            continue;
        }
        if ($inDq) {
            if ($ch === '"') {
                if ($i + 1 < $len && $sql[$i + 1] === '"') {
                    $i++;
                } else {
                    $inDq = false;
                }
            }
            continue;
        }
        if ($inBt) {
            if ($ch === '`') {
                if ($i + 1 < $len && $sql[$i + 1] === '`') {
                    $i++;
                } else {
                    $inBt = false;
                }
            }
            continue;
        }
        if ($ch === "'") {
            $inStr = true;
            continue;
        }
        if ($ch === '"') {
            $inDq = true;
            continue;
        }
        if ($ch === '`') {
            $inBt = true;
            continue;
        }
        if ($ch === ';') {
            $rest = trim(substr($sql, $i + 1));
            if ($rest === '' || strncmp($rest, '--', 2) === 0) {
                return true;
            }
        }
    }
    return false;
}

function sdn_restore_database($con, $path) {
    $handle = @fopen($path, 'rb');
    if (!$handle) {
        return array('ok' => false, 'error' => 'Could not read the backup file.');
    }

    @mysqli_query($con, 'SET FOREIGN_KEY_CHECKS=0');
    @mysqli_query($con, 'SET NAMES utf8mb4');

    $buffer = '';
    $err = null;

    while (($line = fgets($handle, 4096)) !== false) {
        if (trim($buffer) === '') {
            $trimmed = ltrim($line);
            if ($trimmed === '' || strncmp($trimmed, '--', 2) === 0) {
                continue;
            }
        }

        $buffer .= $line;

        if (!sdn_statement_complete($buffer)) {
            continue;
        }

        $stmt = trim($buffer);
        $buffer = '';
        if ($stmt === '') {
            continue;
        }

        if (!mysqli_query($con, $stmt)) {
            $err = mysqli_error($con);
            break;
        }
    }
    fclose($handle);

    @mysqli_query($con, 'SET FOREIGN_KEY_CHECKS=1');

    if ($err) {
        return array('ok' => false, 'error' => $err);
    }
    return array('ok' => true);
}

function sdn_log($con, $action) {
    $esc = mysqli_real_escape_string($con, $action);
    @mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . mysqli_real_escape_string($con, $_SESSION['role']) . "', NOW(), '" . $esc . "')");
}