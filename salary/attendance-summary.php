<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS attendance_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) DEFAULT NULL,
    session_name VARCHAR(100) DEFAULT NULL,
    emp_no VARCHAR(50) DEFAULT NULL,
    emp_name VARCHAR(150) DEFAULT NULL,
    work_days DECIMAL(6,2) DEFAULT 0,
    present_days DECIMAL(6,2) DEFAULT 0,
    absent_days DECIMAL(6,2) DEFAULT 0,
    leave_days DECIMAL(6,2) DEFAULT 0,
    ot_hours DECIMAL(8,2) DEFAULT 0,
    status VARCHAR(30) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$tab = $_GET['tab'] ?? 'process';
$message = '';
$messageType = '';

// Companies & sessions
$companies = [];
$cr = @$conn->query("SELECT company_name FROM companies ORDER BY company_name");
if ($cr) while ($c = $cr->fetch_assoc()) $companies[] = $c['company_name'];
if (empty($companies)) $companies = ['Sipway Campus Pvt Ltd', 'Sipway Higher Education Pvt Ltd'];

$sessions = [];
$sr = @$conn->query("SELECT DISTINCT session_name FROM salary_sessions ORDER BY id DESC");
if ($sr) while ($s = $sr->fetch_assoc()) $sessions[] = $s['session_name'];
if (empty($sessions)) {
    $sessions = ['September 2026', 'August 2026', 'July 2026', 'June 2026'];
}

// Process summary (demo: create rows from employees)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'process') {
    $company = trim($_POST['company_name'] ?? '');
    $session = trim($_POST['session_name'] ?? '');
    if ($company && $session) {
        // Clear existing for company+session then regenerate demo
        $st = $conn->prepare("DELETE FROM attendance_summary WHERE company_name=? AND session_name=?");
        $st->bind_param("ss", $company, $session);
        $st->execute();
        $st->close();

        $emps = [];
        $er = @$conn->query("SELECT emp_no, full_name, name_with_initials, first_name, last_name FROM employees ORDER BY emp_no LIMIT 50");
        if ($er) while ($e = $er->fetch_assoc()) {
            $name = $e['full_name'] ?: $e['name_with_initials'] ?: trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? ''));
            $emps[] = ['emp_no' => $e['emp_no'], 'name' => $name ?: $e['emp_no']];
        }
        if (empty($emps)) {
            $emps = [
                ['emp_no' => 'EMP001', 'name' => 'Kamal Perera'],
                ['emp_no' => 'EMP002', 'name' => 'Nimal Silva'],
                ['emp_no' => 'EMP003', 'name' => 'Sunil Fernando'],
            ];
        }
        $ins = $conn->prepare("INSERT INTO attendance_summary (company_name, session_name, emp_no, emp_name, work_days, present_days, absent_days, leave_days, ot_hours, status) VALUES (?,?,?,?,?,?,?,?,?,'Pending')");
        foreach ($emps as $e) {
            $wd = 26; $pd = rand(22, 26); $ad = max(0, $wd - $pd - rand(0, 2)); $ld = max(0, $wd - $pd - $ad); $ot = rand(0, 20) + (rand(0, 9) / 10);
            $ins->bind_param("ssssddddd", $company, $session, $e['emp_no'], $e['name'], $wd, $pd, $ad, $ld, $ot);
            $ins->execute();
        }
        $ins->close();
        $message = 'Attendance summary processed for ' . count($emps) . ' employee(s).';
        $messageType = 'success';
        $tab = 'details';
    } else {
        $message = 'Please select Company and Session.';
        $messageType = 'error';
    }
}

