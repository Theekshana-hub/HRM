<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS ot_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_no VARCHAR(50) DEFAULT NULL,
    emp_name VARCHAR(150) DEFAULT NULL,
    division VARCHAR(100) DEFAULT NULL,
    session_name VARCHAR(50) DEFAULT NULL,
    ot_date DATE DEFAULT NULL,
    ot_hours DECIMAL(5,2) DEFAULT 0,
    ot_minutes INT DEFAULT 0,
    ot_rate VARCHAR(20) DEFAULT NULL,
    status VARCHAR(30) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Approve selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'approve') {
    $ids = $_POST['ids'] ?? [];
    if (!empty($ids)) {
        $ids = array_map('intval', $ids);
        $in = implode(',', $ids);
        $conn->query("UPDATE ot_requests SET status = 'Approved' WHERE id IN ($in)");
        header("Location: ot-approvals-by-session.php?approved=1");
        exit;
    }
}

$results = [];
$searched = false;
$filter = $_POST['filter'] ?? 'employee';
$session = trim($_POST['session'] ?? '');
$emp = trim($_POST['employee'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') {
    $searched = true;
    $sql = "SELECT * FROM ot_requests WHERE 1=1";
    $params = [];
    $types = '';
    if ($session !== '') {
        $sql .= " AND session_name LIKE ?";
        $params[] = '%' . $session . '%';
        $types .= 's';
    }
    if ($filter === 'employee' && $emp !== '') {
        $sql .= " AND (emp_no LIKE ? OR emp_name LIKE ?)";
        $like = '%' . $emp . '%';
        $params[] = $like;
        $params[] = $like;
        $types .= 'ss';
    }
    $sql .= " ORDER BY session_name, emp_name, ot_date LIMIT 300";
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
<title>OT Approvals by Sessions | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0;--orange:#ea580c;--green:#16a34a}
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
.radio-row{display:flex;align-items:center;gap:20px;margin-bottom:18px}
.radio-row label{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:500;cursor:pointer}

.fields{display:grid;grid-template-columns:90px 200px;gap:12px 16px;align-items:center;max-width:320px}
label.fld{font-size:13px;font-weight:500;color:#334155}
input[type="text"]{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff;width:100%
}
input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}

.btn-row{display:flex;gap:10px;margin-top:20px}
.btn{
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
    width:72px;height:64px;border:none;border-radius:10px;cursor:pointer;
    font-size:12px;font-weight:600;color:#fff;font-family:inherit;transition:all .2s
}
.btn i{font-size:20px}
.btn-search{background:var(--orange)}.btn-search:hover{background:#c2410c;transform:translateY(-2px)}
.btn-approve{background:var(--green)}.btn-approve:hover{background:#15803d;transform:translateY(-2px)}

.legend{margin-top:16px;font-size:12.5px;color:#64748b}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 12px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.empty{text-align:center;padding:28px;color:#94a3b8}
.badge{padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600}
.badge.pending{background:#fef3c7;color:#92400e}
.badge.approved{background:#dcfce7;color:#166534}
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
<h1 class="page-title">OT Approvals by Sessions</h1>

<?php if (isset($_GET['approved'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Selected OT approved.</div><?php endif; ?>

<div class="form-card">
<form method="POST" id="searchForm">
<input type="hidden" name="action" value="search">

<div class="radio-row">
    <label><input type="radio" name="filter" value="division" <?= $filter === 'division' ? 'checked' : '' ?>> By Division</label>
    <label><input type="radio" name="filter" value="employee" <?= $filter !== 'division' ? 'checked' : '' ?>> By Employee</label>
</div>

<div class="fields">
    <label class="fld">Session</label>
    <input type="text" name="session" value="<?= htmlspecialchars($session) ?>" placeholder="e.g. Sep 2026">

    <label class="fld">Employee</label>
    <input type="text" name="employee" value="<?= htmlspecialchars($emp) ?>" placeholder="Name or number">
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-search"><i class="fas fa-search"></i>Search</button>
    <button type="button" class="btn btn-approve" onclick="submitApprove()"><i class="fas fa-gavel"></i>Approve</button>
</div>

<p class="legend">OT time legend – Hours : Minutes</p>
</form>
</div>

<?php if ($searched): ?>
<p class="result-count"><i class="fas fa-list"></i> <?= count($results) ?> record(s)</p>
<form method="POST" id="actionForm">
<input type="hidden" name="action" value="approve">
<div class="table-wrap">
<table>
<thead>
<tr>
<th><input type="checkbox" onclick="document.querySelectorAll('.row-chk').forEach(c=>c.checked=this.checked)"></th>
<th>Emp No</th>
<th>Name</th>
<th>Session</th>
<th>Date</th>
<th>OT (H:M)</th>
<th>Rate</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php if (empty($results)): ?>
<tr><td colspan="8" class="empty">No OT records found for this session.</td></tr>
<?php else: foreach ($results as $r):
    $h = (int)floor((float)($r['ot_hours'] ?? 0));
    $m = (int)(($r['ot_hours'] ?? 0) * 60) % 60;
    if (!empty($r['ot_minutes'])) $m = (int)$r['ot_minutes'];
    $hm = sprintf('%d:%02d', $h, $m);
?>
<tr>
<td><input type="checkbox" class="row-chk" name="ids[]" value="<?= (int)$r['id'] ?>"></td>
<td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['session_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['ot_date'] ?? '') ?></td>
<td><?= $hm ?></td>
<td><?= htmlspecialchars($r['ot_rate'] ?? '') ?></td>
<td><span class="badge <?= strtolower($r['status'] ?? 'pending') ?>"><?= htmlspecialchars($r['status'] ?? '') ?></span></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</form>
<?php endif; ?>
</main>

<script>
function submitApprove() {
    const form = document.getElementById('actionForm');
    if (!form) { alert('Search first'); return; }
    const checked = form.querySelectorAll('.row-chk:checked');
    if (!checked.length) { alert('Select at least one record'); return; }
    if (!confirm('Approve selected OT?')) return;
    form.submit();
}
</script>
</body>
</html>