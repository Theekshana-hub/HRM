<?php
include '../db_connect.php';

// Ensure tables exist
$conn->query("CREATE TABLE IF NOT EXISTS manual_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_name VARCHAR(150) DEFAULT NULL,
    employee_remarks VARCHAR(255) DEFAULT NULL,
    check_mode VARCHAR(20) DEFAULT NULL,
    checkin_date DATE DEFAULT NULL,
    checkin_time TIME DEFAULT NULL,
    checkout_date DATE DEFAULT NULL,
    checkout_time TIME DEFAULT NULL,
    break_mode VARCHAR(20) DEFAULT NULL,
    breakin_date DATE DEFAULT NULL,
    breakin_time TIME DEFAULT NULL,
    breakout_date DATE DEFAULT NULL,
    breakout_time TIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Cancel (delete) selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $ids = $_POST['ids'] ?? [];
    if (!empty($ids)) {
        $ids = array_map('intval', $ids);
        $in = implode(',', $ids);
        $conn->query("DELETE FROM manual_attendance WHERE id IN ($in)");
        header("Location: manual-attendance-cancel.php?cancelled=1");
        exit;
    }
}

$results = [];
$searched = false;
$emp = trim($_POST['employee_name'] ?? '');
$from = $_POST['from_date'] ?? '';
$to = $_POST['to_date'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') {
    $searched = true;
    $sql = "SELECT * FROM manual_attendance WHERE 1=1";
    $params = [];
    $types = '';
    if ($emp !== '') {
        $sql .= " AND employee_name LIKE ?";
        $params[] = '%' . $emp . '%';
        $types .= 's';
    }
    if ($from !== '') {
        $sql .= " AND (checkin_date >= ? OR checkout_date >= ?)";
        $params[] = $from;
        $params[] = $from;
        $types .= 'ss';
    }
    if ($to !== '') {
        $sql .= " AND (checkin_date <= ? OR checkout_date <= ?)";
        $params[] = $to;
        $params[] = $to;
        $types .= 'ss';
    }
    $sql .= " ORDER BY id DESC LIMIT 200";
    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $results[] = $row;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manual Attendance Cancel | SMART HRIS™</title>
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
.page-title{font-size:18px;font-weight:600;margin-bottom:20px;color:#1e293b}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px 28px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.fields{display:flex;flex-wrap:wrap;gap:20px 28px;align-items:flex-end;margin-bottom:18px}
.fg{display:flex;flex-direction:column;gap:6px}
.fg label{font-size:12.5px;font-weight:500;color:#475569}
input[type="text"],input[type="date"]{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff;min-width:160px
}
input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}

.btn-search{
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
    width:72px;height:64px;background:var(--orange);color:#fff;border:none;border-radius:10px;
    font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .2s
}
.btn-search i{font-size:20px}
.btn-search:hover{background:#c2410c;transform:translateY(-2px)}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 12px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.empty{text-align:center;padding:32px;color:#94a3b8}
.btn-cancel{
    height:36px;padding:0 18px;background:#dc2626;color:#fff;border:none;border-radius:8px;
    font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;margin-top:14px
}
.btn-cancel:hover{background:#b91c1c}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
.result-count{font-size:12.5px;color:#64748b;margin-bottom:8px}

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
<h1 class="page-title">Manual Attendance Cancel</h1>

<?php if (isset($_GET['cancelled'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Selected records cancelled/deleted.</div><?php endif; ?>

<div class="form-card">
<form method="POST">
<input type="hidden" name="action" value="search">
<div class="fields">
    <div class="fg">
        <label>Employee Name</label>
        <input type="text" name="employee_name" value="<?= htmlspecialchars($emp) ?>" placeholder="Employee name">
    </div>
    <div class="fg">
        <label>From:</label>
        <input type="date" name="from_date" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="fg">
        <label>To:</label>
        <input type="date" name="to_date" value="<?= htmlspecialchars($to) ?>">
    </div>
</div>
<button type="submit" class="btn-search"><i class="fas fa-search"></i>Search</button>
</form>
</div>

<?php if ($searched): ?>
<p class="result-count"><i class="fas fa-list"></i> <?= count($results) ?> record(s) found</p>
<form method="POST">
<input type="hidden" name="action" value="cancel">
<div class="table-wrap">
<table>
<thead>
<tr>
<th><input type="checkbox" id="checkAll" onclick="document.querySelectorAll('.row-chk').forEach(c=>c.checked=this.checked)"></th>
<th>ID</th>
<th>Employee</th>
<th>Mode</th>
<th>Check In</th>
<th>Check Out</th>
<th>Remarks</th>
</tr>
</thead>
<tbody>
<?php if (empty($results)): ?>
<tr><td colspan="7" class="empty">No manual attendance records found.</td></tr>
<?php else: foreach ($results as $r): ?>
<tr>
<td><input type="checkbox" class="row-chk" name="ids[]" value="<?= (int)$r['id'] ?>"></td>
<td><?= (int)$r['id'] ?></td>
<td><?= htmlspecialchars($r['employee_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['check_mode'] ?? '') ?></td>
<td><?= htmlspecialchars(($r['checkin_date'] ?? '') . ' ' . substr($r['checkin_time'] ?? '', 0, 8)) ?></td>
<td><?= htmlspecialchars(($r['checkout_date'] ?? '') . ' ' . substr($r['checkout_time'] ?? '', 0, 8)) ?></td>
<td><?= htmlspecialchars($r['employee_remarks'] ?? '') ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
<?php if (!empty($results)): ?>
<button type="submit" class="btn-cancel" onclick="return confirm('Cancel/delete selected records?')"><i class="fas fa-times-circle"></i> Cancel Selected</button>
<?php endif; ?>
</form>
<?php endif; ?>
</main>
</body>
</html>