// Download Excel (CSV)
if (isset($_GET['download'])) {
    $company = $_GET['company'] ?? '';
    $session = $_GET['session'] ?? '';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_summary.csv"');
    echo "Emp No,Employee Name,Work Days,Present Days,Absent Days,Leave Days,OT Hours,Status,Company,Session\n";
    $sql = "SELECT * FROM attendance_summary WHERE 1=1";
    if ($company) $sql .= " AND company_name='" . $conn->real_escape_string($company) . "'";
    if ($session) $sql .= " AND session_name='" . $conn->real_escape_string($session) . "'";
    $sql .= " ORDER BY emp_no";
    $r = $conn->query($sql);
    if ($r) while ($row = $r->fetch_assoc()) {
        echo '"' . $row['emp_no'] . '","' . $row['emp_name'] . '",' . $row['work_days'] . ',' . $row['present_days'] . ',' . $row['absent_days'] . ',' . $row['leave_days'] . ',' . $row['ot_hours'] . ',"' . $row['status'] . '","' . $row['company_name'] . '","' . $row['session_name'] . "\"\n";
    }
    exit;
}

// Upload CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    $company = trim($_POST['company_name'] ?? '');
    $session = trim($_POST['session_name'] ?? '');
    if (!empty($_FILES['excel_file']['tmp_name']) && $company && $session) {
        $handle = fopen($_FILES['excel_file']['tmp_name'], 'r');
        $header = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;
            $emp_no = trim($row[0] ?? '');
            $emp_name = trim($row[1] ?? '');
            $wd = (float)($row[2] ?? 0);
            $pd = (float)($row[3] ?? 0);
            $ad = (float)($row[4] ?? 0);
            $ld = (float)($row[5] ?? 0);
            $ot = (float)($row[6] ?? 0);
            if ($emp_no === '') continue;
            $st = $conn->prepare("INSERT INTO attendance_summary (company_name, session_name, emp_no, emp_name, work_days, present_days, absent_days, leave_days, ot_hours, status) VALUES (?,?,?,?,?,?,?,?,?,'Pending')");
            $st->bind_param("ssssddddd", $company, $session, $emp_no, $emp_name, $wd, $pd, $ad, $ld, $ot);
            $st->execute();
            $st->close();
            $count++;
        }
        fclose($handle);
        $message = "$count records uploaded.";
        $messageType = 'success';
        $tab = 'details';
    }
}

// Approve selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'approve') {
    $ids = $_POST['ids'] ?? [];
    if (!empty($ids)) {
        $ids = array_map('intval', $ids);
        $in = implode(',', $ids);
        $conn->query("UPDATE attendance_summary SET status='Approved' WHERE id IN ($in)");
        $message = count($ids) . ' record(s) approved.';
        $messageType = 'success';
        $tab = 'approve';
    }
}

// Load details / approve lists
$filter_company = $_POST['filter_company'] ?? $_GET['company'] ?? '';
$filter_session = $_POST['filter_session'] ?? $_GET['session'] ?? '';
$details = [];
$pending = [];

