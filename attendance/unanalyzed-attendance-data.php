<?php
include '../db_connect.php';
require_once __DIR__ . '/manual_helpers.php';

$results = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['search'])) {
    $searched = true;
    $employee   = trim($_POST['employee'] ?? $_GET['employee'] ?? '');
    $from_date  = $_POST['from_date'] ?? $_GET['from_date'] ?? '';
    $to_date    = $_POST['to_date'] ?? $_GET['to_date'] ?? '';
    $from_time  = $_POST['from_time'] ?? $_GET['from_time'] ?? '';
    $to_time    = $_POST['to_time'] ?? $_GET['to_time'] ?? '';

    /* ---------- 1. unanalyzed_attendance table eken (kalin wage) ---------- */
    $sql = "SELECT * FROM unanalyzed_attendance WHERE 1=1";
    $params = [];
    $types  = '';

    if ($employee !== '') {
        $sql .= " AND (emp_no LIKE ? OR emp_name LIKE ?)";
        $like = '%' . $employee . '%';
        $params[] = $like;
        $params[] = $like;
        $types .= 'ss';
    }
    if ($from_date !== '') {
        $sql .= " AND attendance_date >= ?";
        $params[] = $from_date;
        $types .= 's';
    }
    if ($to_date !== '') {
        $sql .= " AND attendance_date <= ?";
        $params[] = $to_date;
        $types .= 's';
    }
    if ($from_time !== '') {
        $sql .= " AND punch_time >= ?";
        $params[] = $from_time;
        $types .= 's';
    }
    if ($to_time !== '') {
        $sql .= " AND punch_time <= ?";
        $params[] = $to_time;
        $types .= 's';
    }
    $sql .= " ORDER BY attendance_date DESC, punch_time DESC LIMIT 200";

    $tableCheck = $conn->query("SHOW TABLES LIKE 'unanalyzed_attendance'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $results[] = $row;
            }
            $stmt->close();
        }
    }

    /* ---------- 2. manual_attendance eken (aluth) ----------
       Manual record ekak punch 4k widihata wenas karanawa:
       Check In, Check Out, Break In, Break Out (time eka thiyena ewa witharai) */
    $manualCheck = $conn->query("SHOW TABLES LIKE 'manual_attendance'");
    if ($manualCheck && $manualCheck->num_rows > 0) {
        $hasEmpId = false;
        $colCheck = $conn->query("SHOW COLUMNS FROM manual_attendance LIKE 'employee_id'");
        if ($colCheck && $colCheck->num_rows > 0) $hasEmpId = true;
        $joinOn = $hasEmpId ? "e.id = m.employee_id" : "1=0";

        $parts = [
            ['Check In',  "COALESCE(m.checkin_date, m.checkout_date)",                                  "m.checkin_time"],
            ['Check Out', "COALESCE(m.checkout_date, m.checkin_date)",                                  "m.checkout_time"],
            ['Break In',  "COALESCE(m.breakin_date, m.checkin_date, m.checkout_date)",                  "m.breakin_time"],
            ['Break Out', "COALESCE(m.breakout_date, m.breakin_date, m.checkin_date, m.checkout_date)", "m.breakout_time"],
        ];
        $selects = [];
        foreach ($parts as [$label, $dateExpr, $timeExpr]) {
            $selects[] = "SELECT e.emp_no AS emp_no, e.full_name AS e_name, m.employee_name AS m_name,
                                 $dateExpr AS attendance_date, $timeExpr AS punch_time, '$label' AS punch_type
                          FROM manual_attendance m
                          LEFT JOIN employees e ON $joinOn
                          WHERE $timeExpr IS NOT NULL";
        }
        $msql = "SELECT * FROM (" . implode(" UNION ALL ", $selects) . ") p WHERE 1=1";
        $mparams = [];
        $mtypes  = '';

        if ($employee !== '') {
            $msql .= " AND (emp_no LIKE ? OR e_name LIKE ? OR m_name LIKE ?)";
            $like = '%' . $employee . '%';
            array_push($mparams, $like, $like, $like);
            $mtypes .= 'sss';
        }
        if ($from_date !== '') {
            $msql .= " AND attendance_date >= ?";
            $mparams[] = $from_date;
            $mtypes .= 's';
        }
        if ($to_date !== '') {
            $msql .= " AND attendance_date <= ?";
            $mparams[] = $to_date;
            $mtypes .= 's';
        }
        if ($from_time !== '') {
            $msql .= " AND punch_time >= ?";
            $mparams[] = $from_time;
            $mtypes .= 's';
        }
        if ($to_time !== '') {
            $msql .= " AND punch_time <= ?";
            $mparams[] = $to_time;
            $mtypes .= 's';
        }
        $msql .= " ORDER BY attendance_date DESC, punch_time DESC LIMIT 200";

        $mstmt = $conn->prepare($msql);
        if ($mstmt) {
            if ($mparams) {
                $mstmt->bind_param($mtypes, ...$mparams);
            }
            $mstmt->execute();
            $mres = $mstmt->get_result();
            while ($row = $mres->fetch_assoc()) {
                $results[] = [
                    'emp_no'          => $row['emp_no'],
                    'emp_name'        => !empty($row['e_name']) ? $row['e_name'] : $row['m_name'],
                    'attendance_date' => $row['attendance_date'],
                    'punch_time'      => $row['punch_time'],
                    'device_location' => 'Manual Entry - ' . $row['punch_type'],
                    'status'          => 'Pending',
                ];
            }
            $mstmt->close();
        }
    }

    /* ---------- 2b. manual_attendance_upload (Excel upload) eken ---------- */
    foreach (mu_fetchPunches($conn, mu_employeeMap($conn), $employee, $from_date, $to_date, $from_time, $to_time, 200) as $row) {
        $results[] = $row;
    }

    /* ---------- 3. Deka ekathu karala sort karanawa ---------- */
    usort($results, function ($a, $b) {
        $d = strcmp($b['attendance_date'] ?? '', $a['attendance_date'] ?? '');
        return $d !== 0 ? $d : strcmp($b['punch_time'] ?? '', $a['punch_time'] ?? '');
    });
    $results = array_slice($results, 0, 200);
}

