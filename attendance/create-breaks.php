<?php
include '../db_connect.php';

// Auto-create table if missing
$conn->query("CREATE TABLE IF NOT EXISTS breaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    break_name VARCHAR(100) NOT NULL,
    break_start TIME DEFAULT NULL,
    break_end TIME DEFAULT NULL,
    duration_time VARCHAR(20) DEFAULT '00:00:00',
    check_break TINYINT(1) DEFAULT 0,
    fix_duration TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM breaks WHERE id = $id");
    header("Location: create-breaks.php?deleted=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $break_name    = trim($_POST['break_name'] ?? '');
    $break_start   = $_POST['break_start'] ?? null;
    $break_end     = $_POST['break_end'] ?? null;
    $duration_time = $_POST['duration_time'] ?? '00:00:00';
    $check_break   = $_POST['check_break'] ?? '0';
    $fix_duration  = $_POST['fix_duration'] ?? '0';
    $is_active     = $_POST['is_active'] ?? '0';
    $edit_id       = $_POST['edit_id'] ?? '';

    if ($break_name !== '') {
        if (!empty($edit_id)) {
            $sql = "UPDATE breaks SET break_name=?, break_start=?, break_end=?, duration_time=?, check_break=?, fix_duration=?, is_active=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiiii", $break_name, $break_start, $break_end, $duration_time, $check_break, $fix_duration, $is_active, $edit_id);
        } else {
            $sql = "INSERT INTO breaks (break_name, break_start, break_end, duration_time, check_break, fix_duration, is_active) VALUES (?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiii", $break_name, $break_start, $break_end, $duration_time, $check_break, $fix_duration, $is_active);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: create-breaks.php?success=1");
        exit;
    }
}

$rows = [];
$r = $conn->query("SELECT * FROM breaks ORDER BY id ASC");
if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;

