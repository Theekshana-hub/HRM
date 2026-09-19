<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS leave_balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_no VARCHAR(50) DEFAULT NULL,
    emp_name VARCHAR(150) DEFAULT NULL,
    year INT DEFAULT NULL,
    leave_type VARCHAR(50) DEFAULT NULL,
    entitled DECIMAL(6,2) DEFAULT 0,
    taken DECIMAL(6,2) DEFAULT 0,
    balance DECIMAL(6,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS leave_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_no VARCHAR(50) DEFAULT NULL,
    emp_name VARCHAR(150) DEFAULT NULL,
    leave_type VARCHAR(50) DEFAULT NULL,
    from_date DATE DEFAULT NULL,
    to_date DATE DEFAULT NULL,
    days DECIMAL(5,2) DEFAULT 0,
    status VARCHAR(30) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$tab = $_GET['tab'] ?? 'balance';
$results = [];
$searched = false;
$filter = $_POST['filter'] ?? 'employee';
$year = $_POST['year'] ?? '';
$emp = trim($_POST['employee'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') {
    $searched = true;
    if ($tab === 'balance' || $tab === '') {
        $sql = "SELECT * FROM leave_balances WHERE 1=1";
        $params = []; $types = '';
        if ($year !== '') { $sql .= " AND year = ?"; $params[] = (int)$year; $types .= 'i'; }
        if ($filter === 'employee' && $emp !== '') { $sql .= " AND (emp_no LIKE ? OR emp_name LIKE ?)"; $like = '%'.$emp.'%'; $params[] = $like; $params[] = $like; $types .= 'ss'; }
        $sql .= " ORDER BY emp_no, leave_type LIMIT 300";
        $stmt = $conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $results[] = $row;
        $stmt->close();
    } elseif ($tab === 'abnormal') {
        $sql = "SELECT * FROM leave_balances WHERE balance < 0";
        if ($year !== '') { $sql .= " AND year = " . (int)$year; }
        if ($filter === 'employee' && $emp !== '') {
            $like = $conn->real_escape_string('%'.$emp.'%');
            $sql .= " AND (emp_no LIKE '$like' OR emp_name LIKE '$like')";
        }
        $sql .= " ORDER BY balance ASC LIMIT 200";
        $r = $conn->query($sql);
        if ($r) while ($row = $r->fetch_assoc()) $results[] = $row;
    } elseif ($tab === 'history') {
        $sql = "SELECT * FROM leave_history WHERE 1=1";
        $params = []; $types = '';
        if ($filter === 'employee' && $emp !== '') { $sql .= " AND (emp_no LIKE ? OR emp_name LIKE ?)"; $like = '%'.$emp.'%'; $params[] = $like; $params[] = $like; $types .= 'ss'; }
        $sql .= " ORDER BY from_date DESC LIMIT 300";
        $stmt = $conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $results[] = $row;
        $stmt->close();
    }
}

// Update leave balance (simple recalc demo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_balance') {
    // Demo: ensure sample rows exist
    $chk = $conn->query("SELECT COUNT(*) AS c FROM leave_balances");
    $c = $chk ? (int)$chk->fetch_assoc()['c'] : 0;
    if ($c === 0) {
        $conn->query("INSERT INTO leave_balances (emp_no, emp_name, year, leave_type, entitled, taken, balance) VALUES
            ('EMP001','Kamal Perera',2026,'Annual',14,3,11),
            ('EMP001','Kamal Perera',2026,'Casual',7,1,6),
            ('EMP001','Kamal Perera',2026,'Medical',14,0,14),
            ('EMP002','Nimal Silva',2026,'Annual',14,5,9),
            ('EMP002','Nimal Silva',2026,'Casual',7,2,5)");
    }
    header("Location: leave-balance.php?tab=balance&updated=1");
    exit;
}

$curYear = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Balance | SMART HRIS™</title>
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
.container{max-width:960px;margin:0 auto;padding:22px 20px 50px}

.tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:0}
.tab{padding:10px 16px;font-size:13px;font-weight:500;color:#64748b;text-decoration:none;border:1px solid transparent;border-bottom:none;border-radius:8px 8px 0 0}
.tab:hover{color:var(--accent)}
.tab.active{color:#0f172a;background:#fff;border-color:var(--border);margin-bottom:-2px;font-weight:600}

.panel{background:#fff;border:1px solid var(--border);border-top:none;border-radius:0 0 12px 12px;padding:24px 28px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.panel-title{font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;padding-bottom:10px;border-bottom:1px solid #f1f5f9}

.radio-row{display:flex;align-items:center;gap:24px;margin-bottom:18px}
.radio-row label{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:500;cursor:pointer}

.form-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px}
label.fld{font-size:13px;font-weight:500;color:#334155;min-width:110px}
select,input[type="text"]{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff
}
select{min-width:120px} input[type="text"]{min-width:180px}
select:focus,input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.btn-search{height:36px;padding:0 20px;background:var(--orange);color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit}
.btn-search:hover{background:#c2410c}

.btn-update{
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
    width:90px;height:72px;background:#2563eb;color:#fff;border:none;border-radius:10px;
    font-size:11px;font-weight:600;cursor:pointer;font-family:inherit;margin-top:8px
}
.btn-update i{font-size:20px}
.btn-update:hover{background:#1d4ed8}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-top:16px}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 12px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.empty{text-align:center;padding:28px;color:#94a3b8}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
.neg{color:#dc2626;font-weight:600}

@media(max-width:700px){.search-nav,.welcome{display:none}}
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
<div class="tabs">
    <a href="?tab=balance" class="tab <?= $tab === 'balance' ? 'active' : '' ?>">Leave Balance</a>
    <a href="?tab=abnormal" class="tab <?= $tab === 'abnormal' ? 'active' : '' ?>">Abnormal Leave Balances</a>
    <a href="?tab=history" class="tab <?= $tab === 'history' ? 'active' : '' ?>">Leave History</a>
</div>

<div class="panel">
<?php if (isset($_GET['updated'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Leave balances updated!</div><?php endif; ?>

<h2 class="panel-title"><?= $tab === 'abnormal' ? 'Abnormal Leave Balances' : ($tab === 'history' ? 'Leave History' : 'Leave Balance') ?></h2>

<form method="POST">
<input type="hidden" name="action" value="search">

<div class="radio-row">
    <label><input type="radio" name="filter" value="all" <?= $filter === 'all' ? 'checked' : '' ?>> All</label>
    <label><input type="radio" name="filter" value="employee" <?= $filter !== 'all' ? 'checked' : '' ?>> By Employee</label>
</div>

<div class="form-row">
    <label class="fld">Year &amp; Employee</label>
    <select name="year">
        <option value="">-select-</option>
        <?php for ($y = $curYear - 1; $y <= $curYear + 1; $y++): ?>
        <option value="<?= $y ?>" <?= (string)$year === (string)$y ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
    </select>
    <input type="text" name="employee" value="<?= htmlspecialchars($emp) ?>" placeholder="Employee name / no">
    <button type="submit" class="btn-search">search</button>
</div>
</form>

<?php if ($tab === 'balance'): ?>
<form method="POST" style="display:inline">
<input type="hidden" name="action" value="update_balance">
<button type="submit" class="btn-update"><i class="fas fa-sync-alt"></i>Update Leave Balance</button>
</form>
<?php endif; ?>

<?php if ($searched): ?>
<div class="table-wrap">
<?php if ($tab === 'history'): ?>
<table>
<thead><tr><th>Emp No</th><th>Name</th><th>Leave Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th></tr></thead>
<tbody>
<?php if (empty($results)): ?><tr><td colspan="7" class="empty">No leave history found.</td></tr>
<?php else: foreach ($results as $r): ?>
<tr>
<td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['leave_type'] ?? '') ?></td>
<td><?= htmlspecialchars($r['from_date'] ?? '') ?></td>
<td><?= htmlspecialchars($r['to_date'] ?? '') ?></td>
<td><?= htmlspecialchars($r['days'] ?? '') ?></td>
<td><?= htmlspecialchars($r['status'] ?? '') ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
<?php else: ?>
<table>
<thead><tr><th>Emp No</th><th>Name</th><th>Year</th><th>Leave Type</th><th>Entitled</th><th>Taken</th><th>Balance</th></tr></thead>
<tbody>
<?php if (empty($results)): ?><tr><td colspan="7" class="empty">No leave balance records found. Click "Update Leave Balance" to generate sample data.</td></tr>
<?php else: foreach ($results as $r): ?>
<tr>
<td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['year'] ?? '') ?></td>
<td><?= htmlspecialchars($r['leave_type'] ?? '') ?></td>
<td><?= htmlspecialchars($r['entitled'] ?? '') ?></td>
<td><?= htmlspecialchars($r['taken'] ?? '') ?></td>
<td class="<?= ((float)($r['balance'] ?? 0) < 0) ? 'neg' : '' ?>"><?= htmlspecialchars($r['balance'] ?? '') ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
</main>
</body>
</html>