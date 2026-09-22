<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS fix_addition_assign (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assign_type VARCHAR(30) DEFAULT 'employee',
    salary_session VARCHAR(100) DEFAULT NULL,
    reference_no VARCHAR(50) DEFAULT NULL,
    remark VARCHAR(255) DEFAULT NULL,
    company_name VARCHAR(150) DEFAULT NULL,
    emp_no VARCHAR(50) DEFAULT NULL,
    emp_name VARCHAR(150) DEFAULT NULL,
    group_name VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';
$messageType = '';

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $assign_type = $_POST['assign_type'] ?? 'employee';
    $session = trim($_POST['salary_session'] ?? '');
    $ref = trim($_POST['reference_no'] ?? '');
    $remark = trim($_POST['remark'] ?? '');
    $company = trim($_POST['assign_company'] ?? '');
    $emp = trim($_POST['assign_employee'] ?? '');
    $group = trim($_POST['assign_group'] ?? '');

    if ($session !== '') {
        $st = $conn->prepare("INSERT INTO fix_addition_assign (assign_type, salary_session, reference_no, remark, company_name, emp_no, group_name) VALUES (?,?,?,?,?,?,?)");
        $st->bind_param("sssssss", $assign_type, $session, $ref, $remark, $company, $emp, $group);
        $st->execute();
        $st->close();
        $message = 'Fix addition assign saved!';
        $messageType = 'success';
    } else {
        $message = 'Salary Session is required.';
        $messageType = 'error';
    }
}

// Search
$results = [];
$searched = false;
$f_company = $_POST['filter_company'] ?? '';
$f_employee = trim($_POST['filter_employee'] ?? '');
$f_session = $_POST['filter_session'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') {
    $searched = true;
    $sql = "SELECT * FROM fix_addition_assign WHERE 1=1";
    $params = []; $types = '';
    if ($f_company !== '') { $sql .= " AND company_name=?"; $params[] = $f_company; $types .= 's'; }
    if ($f_employee !== '') { $sql .= " AND (emp_no LIKE ? OR emp_name LIKE ?)"; $like = '%'.$f_employee.'%'; $params[] = $like; $params[] = $like; $types .= 'ss'; }
    if ($f_session !== '') { $sql .= " AND salary_session=?"; $params[] = $f_session; $types .= 's'; }
    $sql .= " ORDER BY id DESC LIMIT 200";
    $st = $conn->prepare($sql);
    if ($params) $st->bind_param($types, ...$params);
    $st->execute();
    $res = $st->get_result();
    while ($row = $res->fetch_assoc()) $results[] = $row;
    $st->close();
}

// Dropdown data
$sessions = [];
$sr = @$conn->query("SELECT DISTINCT session_name FROM salary_sessions ORDER BY id DESC");
if ($sr) while ($s = $sr->fetch_assoc()) $sessions[] = $s['session_name'];
if (empty($sessions)) $sessions = ['September 2026', 'August 2026', 'July 2026', 'June 2026'];