$sql = "SELECT * FROM attendance_summary WHERE 1=1";
$params = []; $types = '';
if ($filter_company) { $sql .= " AND company_name=?"; $params[] = $filter_company; $types .= 's'; }
if ($filter_session) { $sql .= " AND session_name=?"; $params[] = $filter_session; $types .= 's'; }
$sql .= " ORDER BY emp_no LIMIT 500";
$st = $conn->prepare($sql);
if ($params) $st->bind_param($types, ...$params);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) {
    $details[] = $row;
    if (($row['status'] ?? '') !== 'Approved') $pending[] = $row;
}
$st->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Summary | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0;--cyan:#0ea5e9}
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
.page-title{font-size:18px;font-weight:600;margin-bottom:16px;color:#1e293b}

.tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:0}
.tab{padding:10px 16px;font-size:13px;font-weight:500;color:#64748b;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px}
.tab:hover{color:var(--accent)}
.tab.active{color:#2563eb;border-bottom-color:#2563eb;font-weight:600}

.panel{background:#fff;border:1px solid var(--border);border-top:none;border-radius:0 0 12px 12px;padding:24px 28px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.panel-title{font-size:15px;font-weight:600;margin-bottom:18px;color:#1e293b}

.form-row{display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap}
label.fld{font-size:13px;font-weight:500;color:#334155;min-width:90px}
label .req{color:#ef4444}
select{height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;font-size:13px;outline:none;font-family:inherit;background:#fff;min-width:200px}
select:focus{border-color:var(--accent)}

.btn-actions{display:flex;gap:12px;margin-left:auto;align-items:center}
.btn-box{
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
    width:88px;height:72px;border:none;border-radius:10px;cursor:pointer;font-size:11px;font-weight:600;
    color:#fff;font-family:inherit;text-decoration:none;transition:all .2s
}
.btn-box i{font-size:20px}
.btn-blue{background:#2563eb}.btn-blue:hover{background:#1d4ed8;transform:translateY(-2px)}
.btn-cyan{background:var(--cyan)}.btn-cyan:hover{background:#0284c7;transform:translateY(-2px)}
.btn-approve{height:38px;padding:0 22px;background:#16a34a;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;margin-top:14px}
.btn-approve:hover{background:#15803d}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-top:16px}
table{width:100%;border-collapse:collapse;font-size:12.5px}
thead{background:#2563eb;color:#fff}
th{padding:11px 12px;text-align:left;font-weight:500}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.badge{padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600}
.badge.pending{background:#fef3c7;color:#92400e}
.badge.approved{background:#dcfce7;color:#166534}
.alert{padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
.alert.success{background:#dcfce7;color:#166534}
.alert.error{background:#fee2e2;color:#991b1b}
.empty{text-align:center;padding:28px;color:#94a3b8}

@media(max-width:700px){.search-nav,.welcome{display:none}.btn-actions{margin-left:0;margin-top:12px}}
</style>
</head>
<body>
<nav class="navbar">
<div class="nav-left">
<a href="../index.php" class="brand"><div class="brand-icon"><i class="fas fa-th-large"></i></div><span>SMART HRIS <sup style="font-size:8px;opacity:.7">™</sup></span></a>
<a href="index.php" class="nav-btn"><i class="fas fa-arrow-left"></i></a>
<a href="../index.php" class="nav-btn"><i class="fas fa-home"></i></a>
<button class="support-btn">Support <i class="fas fa-chevron-down" style="font-size:9px"></i></button>
</div>
<div class="search-nav"><i class="fas fa-search"></i><input type="text" placeholder="Search..."></div>
<div class="nav-right"><span class="welcome">Welcome <strong>Shakila</strong></span><button class="nav-btn"><i class="fas fa-cog"></i></button><div class="avatar">S</div></div>
</nav>

<main class="container">
<h1 class="page-title">Attendance Summary</h1>

<div class="tabs">
    <a href="?tab=process" class="tab <?= $tab === 'process' ? 'active' : '' ?>">Process Attendance Summary</a>
    <a href="?tab=details" class="tab <?= $tab === 'details' ? 'active' : '' ?>">Attendance Summary Details</a>
    <a href="?tab=approve" class="tab <?= $tab === 'approve' ? 'active' : '' ?>">Approve Attendance Summary</a>
</div>

<div class="panel">
<?php if ($message): ?>
<div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($tab === 'process'): ?>
<h2 class="panel-title">Process Attendance Summary</h2>
<form method="POST" enctype="multipart/form-data" id="processForm">
<input type="hidden" name="action" value="process" id="formAction">
<div class="form-row">
    <label class="fld">Company <span class="req">*</span></label>
    <select name="company_name" id="company_name" required>
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-row">
    <label class="fld">Session <span class="req">*</span></label>
    <select name="session_name" id="session_name" required>
        <option value="">-select-</option>
        <?php foreach ($sessions as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="btn-actions">
        <button type="submit" class="btn-box btn-blue" onclick="document.getElementById('formAction').value='process'">
            <i class="fas fa-sync-alt"></i> Process
        </button>
        <a href="#" class="btn-box btn-cyan" onclick="downloadExcel();return false;">
            <i class="fas fa-download"></i> Download Excel
        </a>
        <label class="btn-box btn-cyan" style="cursor:pointer">
            <i class="fas fa-upload"></i> Upload Excel
            <input type="file" name="excel_file" accept=".csv" style="display:none" onchange="document.getElementById('formAction').value='upload';this.form.submit()">
        </label>
    </div>
</div>
</form>

<?php elseif ($tab === 'details'): ?>
<h2 class="panel-title">Attendance Summary Details</h2>
<form method="GET" class="form-row" style="margin-bottom:16px">
<input type="hidden" name="tab" value="details">
<label class="fld">Company</label>
<select name="company">
    <option value="">-all-</option>
    <?php foreach ($companies as $c): ?>
    <option value="<?= htmlspecialchars($c) ?>" <?= $filter_company === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
    <?php endforeach; ?>
</select>
<label class="fld">Session</label>
<select name="session">
    <option value="">-all-</option>
    <?php foreach ($sessions as $s): ?>
    <option value="<?= htmlspecialchars($s) ?>" <?= $filter_session === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
    <?php endforeach; ?>
</select>
<button type="submit" class="btn-box btn-blue" style="width:auto;height:36px;padding:0 16px;flex-direction:row;gap:6px"><i class="fas fa-search"></i> Filter</button>
</form>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Emp No</th><th>Name</th><th>Work Days</th><th>Present</th><th>Absent</th><th>Leave</th><th>OT Hours</th><th>Status</th><th>Session</th>
</tr>
</thead>
<tbody>
<?php if (empty($details)): ?>
<tr><td colspan="9" class="empty">No summary records. Use Process tab first.</td></tr>
<?php else: foreach ($details as $r): ?>
<tr>
<td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['work_days'] ?? '') ?></td>
<td><?= htmlspecialchars($r['present_days'] ?? '') ?></td>
<td><?= htmlspecialchars($r['absent_days'] ?? '') ?></td>
<td><?= htmlspecialchars($r['leave_days'] ?? '') ?></td>
<td><?= htmlspecialchars($r['ot_hours'] ?? '') ?></td>
<td><span class="badge <?= strtolower($r['status'] ?? 'pending') ?>"><?= htmlspecialchars($r['status'] ?? '') ?></span></td>
<td><?= htmlspecialchars($r['session_name'] ?? '') ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php else: /* approve */ ?>
<h2 class="panel-title">Approve Attendance Summary</h2>
<form method="POST">
<input type="hidden" name="action" value="approve">
<div class="table-wrap">
<table>
<thead>
<tr>
<th><input type="checkbox" onclick="document.querySelectorAll('.row-chk').forEach(c=>c.checked=this.checked)"></th>
<th>Emp No</th><th>Name</th><th>Work Days</th><th>Present</th><th>Absent</th><th>Leave</th><th>OT Hours</th><th>Status</th><th>Session</th>
</tr>
</thead>
<tbody>
<?php if (empty($pending)): ?>
<tr><td colspan="10" class="empty">No pending records to approve.</td></tr>
<?php else: foreach ($pending as $r): ?>
<tr>
<td><input type="checkbox" class="row-chk" name="ids[]" value="<?= (int)$r['id'] ?>"></td>
<td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['work_days'] ?? '') ?></td>
<td><?= htmlspecialchars($r['present_days'] ?? '') ?></td>
<td><?= htmlspecialchars($r['absent_days'] ?? '') ?></td>
<td><?= htmlspecialchars($r['leave_days'] ?? '') ?></td>
<td><?= htmlspecialchars($r['ot_hours'] ?? '') ?></td>
<td><span class="badge pending"><?= htmlspecialchars($r['status'] ?? '') ?></span></td>
<td><?= htmlspecialchars($r['session_name'] ?? '') ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
<?php if (!empty($pending)): ?>
<button type="submit" class="btn-approve" onclick="return confirm('Approve selected?')"><i class="fas fa-check"></i> Approve Selected</button>
<?php endif; ?>
</form>
<?php endif; ?>
</div>
</main>
<script>
function downloadExcel() {
    const c = document.getElementById('company_name').value;
    const s = document.getElementById('session_name').value;
    window.location = '?download=1&company=' + encodeURIComponent(c) + '&session=' + encodeURIComponent(s);
}
</script>
</body>
</html>