// Employees for autocomplete-style dropdown (from employees table if exists)
$employees = [];
$empCheck = $conn->query("SHOW TABLES LIKE 'employees'");
if ($empCheck && $empCheck->num_rows > 0) {
    $er = $conn->query("SELECT emp_no, name_with_initials, full_name FROM employees ORDER BY emp_no LIMIT 500");
    if ($er) {
        while ($e = $er->fetch_assoc()) {
            $employees[] = $e;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UnAnalyzed Attendance Data | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root {
    --nav-bg: #1a2332;
    --page-bg: #f1f5f9;
    --accent: #3b82f6;
    --border: #e2e8f0;
    --orange: #ea580c;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Inter', system-ui, sans-serif;
    background: var(--page-bg);
    color: #0f172a;
    font-size: 13.5px;
    min-height: 100vh;
}
.navbar {
    background: var(--nav-bg);
    height: 52px;
    display: flex;
    align-items: center;
    padding: 0 18px;
    position: sticky;
    top: 0;
    z-index: 100;
}
.nav-left { display: flex; align-items: center; gap: 14px; }
.brand {
    display: flex; align-items: center; gap: 8px;
    color: #fff; font-weight: 600; font-size: 14px; text-decoration: none;
}
.brand-icon {
    width: 26px; height: 26px;
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    border-radius: 6px; display: grid; place-items: center;
    font-size: 11px; color: #fff;
}
.nav-btn {
    width: 32px; height: 32px; border: none; background: transparent;
    color: #94a3b8; border-radius: 6px; cursor: pointer;
    display: grid; place-items: center; font-size: 13px; text-decoration: none;
}
.nav-btn:hover { background: rgba(255,255,255,.1); color: #fff; }
.support-btn {
    background: transparent; border: none; color: #94a3b8;
    font-size: 12.5px; padding: 5px 8px; border-radius: 6px; cursor: pointer;
}
.search-nav {
    flex: 1; max-width: 340px; margin: 0 20px; position: relative;
}
.search-nav input {
    width: 100%; height: 32px; background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.1); border-radius: 7px;
    padding: 0 12px 0 32px; color: #e2e8f0; font-size: 12.5px; outline: none;
}
.search-nav i {
    position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
    color: #64748b; font-size: 11px;
}
.nav-right { display: flex; align-items: center; gap: 10px; margin-left: auto; }
.welcome { color: #94a3b8; font-size: 12.5px; }
.welcome strong { color: #f1f5f9; font-weight: 500; }
.avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    display: grid; place-items: center; color: #fff; font-weight: 600; font-size: 12px;
}
.container { max-width: 1000px; margin: 0 auto; padding: 22px 20px 50px; }
.page-title { font-size: 18px; font-weight: 600; margin-bottom: 22px; color: #1e293b; }

.form-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 28px 28px 24px;
    margin-bottom: 22px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.field-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}
.field-row label {
    font-size: 13px;
    font-weight: 500;
    color: #334155;
    min-width: 120px;
}
.field-row input[type="text"],
.field-row select {
    height: 36px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    padding: 0 12px;
    font-size: 13px;
    outline: none;
    font-family: inherit;
    width: 240px;
    background: #fff;
}
.field-row input:focus,
.field-row select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
}
.ranges {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px 40px;
    margin-bottom: 8px;
}
.range-block h4 {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 12px;
}
.range-grid {
    display: grid;
    grid-template-columns: 90px 1fr;
    gap: 10px 12px;
    align-items: center;
}
.range-grid label {
    font-size: 12.5px;
    font-weight: 500;
    color: #64748b;
}
.range-grid input {
    height: 36px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    padding: 0 12px;
    font-size: 13px;
    outline: none;
    font-family: inherit;
    width: 100%;
    max-width: 180px;
    background: #fff;
}
.range-grid input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
}
.btn-search {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: var(--orange);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 28px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 18px;
    transition: all 0.2s;
    font-family: inherit;
}
.btn-search:hover {
    background: #c2410c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(234,88,12,0.3);
}
.btn-search i { font-size: 13px; }

.table-wrap {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
table { width: 100%; border-collapse: collapse; font-size: 13px; }
thead { background: #2563eb; color: #fff; }
th {
    padding: 12px 14px;
    text-align: left;
    font-weight: 500;
    font-size: 12.5px;
}
td {
    padding: 11px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}
tr:nth-child(even) { background: #f8fafc; }
tr:hover { background: #eff6ff; }
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
    font-size: 13.5px;
}
.empty-state i {
    font-size: 36px;
    margin-bottom: 12px;
    display: block;
    color: #cbd5e1;
}
.result-count {
    font-size: 12.5px;
    color: #64748b;
    margin-bottom: 10px;
    padding: 0 2px;
}
@media (max-width: 700px) {
    .ranges { grid-template-columns: 1fr; }
    .search-nav, .welcome { display: none; }
    .field-row { flex-direction: column; align-items: flex-start; }
    .field-row input, .field-row select { width: 100%; }
}
</style>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="../index.php" class="brand">
            <div class="brand-icon"><i class="fas fa-th-large"></i></div>
            <span>SMART HRIS <sup style="font-size:8px;opacity:.7">™</sup></span>
        </a>
        <a href="index.php" class="nav-btn" title="Back"><i class="fas fa-arrow-left"></i></a>
        <a href="../index.php" class="nav-btn" title="Home"><i class="fas fa-home"></i></a>
        <button class="support-btn">Support <i class="fas fa-chevron-down" style="font-size:9px"></i></button>
    </div>
    <div class="search-nav">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search...">
    </div>
    <div class="nav-right">
        <span class="welcome">Welcome <strong>Shakila</strong></span>
        <button class="nav-btn"><i class="fas fa-cog"></i></button>
        <div class="avatar">S</div>
    </div>
</nav>

<main class="container">
    <h1 class="page-title">UnAnalyzed Attendance Data</h1>

    <div class="form-card">
        <form method="POST" action="">
            <div class="field-row">
                <label>Select Employee :</label>
                <input type="text" name="employee" id="employee" list="empList"
                       value="<?= htmlspecialchars($_POST['employee'] ?? '') ?>"
                       placeholder="Emp No or Name">
                <datalist id="empList">
                    <?php foreach ($employees as $e): ?>
                    <option value="<?= htmlspecialchars($e['emp_no'] ?? '') ?>">
                        <?= htmlspecialchars(($e['name_with_initials'] ?? $e['full_name'] ?? '') . ' (' . ($e['emp_no'] ?? '') . ')') ?>
                    </option>
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="ranges">
                <div class="range-block">
                    <h4>Date Range :</h4>
                    <div class="range-grid">
                        <label>From Date</label>
                        <input type="date" name="from_date" value="<?= htmlspecialchars($_POST['from_date'] ?? '') ?>">
                        <label>To Date</label>
                        <input type="date" name="to_date" value="<?= htmlspecialchars($_POST['to_date'] ?? '') ?>">
                    </div>
                </div>
                <div class="range-block">
                    <h4>Time Range :</h4>
                    <div class="range-grid">
                        <label>From Time</label>
                        <input type="time" name="from_time" value="<?= htmlspecialchars($_POST['from_time'] ?? '') ?>">
                        <label>To Time</label>
                        <input type="time" name="to_time" value="<?= htmlspecialchars($_POST['to_time'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>

    <?php if ($searched): ?>
    <p class="result-count">
        <i class="fas fa-list"></i>
        <?= count($results) ?> record(s) found
    </p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Emp No</th>
                    <th>Employee Name</th>
                    <th>Date</th>
                    <th>Punch Time</th>
                    <th>Device / Location</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($results)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            No unanalyzed attendance data found for the selected filters.
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($results as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['attendance_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['punch_time'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['device_location'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['status'] ?? 'Pending') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>
</body>
</html>