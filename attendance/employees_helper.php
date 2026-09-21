<?php
/**
 * Load employees from smart_hris.employees table
 */
function load_employees($conn, $limit = 500) {
    $list = [];
    if (!$conn) return $list;

    // Check table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'employees'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        return $list;
    }

    // Get available columns
    $cols = [];
    $cr = $conn->query("SHOW COLUMNS FROM employees");
    if ($cr) {
        while ($c = $cr->fetch_assoc()) {
            $cols[] = $c['Field'];
        }
    }

    $select = ['emp_no'];
    foreach (['first_name', 'last_name', 'full_name', 'name_with_initials', 'office_use_name', 'initials'] as $col) {
        if (in_array($col, $cols)) $select[] = $col;
    }

    $sql = "SELECT " . implode(', ', $select) . " FROM employees ORDER BY emp_no ASC LIMIT " . (int)$limit;
    $r = $conn->query($sql);

    if (!$r) {
        // Fallback: only emp_no
        $r = $conn->query("SELECT emp_no FROM employees ORDER BY emp_no ASC LIMIT " . (int)$limit);
    }

    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $emp_no = $row['emp_no'] ?? '';
            if ($emp_no === '') continue;

            $name = '';
            if (!empty($row['full_name'])) $name = $row['full_name'];
            elseif (!empty($row['name_with_initials'])) $name = $row['name_with_initials'];
            elseif (!empty($row['office_use_name'])) $name = $row['office_use_name'];
            else {
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            }
            if ($name === '') $name = $emp_no;

            $list[] = [
                'emp_no' => $emp_no,
                'name'   => $name,
                'label'  => $emp_no . ' - ' . $name
            ];
        }
    }
    return $list;
}

/** Render <option> tags for employee select */
function employee_options($employees_list, $selected = '') {
    $html = '<option value="">- Select Employee -</option>';
    foreach ($employees_list as $e) {
        $val = htmlspecialchars($e['label']);
        $sel = ($selected === $e['label'] || $selected === $e['emp_no']) ? ' selected' : '';
        $html .= '<option value="' . $val . '"' . $sel . '>' . $val . '</option>';
    }
    return $html;
}