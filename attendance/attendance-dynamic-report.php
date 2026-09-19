<?php
include '../db_connect.php';

$results = [];
$searched = false;
$report_type = $_POST['report'] ?? $_GET['report'] ?? '';

// Export Excel
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="attendance_dynamic_report.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "Emp No\tEmployee Name\tDate\tIn Time\tOut Time\tWork Hours\tStatus\tReport Type\n";

    $tableCheck = $conn->query("SHOW TABLES LIKE 'attendance_dynamic_report'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $sql = "SELECT * FROM attendance_dynamic_report WHERE 1=1";
        $params = [];
        $types = '';
        if ($report_type !== '') {
            $sql .= " AND report_type = ?";
            $params[] = $report_type;
            $types .= 's';
        }
        $sql .= " ORDER BY attendance_date DESC LIMIT 500";
        $stmt = $conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            echo ($r['emp_no'] ?? '') . "\t" .
                 ($r['emp_name'] ?? '') . "\t" .
                 ($r['attendance_date'] ?? '') . "\t" .
                 ($r['in_time'] ?? '') . "\t" .
                 ($r['out_time'] ?? '') . "\t" .
                 ($r['work_hours'] ?? '') . "\t" .
                 ($r['status'] ?? '') . "\t" .
                 ($r['report_type'] ?? '') . "\n";
        }
        $stmt->close();
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    $report_type = $_POST['report'] ?? '';

    $tableCheck = $conn->query("SHOW TABLES LIKE 'attendance_dynamic_report'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $sql = "SELECT * FROM attendance_dynamic_report WHERE 1=1";
        $params = [];
        $types = '';
        if ($report_type !== '') {
            $sql .= " AND report_type = ?";
            $params[] = $report_type;
            $types .= 's';
        }
        $sql .= " ORDER BY attendance_date DESC, emp_no ASC LIMIT 300";
        $stmt = $conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $results[] = $row;
        $stmt->close();
    }
}

