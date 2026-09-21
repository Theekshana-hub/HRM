<?php
include '../db_connect.php';
require_once __DIR__ . '/manual_helpers.php';

$results = [];
$searched = false;

/* Work hours calculate karanawa (break time thibunoth eka aduwenawa) */
function calcWorkHours($r) {
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

/*
 * Ekama employee ekage ekama dawase thiyena rows godak thiyenam,
 * eka row ekakata ekathu karanawa:
 *   - dawase palamuweni in_time eka gannawa (check-in)
 *   - dawase antima out_time eka gannawa (check-out)
 *   - palamuweni break-in eka saha antima break-out eka gannawa
 *   - ithin work_hours eka aluthin calculate karanawa
 */
function consolidateAttendance($rows) {
    $groups = [];

    foreach ($rows as $r) {
        $emp  = $r['emp_no'] ?? '';
        $date = $r['attendance_date'] ?? '';
        if ($date === '') continue;

        $key = $emp . '|' . $date;

        if (!isset($groups[$key])) {
            $groups[$key] = [
                'emp_no'          => $emp,
                'emp_name'        => $r['emp_name'] ?? '',
                'attendance_date' => $date,
                'checkin_date'    => null,
                'in_time'         => null,
                'checkout_date'   => null,
                'out_time'        => null,
                'breakin_date'    => null,
                'breakin_time'    => null,
                'breakout_date'   => null,
                'breakout_time'   => null,
                'status'          => $r['status'] ?? '',
                'source'          => $r['source'] ?? '',
            ];
        }

        $g = &$groups[$key];

        if (empty($g['emp_name']) && !empty($r['emp_name'])) {
            $g['emp_name'] = $r['emp_name'];
        }

        /* palamuweni (earliest) in_time eka */
        if (!empty($r['in_time'])) {
            $inDate = $r['checkin_date'] ?? $date;
            $ts     = strtotime($inDate . ' ' . $r['in_time']);
            $curTs  = !empty($g['in_time']) ? strtotime($g['checkin_date'] . ' ' . $g['in_time']) : null;
            if ($ts && ($curTs === null || $ts < $curTs)) {
                $g['checkin_date'] = $inDate;
                $g['in_time']      = $r['in_time'];
            }
        }

        /* antima (latest) out_time eka */
        if (!empty($r['out_time'])) {
            $outDate = $r['checkout_date'] ?? $date;
            $ts      = strtotime($outDate . ' ' . $r['out_time']);
            $curTs   = !empty($g['out_time']) ? strtotime($g['checkout_date'] . ' ' . $g['out_time']) : null;
            if ($ts && ($curTs === null || $ts > $curTs)) {
                $g['checkout_date'] = $outDate;
                $g['out_time']      = $r['out_time'];
            }
        }

        /* palamuweni break-in eka */
        if (!empty($r['breakin_time'])) {
            $biDate = $r['breakin_date'] ?? $date;
            $ts     = strtotime($biDate . ' ' . $r['breakin_time']);
            $curTs  = !empty($g['breakin_time']) ? strtotime($g['breakin_date'] . ' ' . $g['breakin_time']) : null;
            if ($ts && ($curTs === null || $ts < $curTs)) {
                $g['breakin_date'] = $biDate;
                $g['breakin_time'] = $r['breakin_time'];
            }
        }

        /* antima break-out eka */
        if (!empty($r['breakout_time'])) {
            $boDate = $r['breakout_date'] ?? $date;
            $ts     = strtotime($boDate . ' ' . $r['breakout_time']);
            $curTs  = !empty($g['breakout_time']) ? strtotime($g['breakout_date'] . ' ' . $g['breakout_time']) : null;
            if ($ts && ($curTs === null || $ts > $curTs)) {
                $g['breakout_date']  = $boDate;
                $g['breakout_time']  = $r['breakout_time'];
            }
        }

        if (empty($g['status']) && !empty($r['status'])) {
            $g['status'] = $r['status'];
        }
        if (!empty($r['source']) && $g['source'] !== $r['source']) {
            $g['source'] = $g['source'] ? $g['source'] . '+' . $r['source'] : $r['source'];
        }

        unset($g);
    }

    $out = [];
    foreach ($groups as $g) {
        $g['work_hours'] = calcWorkHours($g);
        $out[] = $g;
    }
    return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    $employee  = trim($_POST['employee'] ?? '');
    $from_date = $_POST['from_date'] ?? '';
    $to_date   = $_POST['to_date'] ?? '';

    /* ---------- 1. attendance_display table eken (kalin wage) ---------- */
    $sql = "SELECT * FROM attendance_display WHERE 1=1";
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
    $sql .= " ORDER BY attendance_date DESC, in_time ASC LIMIT 300";

    $tableCheck = $conn->query("SHOW TABLES LIKE 'attendance_display'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['source'] = 'Attendance';
                $results[] = $row;
            }
            $stmt->close();
        }
    }

    /* ---------- 2. manual_attendance table eken (aluth) ---------- */
    $manualCheck = $conn->query("SHOW TABLES LIKE 'manual_attendance'");
    if ($manualCheck && $manualCheck->num_rows > 0) {
        $hasEmpId = false;
        $colCheck = $conn->query("SHOW COLUMNS FROM manual_attendance LIKE 'employee_id'");
        if ($colCheck && $colCheck->num_rows > 0) $hasEmpId = true;

        // employee_id thiyenawanam employees table ekath ekka join karanawa
        $joinOn   = $hasEmpId ? "e.id = m.employee_id" : "1=0";
        $dateExpr = "COALESCE(m.checkin_date, m.checkout_date, m.breakin_date, m.breakout_date)";

        $msql = "SELECT e.emp_no,
                        e.full_name AS e_name, m.employee_name AS m_name,
                        $dateExpr AS attendance_date,
                        m.checkin_date,  m.checkin_time  AS in_time,
                        m.checkout_date, m.checkout_time AS out_time,
                        m.breakin_date,  m.breakin_time,
                        m.breakout_date, m.breakout_time
                 FROM manual_attendance m
                 LEFT JOIN employees e ON $joinOn
                 WHERE 1=1";
        $mparams = [];
        $mtypes  = '';

        if ($employee !== '') {
            $msql .= " AND (e.emp_no LIKE ? OR e.full_name LIKE ? OR e.name_with_initials LIKE ? OR m.employee_name LIKE ?)";
            $like = '%' . $employee . '%';
            array_push($mparams, $like, $like, $like, $like);
            $mtypes .= 'ssss';
        }
        if ($from_date !== '') {
            $msql .= " AND $dateExpr >= ?";
            $mparams[] = $from_date;
            $mtypes .= 's';
        }
        if ($to_date !== '') {
            $msql .= " AND $dateExpr <= ?";
            $mparams[] = $to_date;
            $mtypes .= 's';
        }
        $msql .= " ORDER BY attendance_date DESC, in_time ASC LIMIT 300";

        $mstmt = $conn->prepare($msql);
        if ($mstmt) {
            if ($mparams) {
                $mstmt->bind_param($mtypes, ...$mparams);
            }
            $mstmt->execute();
            $mres = $mstmt->get_result();
            while ($row = $mres->fetch_assoc()) {
                $row['emp_name']   = !empty($row['e_name']) ? $row['e_name'] : $row['m_name'];
                $row['work_hours'] = calcWorkHours($row);
                $row['status']     = 'Manual';
                $row['source']     = 'Manual';
                $results[] = $row;
            }
            $mstmt->close();
        }
    }

    /* ---------- 2b. manual_attendance_upload (Excel upload) eken ---------- */
    foreach (mu_fetchDaily($conn, mu_employeeMap($conn), $employee, $from_date, $to_date, 300) as $row) {
        $results[] = $row;
    }

    /* ---------- 3. Ekama employee ekage ekama dawase thiyena records ekathu karala,
     *              palamuweni in eka + antima out eka witharak thiyena widihata consolidate karanawa
     * ---------- */
    $results = consolidateAttendance($results);

    /* ---------- 4. Date eken sort karanawa ---------- */
    usort($results, function ($a, $b) {
        $d = strcmp($b['attendance_date'] ?? '', $a['attendance_date'] ?? '');
        return $d !== 0 ? $d : strcmp($a['in_time'] ?? '', $b['in_time'] ?? '');
    });
    $results = array_slice($results, 0, 300);
}

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
<title>Attendance Display | SMART HRIS™</title>
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
.container { max-width: 1100px; margin: 0 auto; padding: 22px 20px 50px; }
.page-title { font-size: 18px; font-weight: 600; margin-bottom: 22px; color: #1e293b; }

.form-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 28px;
    margin-bottom: 22px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    max-width: 560px;
}
.form-grid {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: 14px 16px;
    align-items: center;
}
label {
    font-size: 13px;
    font-weight: 500;
    color: #334155;
}
input[type="text"],
input[type="date"] {
    height: 36px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    padding: 0 12px;
    font-size: 13px;
    outline: none;
    font-family: inherit;
    width: 100%;
    max-width: 320px;
    background: #fff;
}
input:focus {
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
    margin-top: 20px;
    transition: all 0.2s;
    font-family: inherit;
}
.btn-search:hover {
    background: #c2410c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(234,88,12,0.3);
}

