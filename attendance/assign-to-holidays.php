<?php
include '../db_connect.php';

// Load holidays for dropdowns
$holidays = [];
$hr = $conn->query("SELECT id, holiday_name FROM holidays ORDER BY holiday_name ASC");
if ($hr) while ($h = $hr->fetch_assoc()) $holidays[] = $h;

// Assign save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign') {
    $assign_type   = $_POST['assign_type'] ?? 'employee';
    $emp_group     = trim($_POST['emp_group_name'] ?? '');
    $holiday_id    = $_POST['holiday_id'] ?? '';
    $holiday_date  = $_POST['holiday_date'] ?? '';
    $is_active     = isset($_POST['is_active']) ? 1 : 0;
    $holiday_name  = '';

    if ($holiday_id !== '') {
        foreach ($holidays as $h) {
            if ((string)$h['id'] === (string)$holiday_id) {
                $holiday_name = $h['holiday_name'];
                break;
            }
        }
    }

    if ($emp_group !== '' && $holiday_name !== '' && $holiday_date !== '') {
        $sql = "INSERT INTO holiday_assignments (assign_type, emp_group_name, holiday_id, holiday_name, holiday_date, is_active) VALUES (?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissi", $assign_type, $emp_group, $holiday_id, $holiday_name, $holiday_date, $is_active);
        $stmt->execute();
        $stmt->close();
        header("Location: assign-to-holidays.php?success=1");
        exit;
    }
}

