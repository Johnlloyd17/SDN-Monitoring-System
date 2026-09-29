<?php
// Shared Zone Leader (tblzone) view configuration and rendering.
//
// Add and edit show for every signed-in account, but the original page hid the
// checkbox column and the Delete button for staff sessions, so the row
// renderer and the delete endpoint apply the same rule.

if (!function_exists('adm_escape')) {
    function adm_escape($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('adm_id')) {
    /**
     * Validates a posted id. Casting with (int) would quietly turn '201 OR 1=1'
     * into 201, so a malformed id would still act on a real row; a submitted id
     * has to be a plain positive integer or it is discarded.
     */
    function adm_id($raw)
    {
        if (is_array($raw) || is_object($raw) || is_bool($raw)) { return 0; }
        $s = trim((string) $raw);
        if ($s === '' || !preg_match('/^[0-9]+$/', $s)) { return 0; }
        $n = (int) $s;
        return $n > 0 ? $n : 0;
    }
}

if (!function_exists('adm_module')) {
    function adm_module()
    {
        return array(
            'table'   => 'tblzone',
            'heading' => 'Zone Leader',
        );
    }
}

if (!function_exists('adm_can_manage')) {
    /** Whether the signed-in account may add or edit a zone leader. */
    function adm_can_manage()
    {
        return isset($_SESSION['role']);
    }
}

if (!function_exists('adm_can_delete')) {
    /**
     * Whether the signed-in account may delete zone leaders. The page hid the
     * Delete button and the checkbox column for staff sessions, so the delete
     * endpoint has to make the same decision.
     */
    function adm_can_delete()
    {
        return isset($_SESSION['role']) && !isset($_SESSION['staff']);
    }
}

if (!function_exists('adm_columns')) {
    /** The columns shown in the list. The password is never rendered. */
    function adm_columns()
    {
        return array(
            'zone'     => 'Zone',
            'username' => 'Username',
        );
    }
}

if (!function_exists('adm_fetch_rows')) {
    function adm_fetch_rows($con)
    {
        $module = adm_module();
        $res = mysqli_query($con, "SELECT * FROM {$module['table']} ORDER BY zone ASC");
        if (!$res) { return array(); }
        $rows = array();
        while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }
}

if (!function_exists('adm_render_headers')) {
    function adm_render_headers($canDelete)
    {
        $out = '<tr>' . "\n";
        if ($canDelete) {
            $out .= '<th style="width: 20px !important;"><input type="checkbox" class="cbxMain" onchange="checkMain(this)"/></th>' . "\n";
        }
        foreach (adm_columns() as $label) {
            $out .= '<th>' . adm_escape($label) . '</th>' . "\n";
        }
        $out .= '<th style="width: 40px !important;">Option</th>' . "\n";
        $out .= '</tr>' . "\n";
        return $out;
    }
}

if (!function_exists('adm_render_rows')) {
    function adm_render_rows($rows, $canDelete)
    {
        if (count($rows) === 0) {
            $span = count(adm_columns()) + 1 + ($canDelete ? 1 : 0);
            return '<tr><td colspan="' . $span . '" class="text-center">No records found.</td></tr>';
        }

        $out = '';
        foreach ($rows as $row) {
            $id   = (int) $row['id'];
            $out .= '<tr>';
            if ($canDelete) {
                $out .= '<td><input type="checkbox" class="chk_delete" value="' . $id . '" /></td>';
            }
            foreach (adm_columns() as $field => $label) {
                $out .= '<td>' . adm_escape(isset($row[$field]) ? $row[$field] : '') . '</td>';
            }
            $out .= '<td><button class="btn btn-primary btn-sm btn-edit-item" data-id="' . $id . '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button></td>';
            $out .= '</tr>';
        }
        return $out;
    }
}