.table-wrap {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow-x: auto;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
table { width: 100%; border-collapse: collapse; font-size: 13px; }
thead { background: #2563eb; color: #fff; }
th {
    padding: 12px 14px;
    text-align: left;
    font-weight: 500;
    font-size: 12.5px;
    white-space: nowrap;
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
}
.status-present { color: #16a34a; font-weight: 500; }
.status-absent { color: #dc2626; font-weight: 500; }
.status-late { color: #ea580c; font-weight: 500; }
.status-manual { color: #2563eb; font-weight: 500; }

@media (max-width: 700px) {
    .form-grid { grid-template-columns: 1fr; }
    .search-nav, .welcome { display: none; }
    input[type="text"], input[type="date"] { max-width: 100%; }
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
    <h1 class="page-title">Attendance Display</h1>

    <div class="form-card">
        <form method="POST" action="">
            <div class="form-grid">
                <label>Employee</label>
                <input type="text" name="employee" list="empList"
                       value="<?= htmlspecialchars($_POST['employee'] ?? '') ?>"
                       placeholder="Emp No or Name">
                <datalist id="empList">
                    <?php foreach ($employees as $e): ?>
                    <option value="<?= htmlspecialchars($e['emp_no'] ?? '') ?>">
                        <?= htmlspecialchars(($e['name_with_initials'] ?? $e['full_name'] ?? '') . ' (' . ($e['emp_no'] ?? '') . ')') ?>
                    </option>
                    <?php endforeach; ?>
                </datalist>

                <label>From Date</label>
                <input type="date" name="from_date" value="<?= htmlspecialchars($_POST['from_date'] ?? '') ?>">

                <label>To Date</label>
                <input type="date" name="to_date" value="<?= htmlspecialchars($_POST['to_date'] ?? '') ?>">
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
                    <th>In Time</th>
                    <th>Out Time</th>
                    <th>Break In</th>
                    <th>Break Out</th>
                    <th>Work Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($results)): ?>
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            No attendance records found for the selected filters.
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($results as $r): ?>
                <?php
                    $st = strtolower($r['status'] ?? '');
                    $cls = $st === 'present' ? 'status-present'
                         : ($st === 'absent' ? 'status-absent'
                         : ($st === 'late' ? 'status-late'
                         : (strpos($st, 'manual') === 0 ? 'status-manual' : '')));
                ?>
                <tr>
                    <td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['attendance_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['in_time'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['out_time'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['breakin_time'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['breakout_time'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['work_hours'] ?? '') ?></td>
                    <td class="<?= $cls ?>"><?= htmlspecialchars($r['status'] ?? '') ?></td>
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