<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS shift_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assign_type VARCHAR(20) DEFAULT 'employee',
    emp_group_name VARCHAR(150) DEFAULT NULL,
    all_employees TINYINT(1) DEFAULT 0,
    shift_name VARCHAR(150) DEFAULT NULL,
    from_date DATE DEFAULT NULL,
    to_date DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Assign
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign') {
    $assign_type = $_POST['assign_type'] ?? 'employee';
    $emp_name    = trim($_POST['emp_group_name'] ?? '');
    $all_emp     = isset($_POST['all_employees']) ? 1 : 0;
    $shift_name  = $_POST['shift_name'] ?? '';
    $from_date   = $_POST['from_date'] ?? '';
    $to_date     = $_POST['to_date'] ?? '';
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if (($emp_name !== '' || $all_emp) && $shift_name !== '' && $from_date !== '' && $to_date !== '') {
        if ($all_emp) $emp_name = 'ALL EMPLOYEES';
        $sql = "INSERT INTO shift_assignments (assign_type, emp_group_name, all_employees, shift_name, from_date, to_date, is_active) VALUES (?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisssi", $assign_type, $emp_name, $all_emp, $shift_name, $from_date, $to_date, $is_active);
        $stmt->execute();
        $stmt->close();
        header("Location: assign-to-shift.php?success=1");
        exit;
    }
}

// Search
$results = [];
$searched = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') {
    $searched = true;
    $s_type  = $_POST['s_assign_type'] ?? 'employee';
    $s_emp   = trim($_POST['s_emp'] ?? '');
    $s_all   = isset($_POST['s_all_employees']) ? 1 : 0;
    $s_shift = $_POST['s_shift_name'] ?? '';
    $s_from  = $_POST['s_from_date'] ?? '';
    $s_to    = $_POST['s_to_date'] ?? '';

    $sql = "SELECT * FROM shift_assignments WHERE 1=1";
    $params = [];
    $types = '';
    if ($s_type !== '') { $sql .= " AND assign_type = ?"; $params[] = $s_type; $types .= 's'; }
    if (!$s_all && $s_emp !== '') { $sql .= " AND emp_group_name LIKE ?"; $params[] = '%'.$s_emp.'%'; $types .= 's'; }
    if ($s_shift !== '') { $sql .= " AND shift_name = ?"; $params[] = $s_shift; $types .= 's'; }
    if ($s_from !== '') { $sql .= " AND from_date >= ?"; $params[] = $s_from; $types .= 's'; }
    if ($s_to !== '') { $sql .= " AND to_date <= ?"; $params[] = $s_to; $types .= 's'; }
    $sql .= " ORDER BY from_date DESC LIMIT 200";
    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $results[] = $row;
    $stmt->close();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM shift_assignments WHERE id = $id");
    header("Location: assign-to-shift.php?deleted=1");
    exit;
}