$q_report = urlencode($report_type);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Dynamic Report | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0;--orange:#ea580c}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',system-ui,sans-serif;background:var(--page-bg);color:#0f172a;font-size:13.5px;min-height:100vh}
.navbar{background:var(--nav-bg);height:52px;display:flex;align-items:center;padding:0 18px;position:sticky;top:0;z-index:100}
.nav-left{display:flex;align-items:center;gap:14px}
.brand{display:flex;align-items:center;gap:8px;color:#fff;font-weight:600;font-size:14px;text-decoration:none}
.brand-icon{width:26px;height:26px;background:linear-gradient(135deg,#3b82f6,#60a5fa);border-radius:6px;display:grid;place-items:center;font-size:11px;color:#fff}
.nav-btn{width:32px;height:32px;border:none;background:transparent;color:#94a3b8;border-radius:6px;cursor:pointer;display:grid;place-items:center;font-size:13px;text-decoration:none}
.nav-btn:hover{background:rgba(255,255,255,.1);color:#fff}
.support-btn{background:transparent;border:none;color:#94a3b8;font-size:12.5px;padding:5px 8px;border-radius:6px;cursor:pointer}
.search-nav{flex:1;max-width:340px;margin:0 20px;position:relative}
.search-nav input{width:100%;height:32px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);border-radius:7px;padding:0 12px 0 32px;color:#e2e8f0;font-size:12.5px;outline:none}
.search-nav i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#64748b;font-size:11px}
.nav-right{display:flex;align-items:center;gap:10px;margin-left:auto}
.welcome{color:#94a3b8;font-size:12.5px}.welcome strong{color:#f1f5f9;font-weight:500}
.avatar{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#8b5cf6);display:grid;place-items:center;color:#fff;font-weight:600;font-size:12px}
.container{max-width:1000px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:22px;color:#1e293b}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:28px;margin-bottom:22px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.form-row{display:flex;align-items:center;gap:16px;margin-bottom:8px}
.form-row label{font-size:13px;font-weight:500;color:#334155;min-width:70px}
.form-row select{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;width:320px;background:#fff
}
.form-row select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}

.btn-row{display:flex;align-items:flex-end;gap:28px;margin-top:28px}
.btn-search{
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
    width:72px;height:64px;background:var(--orange);color:#fff;border:none;border-radius:10px;
    font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;font-family:inherit
}
.btn-search i{font-size:20px}
.btn-search:hover{background:#c2410c;transform:translateY(-2px);box-shadow:0 6px 16px rgba(234,88,12,.35)}
.btn-excel{
    display:flex;flex-direction:column;align-items:center;gap:6px;
    background:transparent;border:none;cursor:pointer;text-decoration:none;color:#217346;font-size:12px;font-weight:500
}
.btn-excel .excel-icon{
    width:52px;height:52px;background:#217346;border-radius:10px;display:grid;place-items:center;
    color:#fff;font-size:26px;box-shadow:0 3px 10px rgba(33,115,70,.3);transition:all .2s
}
.btn-excel:hover .excel-icon{transform:translateY(-2px);box-shadow:0 6px 16px rgba(33,115,70,.4)}
.btn-excel span{color:#475569}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:12px 14px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:11px 14px;border-bottom:1px solid #f1f5f9;color:#334155}
tr:nth-child(even){background:#f8fafc}
tr:hover{background:#eff6ff}
.empty-state{text-align:center;padding:40px 20px;color:#94a3b8;font-size:13.5px}
.empty-state i{font-size:36px;margin-bottom:12px;display:block;color:#cbd5e1}
.result-count{font-size:12.5px;color:#64748b;margin-bottom:10px}

@media(max-width:700px){
    .form-row{flex-direction:column;align-items:flex-start}
    .form-row select{width:100%}
    .search-nav,.welcome{display:none}
}
</style>
</head>
<body>
<nav class="navbar">
<div class="nav-left">
<a href="../index.php" class="brand"><div class="brand-icon"><i class="fas fa-th-large"></i></div><span>SMART HRIS <sup style="font-size:8px;opacity:.7">™</sup></span></a>
<a href="index.php" class="nav-btn" title="Back"><i class="fas fa-arrow-left"></i></a>
<a href="../index.php" class="nav-btn" title="Home"><i class="fas fa-home"></i></a>
<button class="support-btn">Support <i class="fas fa-chevron-down" style="font-size:9px"></i></button>
</div>
<div class="search-nav"><i class="fas fa-search"></i><input type="text" placeholder="Search..."></div>
<div class="nav-right"><span class="welcome">Welcome <strong>Shakila</strong></span><button class="nav-btn"><i class="fas fa-cog"></i></button><div class="avatar">S</div></div>
</nav>

<main class="container">
<h1 class="page-title">Attendance Dynamic Report</h1>

<div class="form-card">
<form method="POST" action="">
    <div class="form-row">
        <label>Report</label>
        <select name="report" id="report">
            <option value="">-select-</option>
            <option value="Daily Attendance" <?= $report_type === 'Daily Attendance' ? 'selected' : '' ?>>Daily Attendance</option>
            <option value="Monthly Summary" <?= $report_type === 'Monthly Summary' ? 'selected' : '' ?>>Monthly Summary</option>
            <option value="Absent Report" <?= $report_type === 'Absent Report' ? 'selected' : '' ?>>Absent Report</option>
            <option value="Late Report" <?= $report_type === 'Late Report' ? 'selected' : '' ?>>Late Report</option>
            <option value="Overtime Report" <?= $report_type === 'Overtime Report' ? 'selected' : '' ?>>Overtime Report</option>
            <option value="Custom Report" <?= $report_type === 'Custom Report' ? 'selected' : '' ?>>Custom Report</option>
        </select>
    </div>

    <div class="btn-row">
        <button type="submit" class="btn-search">
            <i class="fas fa-search"></i>
            Search
        </button>
        <a href="?export=excel&report=<?= $q_report ?>" class="btn-excel">
            <div class="excel-icon"><i class="fas fa-file-excel"></i></div>
            <span>Export to Excel</span>
        </a>
    </div>
</form>
</div>

<?php if ($searched): ?>
<p class="result-count"><i class="fas fa-list"></i> <?= count($results) ?> record(s) found
<?php if ($report_type): ?> — <strong><?= htmlspecialchars($report_type) ?></strong><?php endif; ?>
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
<th>Work Hours</th>
<th>Status</th>
<th>Report Type</th>
</tr>
</thead>
<tbody>
<?php if (empty($results)): ?>
<tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox"></i>No records found. Select a report type and search.</div></td></tr>
<?php else: foreach ($results as $r): ?>
<tr>
<td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['attendance_date'] ?? '') ?></td>
<td><?= htmlspecialchars($r['in_time'] ?? '') ?></td>
<td><?= htmlspecialchars($r['out_time'] ?? '') ?></td>
<td><?= htmlspecialchars($r['work_hours'] ?? '') ?></td>
<td><?= htmlspecialchars($r['status'] ?? '') ?></td>
<td><?= htmlspecialchars($r['report_type'] ?? '') ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</main>
</body>
</html>