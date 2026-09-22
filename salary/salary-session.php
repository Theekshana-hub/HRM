<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS salary_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) DEFAULT NULL,
    session_name VARCHAR(100) DEFAULT NULL,
    salary_type VARCHAR(50) DEFAULT NULL,
    attendance_start DATE DEFAULT NULL,
    attendance_end DATE DEFAULT NULL,
    ot_start DATE DEFAULT NULL,
    ot_end DATE DEFAULT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed sample if empty
$cnt = 0;
$cr = @$conn->query("SELECT COUNT(*) AS c FROM salary_sessions");
if ($cr) $cnt = (int)$cr->fetch_assoc()['c'];
if ($cnt === 0) {
    $samples = [
        ['Sipway Campus Pvt Ltd', 'september', 'Salary', '2026-09-01', '2026-09-30', '2026-09-01', '2026-09-30', 'salary'],
        ['Sipway Campus Pvt Ltd', 'August 2026', 'Salary', '2026-08-01', '2026-08-31', '2026-08-01', '2026-08-31', 'salary'],
        ['Sipway Campus Pvt Ltd', 'July 2026', 'Salary', '2026-07-01', '2026-07-31', '2026-07-01', '2026-07-31', 'Salary'],
        ['Sipway Campus Pvt Ltd', 'December Arreas - 2025', 'Salary', '2025-12-01', '2025-12-31', '2025-12-01', '2025-12-31', 'for Labour'],
        ['Sipway Campus Pvt Ltd', 'June 2026', 'Salary', '2026-06-01', '2026-06-30', '2026-06-01', '2026-06-30', 'Salary session'],
        ['Sipway Campus Pvt Ltd', 'May 2026', 'Salary', '2026-05-01', '2026-05-31', '2026-05-01', '2026-05-31', 'Salary session'],
        ['Sipway Campus Pvt Ltd', 'April 2026', 'Salary', '2026-04-01', '2026-04-30', '2026-04-01', '2026-04-30', 'Salary session'],
        ['Sipway Campus Pvt Ltd', 'March 2026', 'Salary', '2026-03-01', '2026-03-31', '2026-03-01', '2026-03-31', 'Salary session'],
        ['Sipway Campus Pvt Ltd', 'February 2026', 'Salary', '2026-02-01', '2026-02-28', '2026-02-01', '2026-02-28', 'Salary session'],
        ['Sipway Campus Pvt Ltd', 'January 2026', 'Salary', '2026-01-01', '2026-01-31', '2026-01-01', '2026-01-31', 'Salary'],
        ['Sipway Campus Pvt Ltd', 'December 2025', 'Salary', '2025-12-01', '2025-12-31', '2025-12-01', '2025-12-31', 'Salary session'],
        ['Sipway Campus Pvt Ltd', 'November 2025', 'Salary', '2025-11-01', '2025-11-30', '2025-11-01', '2025-11-30', 'Salary session'],
        ['Sipway Campus Pvt Ltd', 'October 2025', 'Salary', '2025-10-01', '2025-10-31', '2025-10-01', '2025-10-31', 'Salary session'],
        ['Sipway Campus Pvt Ltd', 'September 2025', 'Salary', '2025-09-01', '2025-09-30', '2025-09-01', '2025-09-30', 'September 2025'],
    ];
    $st = $conn->prepare("INSERT INTO salary_sessions (company_name, session_name, salary_type, attendance_start, attendance_end, ot_start, ot_end, note) VALUES (?,?,?,?,?,?,?,?)");
    foreach ($samples as $s) {
        $st->bind_param("ssssssss", $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7]);
        $st->execute();
    }
    $st->close();
}

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_row = null;
if ($edit_id > 0) {
    $st = $conn->prepare("SELECT * FROM salary_sessions WHERE id = ?");
    $st->bind_param("i", $edit_id);
    $st->execute();
    $edit_row = $st->get_result()->fetch_assoc();
    $st->close();
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $company = trim($_POST['company_name'] ?? '');
    $session = trim($_POST['session_name'] ?? '');
    $stype   = trim($_POST['salary_type'] ?? '');
    $a_start = $_POST['attendance_start'] ?: null;
    $a_end   = $_POST['attendance_end'] ?: null;
    $o_start = $_POST['ot_start'] ?: null;
    $o_end   = $_POST['ot_end'] ?: null;
    $note    = trim($_POST['note'] ?? '');

    if ($company && $session && $stype) {
        if ($id > 0) {
            $st = $conn->prepare("UPDATE salary_sessions SET company_name=?, session_name=?, salary_type=?, attendance_start=?, attendance_end=?, ot_start=?, ot_end=?, note=? WHERE id=?");
            $st->bind_param("ssssssssi", $company, $session, $stype, $a_start, $a_end, $o_start, $o_end, $note, $id);
        } else {
            $st = $conn->prepare("INSERT INTO salary_sessions (company_name, session_name, salary_type, attendance_start, attendance_end, ot_start, ot_end, note) VALUES (?,?,?,?,?,?,?,?)");
            $st->bind_param("ssssssss", $company, $session, $stype, $a_start, $a_end, $o_start, $o_end, $note);
        }
        $st->execute();
        $st->close();
        header("Location: salary-session.php?saved=1");
        exit;
    }
}