// Search
$results = [];
$searched = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') {
    $searched = true;
    $s_holiday = $_POST['s_holiday'] ?? '';
    $s_emp     = trim($_POST['s_employee'] ?? '');
    $s_from    = $_POST['s_from'] ?? '';
    $s_to      = $_POST['s_to'] ?? '';

    $tableCheck = $conn->query("SHOW TABLES LIKE 'holiday_assignments'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $sql = "SELECT * FROM holiday_assignments WHERE 1=1";
        $params = [];
        $types = '';
        if ($s_holiday !== '') { $sql .= " AND holiday_id = ?"; $params[] = $s_holiday; $types .= 'i'; }
        if ($s_emp !== '') { $sql .= " AND emp_group_name LIKE ?"; $params[] = '%'.$s_emp.'%'; $types .= 's'; }
        if ($s_from !== '') { $sql .= " AND holiday_date >= ?"; $params[] = $s_from; $types .= 's'; }
        if ($s_to !== '') { $sql .= " AND holiday_date <= ?"; $params[] = $s_to; $types .= 's'; }
        $sql .= " ORDER BY holiday_date DESC LIMIT 200";
        $stmt = $conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $results[] = $row;
        $stmt->close();
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM holiday_assignments WHERE id = $id");
    header("Location: assign-to-holidays.php?deleted=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign to Holidays | SMART HRIS™</title>
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
.radio-row{display:flex;align-items:center;gap:18px;margin-bottom:18px}
.radio-row label{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:500;cursor:pointer}
.btn-select{height:34px;padding:0 18px;background:#2563eb;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:inherit}
.btn-select:hover{background:#1d4ed8}

.form-grid{display:grid;grid-template-columns:150px 1fr auto;gap:12px 14px;align-items:center;max-width:520px}
label.fld{font-size:13px;font-weight:500;color:#334155}
input[type="text"],input[type="date"],select{
    height:34px;border:1px solid #cbd5e1;border-radius:7px;padding:0 10px;
    font-size:13px;outline:none;font-family:inherit;width:100%;max-width:260px;background:#fff
}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(59,130,246,.12)}
.btn-add{height:34px;padding:0 14px;background:#2563eb;color:#fff;border:none;border-radius:7px;font-size:12.5px;cursor:pointer}
.chk-row{display:flex;align-items:center;gap:8px;margin-top:4px}
.chk-row input{width:16px;height:16px}

.btn-row{display:flex;gap:12px;margin-top:22px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;width:72px;height:60px;border:none;border-radius:10px;cursor:pointer;font-size:11px;font-weight:600;color:#fff;gap:3px;font-family:inherit}
.btn i{font-size:18px}
.btn-assign{background:var(--green)}.btn-new{background:#94a3b8}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}

.search-section{border-top:1px solid var(--border);margin-top:28px;padding-top:22px}
.search-section h3{font-size:14px;font-weight:600;margin-bottom:14px;color:#475569}
.search-grid{display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px 18px}
.search-grid .sg{display:flex;flex-direction:column;gap:5px}
.search-grid label{font-size:12px;font-weight:500;color:#64748b}
.btn-search{height:36px;padding:0 22px;background:var(--orange);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit}
.btn-search:hover{background:#c2410c}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-top:18px}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 14px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 14px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.btn-del{background:#dc2626;color:#fff;border:none;padding:3px 10px;border-radius:4px;font-size:11px;cursor:pointer}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px}
.alert.red{background:#fee2e2;color:#991b1b}
.empty{text-align:center;padding:28px;color:#94a3b8}

@media(max-width:700px){.form-grid{grid-template-columns:1fr}.search-nav,.welcome{display:none}}
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
<h1 class="page-title">Assign to Holidays</h1>

<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Holiday assigned successfully!</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert red"><i class="fas fa-trash"></i> Assignment deleted.</div><?php endif; ?>

<div class="form-card">
<form method="POST" id="assignForm">
<input type="hidden" name="action" value="assign">

<div class="radio-row">
    <label><input type="radio" name="assign_type" value="employee" checked onchange="updateLabel()"> By Employee</label>
    <label><input type="radio" name="assign_type" value="group" onchange="updateLabel()"> By Group</label>
    <button type="button" class="btn-select" onclick="alert('Select employee/group from list (connect to employees table)')">Select</button>
</div>

<div class="form-grid">
    <label class="fld" id="nameLabel">Employee/ Group Name</label>
    <input type="text" name="emp_group_name" id="emp_group_name" placeholder="Enter name or code" required>
    <div></div>

    <label class="fld">Holiday Name</label>
    <select name="holiday_id" id="holiday_id" required>
        <option value="">-select-</option>
        <?php foreach ($holidays as $h): ?>
        <option value="<?= (int)$h['id'] ?>"><?= htmlspecialchars($h['holiday_name']) ?></option>
        <?php endforeach; ?>
        <?php if (empty($holidays)): ?>
        <option value="1">Poya Day</option>
        <option value="2">National Day</option>
        <option value="3">Christmas Day</option>
        <option value="4">Company Holiday</option>
        <?php endif; ?>
    </select>
    <div></div>

    <label class="fld">Date</label>
    <input type="date" name="holiday_date" id="holiday_date" required>
    <button type="button" class="btn-add" onclick="document.getElementById('holiday_date').showPicker && document.getElementById('holiday_date').showPicker()">Add</button>

    <label class="fld">Is Active Holiday</label>
    <div class="chk-row"><input type="checkbox" name="is_active" id="is_active" value="1" checked> <label for="is_active" style="font-weight:400">IsActive</label></div>
    <div></div>
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-assign"><i class="fas fa-user-check"></i>Assign</button>
    <button type="button" class="btn btn-new" onclick="clearAssign()"><i class="fas fa-file"></i>New Assign</button>
</div>
</form>

<!-- Search -->
<div class="search-section">
<form method="POST">
<input type="hidden" name="action" value="search">
<div class="search-grid">
    <div class="sg">
        <label>Holiday</label>
        <select name="s_holiday">
            <option value="">-select-</option>
            <?php foreach ($holidays as $h): ?>
            <option value="<?= (int)$h['id'] ?>" <?= ($_POST['s_holiday'] ?? '') == $h['id'] ? 'selected' : '' ?>><?= htmlspecialchars($h['holiday_name']) ?></option>
            <?php endforeach; ?>
            <?php if (empty($holidays)): ?>
            <option value="1">Poya Day</option>
            <option value="2">National Day</option>
            <?php endif; ?>
        </select>
    </div>
    <div class="sg">
        <label>Employee</label>
        <input type="text" name="s_employee" value="<?= htmlspecialchars($_POST['s_employee'] ?? '') ?>" placeholder="Employee name">
    </div>
    <div class="sg">
        <label>Date</label>
        <div style="display:flex;gap:8px">
            <input type="date" name="s_from" value="<?= htmlspecialchars($_POST['s_from'] ?? '') ?>">
            <input type="date" name="s_to" value="<?= htmlspecialchars($_POST['s_to'] ?? '') ?>">
        </div>
    </div>
    <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
</div>
</form>
</div>
</div>

<?php if ($searched): ?>
<p style="font-size:12.5px;color:#64748b;margin-bottom:8px"><i class="fas fa-list"></i> <?= count($results) ?> record(s)</p>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Type</th>
<th>Employee / Group</th>
<th>Holiday</th>
<th>Date</th>
<th>Active</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($results)): ?>
<tr><td colspan="6" class="empty">No assignments found.</td></tr>
<?php else: foreach ($results as $r): ?>
<tr>
<td><?= htmlspecialchars(ucfirst($r['assign_type'] ?? '')) ?></td>
<td><?= htmlspecialchars($r['emp_group_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['holiday_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['holiday_date'] ?? '') ?></td>
<td><?= ($r['is_active'] ?? 0) ? 'Yes' : 'No' ?></td>
<td><button type="button" class="btn-del" onclick="if(confirm('Delete?')) location='?delete=<?= (int)$r['id'] ?>'">Delete</button></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</main>

<script>
function updateLabel() {
    const t = document.querySelector('input[name="assign_type"]:checked').value;
    document.getElementById('nameLabel').textContent = t === 'group' ? 'Group Name' : 'Employee/ Group Name';
}
function clearAssign() {
    document.getElementById('assignForm').reset();
    document.querySelector('input[name="assign_type"][value="employee"]').checked = true;
    updateLabel();
    document.getElementById('is_active').checked = true;
}
</script>
</body>
</html>