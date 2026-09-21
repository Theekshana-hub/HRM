<?php
/* ------------------------------------------------------------------
   manual_attendance_upload (Excel/CSV upload eken save wena data)
   display pages walata gannawa. Me file eka display pages thiyena
   folder ekatama danna.
------------------------------------------------------------------- */

function mu_tableExists($conn, $name) {
    $r = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($name) . "'");
    return $r && $r->num_rows > 0;
}

/* employees table eken emp_no => name map ekak (JOIN eka collation error walin ekka nathi wenna PHP eken) */
function mu_employeeMap($conn) {
    $map = [];
    if (!mu_tableExists($conn, 'employees')) return $map;
    try {
        $r = $conn->query("SELECT emp_no, full_name, name_with_initials FROM employees");
        if ($r) while ($e = $r->fetch_assoc()) $map[(string)$e['emp_no']] = $e;
    } catch (Throwable $ex) {}
    return $map;
}

function mu_empName($map, $emp_no) {
    $e = $map[(string)$emp_no] ?? null;
    if (!$e) return '';
    return !empty($e['full_name']) ? $e['full_name'] : ($e['name_with_initials'] ?? '');
}

/* Name / emp_no search term ekata match wena emp_no list eka */
function mu_empNosForTerm($map, $term, $max = 200) {
    $term = mb_strtolower(trim($term));
    $out = [];
    if ($term === '') return $out;
    foreach ($map as $no => $e) {
        $hay = mb_strtolower($no . ' ' . ($e['full_name'] ?? '') . ' ' . ($e['name_with_initials'] ?? ''));
        if (mb_strpos($hay, $term) !== false) {
            $out[] = (string)$no;
            if (count($out) >= $max) break;
        }
    }
    return $out;
}

function mu_where($map, $employee, $from_date, $to_date, &$types, &$params) {
    $sql = '';
    if ($employee !== '') {
        $nos = mu_empNosForTerm($map, $employee);
        $sql .= " AND (emp_no LIKE ?";
        $params[] = '%' . $employee . '%';
        $types .= 's';
        if ($nos) {
            $sql .= " OR emp_no IN (" . implode(',', array_fill(0, count($nos), '?')) . ")";
            foreach ($nos as $n) { $params[] = $n; $types .= 's'; }
        }
        $sql .= ")";
    }
    if ($from_date !== '') { $sql .= " AND att_date >= ?"; $params[] = $from_date; $types .= 's'; }
    if ($to_date !== '')   { $sql .= " AND att_date <= ?"; $params[] = $to_date;   $types .= 's'; }
    return $sql;
}

function mu_run($conn, $sql, $types, $params) {
    $rows = [];
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
    } catch (Throwable $ex) {}
    return $rows;
}

function mu_hours($in, $out) {
    if (!$in || !$out) return '';
    $s = strtotime("1970-01-01 $out") - strtotime("1970-01-01 $in");
    if ($s <= 0) return '';
    return sprintf('%02d:%02d', floor($s / 3600), floor(($s % 3600) / 60));
}

/* Dawasakata row ekak: In Time (CheckIn) + Out Time (CheckOut) - Attendance Display & Dynamic Report walata */
function mu_fetchDaily($conn, $map, $employee = '', $from_date = '', $to_date = '', $limit = 300) {
    if (!mu_tableExists($conn, 'manual_attendance_upload')) return [];
    $types = ''; $params = [];
    $where = "att_date IS NOT NULL" . mu_where($map, $employee, $from_date, $to_date, $types, $params);
    $sql = "SELECT emp_no, att_date AS attendance_date,
                   MIN(CASE WHEN LOWER(REPLACE(status,' ','')) = 'checkin'  THEN att_time END) AS in_time,
                   MAX(CASE WHEN LOWER(REPLACE(status,' ','')) = 'checkout' THEN att_time END) AS out_time
            FROM manual_attendance_upload
            WHERE $where
            GROUP BY emp_no, att_date
            HAVING in_time IS NOT NULL OR out_time IS NOT NULL
            ORDER BY att_date DESC, emp_no ASC
            LIMIT " . (int)$limit;
    $rows = [];
    foreach (mu_run($conn, $sql, $types, $params) as $r) {
        $rows[] = [
            'emp_no'          => $r['emp_no'],
            'emp_name'        => mu_empName($map, $r['emp_no']),
            'attendance_date' => $r['attendance_date'],
            'in_time'         => $r['in_time'],
            'out_time'        => $r['out_time'],
            'work_hours'      => mu_hours($r['in_time'], $r['out_time']),
            'status'          => 'Manual (Upload)',
            'report_type'     => 'Manual Upload',
        ];
    }
    return $rows;
}