// Delete
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $conn->query("DELETE FROM salary_sessions WHERE id = $did");
    header("Location: salary-session.php?deleted=1");
    exit;
}

// Search
$filter_company = $_POST['filter_company'] ?? $_GET['filter_company'] ?? '';
$filter_session = $_POST['filter_session'] ?? $_GET['filter_session'] ?? '';
$searched = ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') || $filter_company || $filter_session || true;

$rows = [];
$sql = "SELECT * FROM salary_sessions WHERE 1=1";
$params = []; $types = '';
if ($filter_company !== '') {
    $sql .= " AND company_name = ?";
    $params[] = $filter_company; $types .= 's';
}
if ($filter_session !== '') {
    $sql .= " AND session_name LIKE ?";
    $params[] = '%' . $filter_session . '%'; $types .= 's';
}
$sql .= " ORDER BY id DESC LIMIT 200";
$st = $conn->prepare($sql);
if ($params) $st->bind_param($types, ...$params);
$st->execute();
$res = $st->get_result();
while ($r = $res->fetch_assoc()) $rows[] = $r;
$st->close();

// Companies list
$companies = [];
$cr = @$conn->query("SELECT company_name FROM companies ORDER BY company_name");
if ($cr) while ($c = $cr->fetch_assoc()) $companies[] = $c['company_name'];
if (empty($companies)) {
    $companies = ['Sipway Campus Pvt Ltd', 'Sipway Higher Education Pvt Ltd'];
}