$companies = [];
$cr = @$conn->query("SELECT company_name FROM companies ORDER BY company_name");
if ($cr) while ($c = $cr->fetch_assoc()) $companies[] = $c['company_name'];
if (empty($companies)) $companies = ['Sipway Campus Pvt Ltd', 'Sipway Higher Education Pvt Ltd'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fix Addition Assign | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0}
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
.container{max-width:720px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:20px;color:#1e293b;padding-bottom:10px;border-bottom:1px solid var(--border)}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px 28px;margin-bottom:22px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.radio-row{display:flex;gap:40px;margin-bottom:20px;flex-wrap:wrap}
.radio-row label{display:flex;flex-direction:column;align-items:center;gap:6px;font-size:13px;font-weight:500;cursor:pointer;color:#334155}
.radio-row input[type="radio"]{width:16px;height:16px;accent-color:#2563eb}

.field{margin-bottom:14px}
.field label{display:block;font-size:13px;font-weight:500;color:#334155;margin-bottom:6px}
.field label .req{color:#ef4444}
.field input,.field select{
    width:100%;max-width:420px;height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff
}
.field input:focus,.field select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}

.btn-row{display:flex;gap:10px;margin-top:18px}
.btn{height:38px;padding:0 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px}
.btn-save{background:#2563eb;color:#fff}.btn-save:hover{background:#1d4ed8}
.btn-new{background:#e2e8f0;color:#475569;text-decoration:none}.btn-new:hover{background:#cbd5e1}
.btn-search{background:#2563eb;color:#fff;margin:16px auto 0;display:flex}.btn-search:hover{background:#1d4ed8}

.search-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:22px 28px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.search-grid{display:grid;grid-template-columns:100px 1fr;gap:12px 16px;align-items:center;max-width:520px}
.search-grid label{font-size:13px;font-weight:500;color:#334155}
.search-grid select,.search-grid input{height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;font-size:13px;width:100%;font-family:inherit}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-top:16px}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 12px;text-align:left;font-weight:500}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.alert{padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
.alert.success{background:#dcfce7;color:#166534}
.alert.error{background:#fee2e2;color:#991b1b}
.empty{text-align:center;padding:24px;color:#94a3b8}
.extra-fields{display:none;margin-bottom:14px}
.extra-fields.show{display:block}

@media(max-width:700px){.search-nav,.welcome{display:none}.radio-row{gap:20px}}
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
<h1 class="page-title">Fix Addition Assign</h1>

<?php if ($message): ?><div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="form-card">
<form method="POST" id="assignForm">
<input type="hidden" name="action" value="save">

<div class="radio-row">
    <label>
        <input type="radio" name="assign_type" value="employee" checked onchange="toggleType()">
        By Employee
    </label>
    <label>
        <input type="radio" name="assign_type" value="group" onchange="toggleType()">
        By Group
    </label>
    <label>
        <input type="radio" name="assign_type" value="company" onchange="toggleType()">
        Company
    </label>
</div>

<div class="field">
    <label>Salary Session <span class="req">*</span></label>
    <select name="salary_session" required>
        <option value="">-select session-</option>
        <?php foreach ($sessions as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="field">
    <label>Reference No</label>
    <input type="text" name="reference_no" placeholder="Reference number">
</div>

<div class="field">
    <label>Remark</label>
    <input type="text" name="remark" placeholder="Remark">
</div>

<div class="extra-fields show" id="empFields">
    <div class="field">
        <label>Employee</label>
        <input type="text" name="assign_employee" placeholder="Employee no / name">
    </div>
</div>
<div class="extra-fields" id="groupFields">
    <div class="field">
        <label>Group</label>
        <input type="text" name="assign_group" placeholder="Group name">
    </div>
</div>
<div class="extra-fields" id="companyFields">
    <div class="field">
        <label>Company</label>
        <select name="assign_company">
            <option value="">-select-</option>
            <?php foreach ($companies as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-save"><i class="fas fa-save"></i> Save</button>
    <a href="fix-additions-assign.php" class="btn btn-new"><i class="fas fa-file"></i> New</a>
</div>
</form>
</div>

<div class="search-card">
<form method="POST">
<input type="hidden" name="action" value="search">
<div class="search-grid">
    <label>Company</label>
    <select name="filter_company">
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= $f_company === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Employee</label>
    <input type="text" name="filter_employee" value="<?= htmlspecialchars($f_employee) ?>" placeholder="Employee">
    <label>Session</label>
    <select name="filter_session">
        <option value="">-select session-</option>
        <?php foreach ($sessions as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>" <?= $f_session === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<button type="submit" class="btn btn-search"><i class="fas fa-search"></i> Search</button>
</form>

<?php if ($searched): ?>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>ID</th>
<th>Type</th>
<th>Session</th>
<th>Ref No</th>
<th>Company</th>
<th>Employee</th>
<th>Group</th>
<th>Remark</th>
</tr>
</thead>
<tbody>
<?php if (empty($results)): ?>
<tr><td colspan="8" class="empty">No records found.</td></tr>
<?php else: foreach ($results as $r): ?>
<tr>
<td><?= (int)$r['id'] ?></td>
<td><?= htmlspecialchars($r['assign_type'] ?? '') ?></td>
<td><?= htmlspecialchars($r['salary_session'] ?? '') ?></td>
<td><?= htmlspecialchars($r['reference_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['company_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['group_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['remark'] ?? '') ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
</main>
<script>
function toggleType() {
    const t = document.querySelector('input[name="assign_type"]:checked').value;
    document.getElementById('empFields').classList.toggle('show', t === 'employee');
    document.getElementById('groupFields').classList.toggle('show', t === 'group');
    document.getElementById('companyFields').classList.toggle('show', t === 'company');
}
</script>
</body>
</html>