function fmtT($t) {
    if (!$t) return '';
    return date('g:i A', strtotime($t));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Breaks | SMART HRIS™</title>
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
.container{max-width:900px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:20px;color:#1e293b}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px}
.form-grid{display:grid;grid-template-columns:140px 200px 140px 200px;gap:14px 20px;align-items:center}
label{font-size:12.5px;font-weight:500;color:#334155}
label .req{color:#ef4444}
input[type="text"],input[type="time"]{
    height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;
    font-size:13px;outline:none;font-family:inherit;width:100%;background:#fff
}
input:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(59,130,246,.12)}
.toggle{width:40px;height:22px;background:#cbd5e1;border-radius:20px;position:relative;cursor:pointer;transition:.2s;display:inline-block}
.toggle.on{background:#22c55e}
.toggle.red.on{background:#ef4444}
.toggle::after{content:'';position:absolute;width:18px;height:18px;background:#fff;border-radius:50%;top:2px;left:2px;transition:.2s}
.toggle.on::after{left:20px}

.btn-row{display:flex;gap:10px;margin-top:20px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;width:64px;height:54px;border:none;border-radius:8px;cursor:pointer;font-size:11px;font-weight:500;color:#fff;gap:3px}
.btn i{font-size:16px}
.btn-save{background:#2563eb}.btn-new{background:#ea580c}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 12px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.btn-edit{background:#2563eb;color:#fff;border:none;padding:3px 10px;border-radius:4px;font-size:11px;cursor:pointer;margin:1px}
.btn-del{background:#dc2626;color:#fff;border:none;padding:3px 10px;border-radius:4px;font-size:11px;cursor:pointer;margin:1px}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
.alert.red{background:#fee2e2;color:#991b1b}

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
<h1 class="page-title">Create Breaks</h1>

<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Break saved successfully!</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert red"><i class="fas fa-trash"></i> Break deleted.</div><?php endif; ?>

<div class="form-card">
<form method="POST" id="breakForm">
<input type="hidden" name="action" value="save">
<input type="hidden" name="edit_id" id="edit_id" value="">
<input type="hidden" name="check_break" id="check_break" value="0">
<input type="hidden" name="fix_duration" id="fix_duration" value="0">
<input type="hidden" name="is_active" id="is_active" value="0">

<div class="form-grid">
    <label>Break Name <span class="req">*</span></label>
    <input type="text" name="break_name" id="break_name" required>

    <label>Duration Time <small>(hh:mm:ss)</small></label>
    <input type="text" name="duration_time" id="duration_time" placeholder="00:00:00">

    <label>Break Start Time <span class="req">*</span></label>
    <input type="time" name="break_start" id="break_start" step="1">

    <label>Fix Duration</label>
    <div class="toggle red" id="tg_fix_duration" onclick="toggleSw(this,'fix_duration')"></div>

    <label>Break End Time <span class="req">*</span></label>
    <input type="time" name="break_end" id="break_end" step="1">

    <label>Is Active <span class="req">*</span></label>
    <div class="toggle red" id="tg_is_active" onclick="toggleSw(this,'is_active')"></div>

    <label>Check Break</label>
    <div class="toggle red" id="tg_check_break" onclick="toggleSw(this,'check_break')"></div>
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-save"><i class="fas fa-save"></i>Save</button>
    <button type="button" class="btn btn-new" onclick="clearForm()"><i class="fas fa-file"></i>New</button>
</div>
</form>
</div>

<div class="table-wrap">
<table>
<thead>
<tr>
<th>Break Name</th>
<th>Break InTime</th>
<th>Break OutTime</th>
<th>IsCheck Break</th>
<th>IsFix Duration</th>
<th>Duration Time</th>
<th>Is Active</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($rows)): ?>
<tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:20px">No breaks yet. Add one above.</td></tr>
<?php else: foreach ($rows as $r): ?>
<tr>
<td><?= htmlspecialchars($r['break_name'] ?? '') ?></td>
<td><?= fmtT($r['break_start'] ?? '') ?></td>
<td><?= fmtT($r['break_end'] ?? '') ?></td>
<td><?= ($r['check_break'] ?? 0) ? 'Yes' : 'No' ?></td>
<td><?= ($r['fix_duration'] ?? 0) ? 'Yes' : 'No' ?></td>
<td><?= htmlspecialchars($r['duration_time'] ?? '00:00:00') ?></td>
<td><?= ($r['is_active'] ?? 0) ? 'Yes' : 'No' ?></td>
<td>
    <button type="button" class="btn-del" onclick="if(confirm('Delete?')) location='?delete=<?= (int)$r['id'] ?>'">Delete</button>
    <button type="button" class="btn-edit" onclick='editRow(<?= json_encode($r) ?>)'>Edit</button>
</td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</main>

<script>
function toggleSw(el, id) {
    el.classList.toggle('on');
    document.getElementById(id).value = el.classList.contains('on') ? '1' : '0';
}
function setT(id, v) {
    document.getElementById(id).value = v == 1 ? '1' : '0';
    const el = document.getElementById('tg_' + id);
    if (el) { if (v == 1) el.classList.add('on'); else el.classList.remove('on'); }
}
function clearForm() {
    document.getElementById('breakForm').reset();
    document.getElementById('edit_id').value = '';
    setT('check_break', 0);
    setT('fix_duration', 0);
    setT('is_active', 0);
    document.getElementById('break_name').focus();
}
function editRow(r) {
    document.getElementById('edit_id').value = r.id;
    document.getElementById('break_name').value = r.break_name || '';
    document.getElementById('break_start').value = (r.break_start || '').substring(0, 8);
    document.getElementById('break_end').value = (r.break_end || '').substring(0, 8);
    document.getElementById('duration_time').value = r.duration_time || '00:00:00';
    setT('check_break', r.check_break);
    setT('fix_duration', r.fix_duration);
    setT('is_active', r.is_active);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</body>
</html>