// Session names for filter
$sessionNames = [];
$sr = $conn->query("SELECT DISTINCT session_name FROM salary_sessions ORDER BY session_name");
if ($sr) while ($s = $sr->fetch_assoc()) $sessionNames[] = $s['session_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Salary Session | SMART HRIS™</title>
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
.container{max-width:1000px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:20px;color:#1e293b}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px 28px;margin-bottom:22px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.form-grid{display:grid;grid-template-columns:160px 1fr;gap:12px 16px;align-items:center;max-width:720px}
.form-grid .full-row{grid-column:1/-1;display:grid;grid-template-columns:160px 1fr 140px 1fr;gap:12px 12px;align-items:center}
label{font-size:13px;font-weight:500;color:#334155}
label .req{color:#ef4444}
input[type="text"],input[type="date"],select{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff;width:100%
}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}

.btn-row{display:flex;gap:10px;margin-top:18px}
.btn{height:38px;padding:0 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px}
.btn-save{background:#2563eb;color:#fff}.btn-save:hover{background:#1d4ed8}
.btn-new{background:#e2e8f0;color:#475569}.btn-new:hover{background:#cbd5e1}
.btn-search{background:#2563eb;color:#fff;margin:12px auto;display:flex}.btn-search:hover{background:#1d4ed8}

.search-box{background:#fff;border:1px solid var(--border);border-radius:12px;padding:20px 28px;margin-bottom:18px}
.search-grid{display:grid;grid-template-columns:100px 1fr;gap:12px 16px;align-items:center;max-width:520px}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:12.5px}
thead{background:#f8fafc}
th{padding:11px 10px;text-align:left;font-weight:600;color:#334155;border-bottom:2px solid #e2e8f0;white-space:nowrap}
td{padding:10px;border-bottom:1px solid #f1f5f9;color:#334155}
tr:nth-child(even){background:#f8fafc}
tr:hover{background:#eff6ff}
.edit-btn{color:#f59e0b;background:none;border:none;cursor:pointer;font-size:14px}
.edit-btn:hover{color:#d97706}
.footer-count{padding:10px 14px;background:#f1f5f9;font-size:12.5px;color:#64748b;border-top:1px solid var(--border)}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}

@media(max-width:700px){
    .form-grid,.form-grid .full-row,.search-grid{grid-template-columns:1fr}
    .search-nav,.welcome{display:none}
}
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
<h1 class="page-title">Create Salary Session</h1>

<?php if (isset($_GET['saved'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Salary session saved!</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Session deleted.</div><?php endif; ?>

<!-- Create Form -->
<div class="form-card">
<form method="POST">
<input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?= (int)($edit_row['id'] ?? 0) ?>">

<div class="form-grid">
    <label>Company <span class="req">*</span></label>
    <select name="company_name" required>
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= ($edit_row['company_name'] ?? '') === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Session Name <span class="req">*</span></label>
    <input type="text" name="session_name" required value="<?= htmlspecialchars($edit_row['session_name'] ?? '') ?>" placeholder="e.g. September 2026">

    <label>Salary Type <span class="req">*</span></label>
    <select name="salary_type" required>
        <option value="">-select-</option>
        <?php foreach (['Salary', 'Arrears', 'Bonus', 'Advance'] as $t): ?>
        <option value="<?= $t ?>" <?= ($edit_row['salary_type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
    </select>

    <div class="full-row">
        <label>Attendance Session Start <span class="req">*</span></label>
        <input type="date" name="attendance_start" required value="<?= htmlspecialchars($edit_row['attendance_start'] ?? '') ?>">
        <label>Attendance Session End <span class="req">*</span></label>
        <input type="date" name="attendance_end" required value="<?= htmlspecialchars($edit_row['attendance_end'] ?? '') ?>">
    </div>

    <div class="full-row">
        <label>OT Session Start <span class="req">*</span></label>
        <input type="date" name="ot_start" required value="<?= htmlspecialchars($edit_row['ot_start'] ?? '') ?>">
        <label>OT Session End <span class="req">*</span></label>
        <input type="date" name="ot_end" required value="<?= htmlspecialchars($edit_row['ot_end'] ?? '') ?>">
    </div>

    <label>Note <span class="req">*</span></label>
    <input type="text" name="note" required value="<?= htmlspecialchars($edit_row['note'] ?? '') ?>" placeholder="Note">
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-save"><i class="fas fa-save"></i> Save</button>
    <a href="salary-session.php" class="btn btn-new"><i class="fas fa-file"></i> New</a>
</div>
</form>
</div>

<!-- Search -->
<div class="search-box">
<form method="POST">
<input type="hidden" name="action" value="search">
<div class="search-grid">
    <label>Company</label>
    <select name="filter_company">
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= $filter_company === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Session</label>
    <select name="filter_session">
        <option value="">-select session-</option>
        <?php foreach ($sessionNames as $sn): ?>
        <option value="<?= htmlspecialchars($sn) ?>" <?= $filter_session === $sn ? 'selected' : '' ?>><?= htmlspecialchars($sn) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<button type="submit" class="btn btn-search"><i class="fas fa-search"></i> Search</button>
</form>
</div>

<!-- Results -->
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Row #</th>
<th>Session ID</th>
<th>Session Name</th>
<th>Attendance From Date</th>
<th>Attendance To Date</th>
<th>Salary Type</th>
<th>Note</th>
<th>Company Name</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($rows)): ?>
<tr><td colspan="9" style="text-align:center;padding:28px;color:#94a3b8">No sessions found.</td></tr>
<?php else: $i = 1; foreach ($rows as $r): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= (int)$r['id'] ?></td>
<td><?= htmlspecialchars($r['session_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['attendance_start'] ?? '') ?></td>
<td><?= htmlspecialchars($r['attendance_end'] ?? '') ?></td>
<td><?= htmlspecialchars($r['salary_type'] ?? '') ?></td>
<td><?= htmlspecialchars($r['note'] ?? '') ?></td>
<td><?= htmlspecialchars($r['company_name'] ?? '') ?></td>
<td>
    <a href="?edit=<?= (int)$r['id'] ?>" class="edit-btn" title="Edit"><i class="fas fa-pen"></i></a>
</td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
<div class="footer-count"><?= count($rows) ?> Records Found</div>
</div>
</main>
</body>
</html>