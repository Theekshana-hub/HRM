<?php
include '../db_connect.php';

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS leave_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) DEFAULT NULL,
    shift_name VARCHAR(150) DEFAULT NULL,
    leave_category VARCHAR(50) DEFAULT NULL,
    leave_half_day_type VARCHAR(50) DEFAULT NULL,
    leave_start_time TIME DEFAULT NULL,
    leave_end_time TIME DEFAULT NULL,
    in_out_time TIME DEFAULT NULL,
    remark VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM leave_shifts WHERE id = $id");
    header("Location: leave-shift-creation.php?deleted=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $company     = trim($_POST['company_name'] ?? '');
    $shift_name  = trim($_POST['shift_name'] ?? '');
    $category    = $_POST['leave_category'] ?? '';
    $half_type   = $_POST['leave_half_day_type'] ?? '';
    $start_time  = $_POST['leave_start_time'] ?? null;
    $end_time    = $_POST['leave_end_time'] ?? null;
    $in_out      = $_POST['in_out_time'] ?? null;
    $remark      = $half_type;
    $edit_id     = $_POST['edit_id'] ?? '';

    if ($company !== '' && $shift_name !== '') {
        if (!empty($edit_id)) {
            $sql = "UPDATE leave_shifts SET company_name=?, shift_name=?, leave_category=?, leave_half_day_type=?, leave_start_time=?, leave_end_time=?, in_out_time=?, remark=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $company, $shift_name, $category, $half_type, $start_time, $end_time, $in_out, $remark, $edit_id);
        } else {
            $sql = "INSERT INTO leave_shifts (company_name, shift_name, leave_category, leave_half_day_type, leave_start_time, leave_end_time, in_out_time, remark) VALUES (?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $company, $shift_name, $category, $half_type, $start_time, $end_time, $in_out, $remark);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: leave-shift-creation.php?success=1");
        exit;
    }
}

$rows = [];
$r = $conn->query("SELECT * FROM leave_shifts ORDER BY id ASC");
if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;

// Companies & shifts for dropdowns
$companies = [];
$cr = @$conn->query("SELECT company_name FROM companies ORDER BY company_name");
if ($cr) while ($c = $cr->fetch_assoc()) $companies[] = $c['company_name'];

