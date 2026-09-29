<?php
// Shared User Credentials (tblstaff) view configuration and rendering.
//
// The page rendered its buttons for every signed-in account and never hid a
// column, so the same decision is made here for the endpoints: any logged-in
// user may add, edit or delete credentials. Keeping the rule here means the
// page, the ajax list endpoint and the mutation endpoint cannot drift apart.

if (!function_exists('cred_escape')) {
    function cred_escape($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('cred_id')) {
    /**
     * Validates a posted id. Casting with (int) would quietly turn '201 OR 1=1'
     * into 201, so a malformed id would still act on a real row; a submitted id
     * has to be a plain positive integer or it is discarded.
     */
    function cred_id($raw)
    {
        if (is_array($raw) || is_object($raw) || is_bool($raw)) { return 0; }
        $s = trim((string) $raw);
        if ($s === '' || !preg_match('/^[0-9]+$/', $s)) { return 0; }
        $n = (int) $s;
        return $n > 0 ? $n : 0;
    }
}

if (!function_exists('cred_module')) {
    function cred_module()
    {
        return array(
            'table'   => 'tblstaff',
            'heading' => 'User Credentials',
        );
    }
}

if (!function_exists('cred_can_manage')) {
    /** Whether the signed-in account may add, edit or delete credentials. */
    function cred_can_manage()
    {
        return isset($_SESSION['role']);
    }
}

if (!function_exists('cred_columns')) {
    /** The columns shown in the list. The password is never rendered. */
    function cred_columns()
    {
        return array(
            'name'     => 'Name',
            'username' => 'Username',
        );
    }
}

if (!function_exists('cred_fetch_rows')) {
    function cred_fetch_rows($con)
    {
        $module = cred_module();
        $res = mysqli_query($con, "SELECT * FROM {$module['table']} ORDER BY name ASC");
        if (!$res) { return array(); }
        $rows = array();
        while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }
}

if (!function_exists('cred_render_headers')) {
    function cred_render_headers($canManage)
    {
        $out = '<tr>' . "\n";
        if ($canManage) {
            $out .= '<th style="width: 20px !important;"><input type="checkbox" class="cbxMain" onchange="checkMain(this)"/></th>' . "\n";
        }
        foreach (cred_columns() as $label) {
            $out .= '<th>' . cred_escape($label) . '</th>' . "\n";
        }
        if ($canManage) {
            $out .= '<th style="width: 40px !important;">Option</th>' . "\n";
        }
        $out .= '</tr>' . "\n";
        return $out;
    }
}

if (!function_exists('cred_render_rows')) {
    function cred_render_rows($rows, $canManage)
    {
        if (count($rows) === 0) {
            $span = count(cred_columns()) + ($canManage ? 2 : 0);
            return '<tr><td colspan="' . $span . '" class="text-center">No records found.</td></tr>';
        }

        $out = '';
        foreach ($rows as $row) {
            $id   = (int) $row['id'];
            $out .= '<tr>';
            if ($canManage) {
                $out .= '<td><input type="checkbox" class="chk_delete" value="' . $id . '" /></td>';
            }
            foreach (cred_columns() as $field => $label) {
                $out .= '<td>' . cred_escape(isset($row[$field]) ? $row[$field] : '') . '</td>';
            }
            if ($canManage) {
                $out .= '<td><button class="btn btn-primary btn-sm btn-edit-item" data-id="' . $id . '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button></td>';
            }
            $out .= '</tr>';
        }
        return $out;
    }
}