/* Punch ekakata row ekak - UnAnalyzed Attendance page ekata */
function mu_fetchPunches($conn, $map, $employee = '', $from_date = '', $to_date = '', $from_time = '', $to_time = '', $limit = 200) {
    if (!mu_tableExists($conn, 'manual_attendance_upload')) return [];
    $types = ''; $params = [];
    $where = "att_date IS NOT NULL AND att_time IS NOT NULL" . mu_where($map, $employee, $from_date, $to_date, $types, $params);
    if ($from_time !== '') { $where .= " AND att_time >= ?"; $params[] = $from_time; $types .= 's'; }
    if ($to_time !== '')   { $where .= " AND att_time <= ?"; $params[] = $to_time;   $types .= 's'; }
    $sql = "SELECT emp_no, att_date, att_time, status
            FROM manual_attendance_upload
            WHERE $where
            ORDER BY att_date DESC, att_time DESC
            LIMIT " . (int)$limit;
    $rows = [];
    foreach (mu_run($conn, $sql, $types, $params) as $r) {
        $rows[] = [
            'emp_no'          => $r['emp_no'],
            'emp_name'        => mu_empName($map, $r['emp_no']),
            'attendance_date' => $r['att_date'],
            'punch_time'      => $r['att_time'],
            'device_location' => 'Manual Upload - ' . ($r['status'] !== '' ? $r['status'] : 'Punch'),
            'status'          => 'Pending',
        ];
    }
    return $rows;
}

/* ------------------------------------------------------------------
   Apply Manual Attendance form eken (manual_attendance table) save karapu ewa
   filter ekka - Attendance Shift Report page ekata
------------------------------------------------------------------- */
function mu_workHours($r) {
    if (empty($r['checkin_date']) || empty($r['in_time']) || empty($r['checkout_date']) || empty($r['out_time'])) return '';
    $in  = strtotime($r['checkin_date'] . ' ' . $r['in_time']);
    $out = strtotime($r['checkout_date'] . ' ' . $r['out_time']);
    if (!$in || !$out || $out <= $in) return '';
    $secs = $out - $in;
    if (!empty($r['breakin_time']) && !empty($r['breakout_time'])) {
        $bi_date = $r['breakin_date'] ?: $r['checkin_date'];
        $bo_date = $r['breakout_date'] ?: $bi_date;
        $b = strtotime($bo_date . ' ' . $r['breakout_time']) - strtotime($bi_date . ' ' . $r['breakin_time']);
        if ($b > 0 && $b < $secs) $secs -= $b;
    }
    return sprintf('%02d:%02d', floor($secs / 3600), floor(($secs % 3600) / 60));
}

function mu_fetchApply($conn, $employee = '', $from_date = '', $to_date = '', $limit = 300) {
    if (!mu_tableExists($conn, 'manual_attendance') || !mu_tableExists($conn, 'employees')) return [];

    $hasEmpId = false;
    $c = $conn->query("SHOW COLUMNS FROM manual_attendance LIKE 'employee_id'");
    if ($c && $c->num_rows > 0) $hasEmpId = true;
    $joinOn   = $hasEmpId ? "e.id = m.employee_id" : "1=0";
    $dateExpr = "COALESCE(m.checkin_date, m.checkout_date, m.breakin_date, m.breakout_date)";

    $sql = "SELECT e.emp_no, e.full_name AS e_name, m.employee_name AS m_name,
                   $dateExpr AS attendance_date,
                   m.checkin_date,  m.checkin_time  AS in_time,
                   m.checkout_date, m.checkout_time AS out_time,
                   m.breakin_date,  m.breakin_time,
                   m.breakout_date, m.breakout_time
            FROM manual_attendance m
            LEFT JOIN employees e ON $joinOn
            WHERE 1=1";
    $types = ''; $params = [];
    if ($employee !== '') {
        $sql .= " AND (e.emp_no LIKE ? OR e.full_name LIKE ? OR e.name_with_initials LIKE ? OR m.employee_name LIKE ?)";
        $like = '%' . $employee . '%';
        array_push($params, $like, $like, $like, $like);
        $types .= 'ssss';
    }
    if ($from_date !== '') { $sql .= " AND $dateExpr >= ?"; $params[] = $from_date; $types .= 's'; }
    if ($to_date !== '')   { $sql .= " AND $dateExpr <= ?"; $params[] = $to_date;   $types .= 's'; }
    $sql .= " ORDER BY attendance_date DESC, in_time ASC LIMIT " . (int)$limit;

    $rows = [];
    foreach (mu_run($conn, $sql, $types, $params) as $r) {
        $rows[] = [
            'emp_no'          => $r['emp_no'],
            'emp_name'        => !empty($r['e_name']) ? $r['e_name'] : $r['m_name'],
            'attendance_date' => $r['attendance_date'],
            'in_time'         => $r['in_time'],
            'out_time'        => $r['out_time'],
            'work_hours'      => mu_workHours($r),
            'status'          => 'Manual',
            'report_type'     => 'Manual Attendance',
        ];
    }
    return $rows;
}