$shifts = [];
$sr = @$conn->query("SELECT shift_name FROM shifts ORDER BY shift_name");
if ($sr) while ($s = $sr->fetch_assoc()) $shifts[] = $s['shift_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Shift Creation | SMART HRIS™</title>
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
.form-grid{display:grid;grid-template-columns:180px 1fr;gap:14px 16px;align-items:center;max-width:520px}
label{font-size:13px;font-weight:500;color:#334155}
label .req{color:#ef4444}
select,input[type="time"]{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;width:100%;max-width:340px;background:#fff
}
select:focus,input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}

.btn-row{display:flex;gap:10px;margin-top:22px}
.btn{display:inline-flex;align-items:center;gap:8px;height:40px;padding:0 22px;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:#fff;font-family:inherit}
.btn-save{background:#2563eb}.btn-new{background:#94a3b8}
.btn:hover{filter:brightness(1.08)}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#f8fafc;border-bottom:2px solid var(--border)}
th{padding:12px 14px;text-align:left;font-weight:600;font-size:12px;color:#475569}
td{padding:11px 14px;border-bottom:1px solid #f1f5f9;color:#334155}
tr:hover{background:#f8fafc}
.btn-icon{border:none;background:transparent;cursor:pointer;font-size:14px;padding:4px 6px;border-radius:4px}
.btn-icon.edit{color:#2563eb}.btn-icon.del{color:#dc2626}
.btn-icon:hover{background:#f1f5f9}
.records{padding:10px 14px;background:#f8fafc;font-size:12.5px;color:#64748b;border-top:1px solid var(--border)}
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
<h1 class="page-title">Leave Shift Creation</h1>

<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Leave shift saved!</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert red"><i class="fas fa-trash"></i> Record deleted.</div><?php endif; ?>

<div class="form-card">
<form method="POST" id="leaveShiftForm">
<input type="hidden" name="action" value="save">
<input type="hidden" name="edit_id" id="edit_id" value="">

<div class="form-grid">
    <label>Company <span class="req">*</span></label>
    <select name="company_name" id="company_name" required>
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
        <?php if (empty($companies)): ?>
        <option value="Sipway Campus Pvt Ltd">Sipway Campus Pvt Ltd</option>
        <option value="Sipway Higher Education Pvt Ltd">Sipway Higher Education Pvt Ltd</option>
        <option value="Sipway Online School">Sipway Online School</option>
        <?php endif; ?>
    </select>

    <label>Shift Name <span class="req">*</span></label>
    <select name="shift_name" id="shift_name" required>
        <option value="">-select-</option>
        <?php foreach ($shifts as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
        <?php endforeach; ?>
        <?php if (empty($shifts)): ?>
        <option value="General Shift">General Shift</option>
        <option value="Evening Shift">Evening Shift</option>
        <option value="Morning Half Day Shift">Morning Half Day Shift</option>
        <option value="Work from home">Work from home</option>
        <?php endif; ?>
    </select>

    <label>Leave Category <span class="req">*</span></label>
    <select name="leave_category" id="leave_category" required>
        <option value="">-select-</option>
        <option value="Half Day">Half Day</option>
        <option value="Full Day">Full Day</option>
        <option value="Short Leave">Short Leave</option>
    </select>

    <label>Leave Half Day Type <span class="req">*</span></label>
    <select name="leave_half_day_type" id="leave_half_day_type" required>
        <option value="">-select-</option>
        <option value="1st Half Day">1st Half Day</option>
        <option value="2nd Half Day">2nd Half Day</option>
    </select>

    <label>Leave Shift Start Time <span class="req">*</span></label>
    <input type="time" name="leave_start_time" id="leave_start_time" step="1" required>

    <label>Leave Shift End Time <span class="req">*</span></label>
    <input type="time" name="leave_end_time" id="leave_end_time" step="1" required>

    <label>In Out Time <span class="req">*</span></label>
    <input type="time" name="in_out_time" id="in_out_time" step="1" required>
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-save"><i class="fas fa-save"></i> Save</button>
    <button type="button" class="btn btn-new" onclick="clearForm()"><i class="fas fa-file"></i> New</button>
</div>
</form>
</div>

<div class="table-wrap">
<table>
<thead>
<tr>
<th>Row #</th>
<th>Category</th>
<th>LeaveShiftStartTime</th>
<th>LeaveShiftEndTime</th>
<th>InOutTime</th>
<th>Remark</th>
<th>Company</th>
<th>Shift</th>
<th></th>
</tr>
</thead>
<tbody>
<?php if (empty($rows)): ?>
<tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:24px">No records yet.</td></tr>
<?php else: $i=1; foreach ($rows as $r): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($r['leave_category'] ?? '') ?></td>
<td><?= htmlspecialchars(substr($r['leave_start_time'] ?? '', 0, 8)) ?></td>
<td><?= htmlspecialchars(substr($r['leave_end_time'] ?? '', 0, 8)) ?></td>
<td><?= htmlspecialchars(substr($r['in_out_time'] ?? '', 0, 8)) ?></td>
<td><?= htmlspecialchars($r['remark'] ?? $r['leave_half_day_type'] ?? '') ?></td>
<td><?= htmlspecialchars($r['company_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['shift_name'] ?? '') ?></td>
<td>
    <button type="button" class="btn-icon edit" onclick='editRow(<?= json_encode($r) ?>)' title="Edit"><i class="fas fa-pen"></i></button>
    <button type="button" class="btn-icon del" onclick="if(confirm('Delete?')) location='?delete=<?= (int)$r['id'] ?>'" title="Delete"><i class="fas fa-trash"></i></button>
</td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
<div class="records"><?= count($rows) ?> Records Found</div>
</div>
</main>

<script>
function clearForm() {
    document.getElementById('leaveShiftForm').reset();
    document.getElementById('edit_id').value = '';
}
function tSub(v){ return (v||'').substring(0,8); }
function editRow(r) {
    document.getElementById('edit_id').value = r.id;
    document.getElementById('company_name').value = r.company_name || '';
    document.getElementById('shift_name').value = r.shift_name || '';
    document.getElementById('leave_category').value = r.leave_category || '';
    document.getElementById('leave_half_day_type').value = r.leave_half_day_type || '';
    document.getElementById('leave_start_time').value = tSub(r.leave_start_time);
    document.getElementById('leave_end_time').value = tSub(r.leave_end_time);
    document.getElementById('in_out_time').value = tSub(r.in_out_time);
    window.scrollTo({top:0, behavior:'smooth'});
}
</script>
</body>
</html>