$shifts = [];
$sr = @$conn->query("SELECT DISTINCT shift_name FROM shifts ORDER BY shift_name");
if ($sr) while ($s = $sr->fetch_assoc()) $shifts[] = $s['shift_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign To Shift | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0;--green:#16a34a;--cyan:#0ea5e9}
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
.container{max-width:900px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:18px;color:#1e293b}
.section-title{font-size:15px;font-weight:600;color:#334155;margin:28px 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border)}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px 28px;margin-bottom:8px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.radio-row{display:flex;align-items:center;gap:18px;margin-bottom:16px}
.radio-row label{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:500;cursor:pointer}

.form-grid{display:grid;grid-template-columns:130px 1fr auto;gap:12px 14px;align-items:center;max-width:560px}
label.fld{font-size:13px;font-weight:500;color:#334155}
label .req{color:#ef4444}
input[type="text"],input[type="date"],select{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;width:100%;max-width:300px;background:#fff
}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.chk{display:flex;align-items:center;gap:6px;font-size:12.5px;color:#475569;white-space:nowrap}
.chk input{width:15px;height:15px}

.btn-assign{
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
    width:72px;height:64px;background:var(--green);color:#fff;border:none;border-radius:10px;
    font-size:12px;font-weight:600;cursor:pointer;margin-top:18px;font-family:inherit;transition:all .2s
}
.btn-assign i{font-size:20px}
.btn-assign:hover{background:#15803d;transform:translateY(-2px)}

.btn-search{
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
    width:72px;height:64px;background:var(--cyan);color:#fff;border:none;border-radius:10px;
    font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .2s
}
.btn-search i{font-size:20px}
.btn-search:hover{background:#0284c7;transform:translateY(-2px)}

.search-layout{display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap}
.search-fields{flex:1;min-width:280px}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-top:18px}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 14px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 14px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.btn-del{background:#dc2626;color:#fff;border:none;padding:3px 10px;border-radius:4px;font-size:11px;cursor:pointer}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
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
<h1 class="page-title">Assign To Shift</h1>

<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Shift assigned successfully!</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert red"><i class="fas fa-trash"></i> Assignment deleted.</div><?php endif; ?>

<!-- ASSIGN FORM -->
<div class="form-card">
<form method="POST">
<input type="hidden" name="action" value="assign">

<div class="radio-row">
    <label><input type="radio" name="assign_type" value="employee" checked> Employee</label>
    <label><input type="radio" name="assign_type" value="group"> Group</label>
</div>

<div class="form-grid">
    <label class="fld">By Employee <span class="req">*</span></label>
    <input type="text" name="emp_group_name" id="emp_group_name" placeholder="Employee name or number">
    <label class="chk"><input type="checkbox" name="all_employees" id="all_employees" value="1"> All Employees</label>

    <label class="fld">Shift Name <span class="req">*</span></label>
    <select name="shift_name" id="shift_name" required>
        <option value="">-select-</option>
        <?php foreach ($shifts as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
        <?php endforeach; ?>
        <?php if (empty($shifts)): ?>
        <option value="General Shift">General Shift</option>
        <option value="Evening Shift">Evening Shift</option>
        <option value="Work from home">Work from home</option>
        <option value="Morning Half Day Shift">Morning Half Day Shift</option>
        <?php endif; ?>
    </select>
    <div></div>

    <label class="fld">From Date <span class="req">*</span></label>
    <input type="date" name="from_date" required>
    <div></div>

    <label class="fld">To Date <span class="req">*</span></label>
    <input type="date" name="to_date" required>
    <div></div>

    <label class="fld">IsActive</label>
    <label class="chk"><input type="checkbox" name="is_active" value="1" checked> IsActive</label>
    <div></div>
</div>

<button type="submit" class="btn-assign"><i class="fas fa-user-check"></i>Assign</button>
</form>
</div>

<!-- SEARCH -->
<h2 class="section-title">Search Assigned Shifts</h2>
<div class="form-card">
<form method="POST">
<input type="hidden" name="action" value="search">

<div class="radio-row">
    <label><input type="radio" name="s_assign_type" value="employee" checked> Employee</label>
    <label><input type="radio" name="s_assign_type" value="group"> Group</label>
</div>

<div class="search-layout">
<div class="search-fields">
<div class="form-grid">
    <label class="fld">Employee <span class="req">*</span></label>
    <input type="text" name="s_emp" value="<?= htmlspecialchars($_POST['s_emp'] ?? '') ?>" placeholder="Employee name">
    <label class="chk"><input type="checkbox" name="s_all_employees" value="1"> All Employees</label>

    <label class="fld">Shift Name <span class="req">*</span></label>
    <select name="s_shift_name">
        <option value="">-select-</option>
        <?php foreach ($shifts as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>" <?= ($_POST['s_shift_name'] ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
        <?php endforeach; ?>
        <?php if (empty($shifts)): ?>
        <option value="General Shift">General Shift</option>
        <option value="Evening Shift">Evening Shift</option>
        <?php endif; ?>
    </select>
    <div></div>

    <label class="fld">From Date <span class="req">*</span></label>
    <input type="date" name="s_from_date" value="<?= htmlspecialchars($_POST['s_from_date'] ?? '') ?>">
    <div></div>

    <label class="fld">To Date <span class="req">*</span></label>
    <input type="date" name="s_to_date" value="<?= htmlspecialchars($_POST['s_to_date'] ?? '') ?>">
    <div></div>
</div>
</div>
<button type="submit" class="btn-search"><i class="fas fa-search"></i>Search</button>
</div>
</form>
</div>

<?php if ($searched): ?>
<p style="font-size:12.5px;color:#64748b;margin:12px 0 8px"><i class="fas fa-list"></i> <?= count($results) ?> record(s)</p>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Type</th>
<th>Employee / Group</th>
<th>Shift</th>
<th>From Date</th>
<th>To Date</th>
<th>Active</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($results)): ?>
<tr><td colspan="7" class="empty">No assigned shifts found.</td></tr>
<?php else: foreach ($results as $r): ?>
<tr>
<td><?= htmlspecialchars(ucfirst($r['assign_type'] ?? '')) ?></td>
<td><?= htmlspecialchars($r['emp_group_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['shift_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['from_date'] ?? '') ?></td>
<td><?= htmlspecialchars($r['to_date'] ?? '') ?></td>
<td><?= ($r['is_active'] ?? 0) ? 'Yes' : 'No' ?></td>
<td><button type="button" class="btn-del" onclick="if(confirm('Delete?')) location='?delete=<?= (int)$r['id'] ?>'">Delete</button></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</main>
</body>
</html>