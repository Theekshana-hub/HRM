<?php
include '../db_connect.php';

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM holidays WHERE id = $id");
    header("Location: create-holidays.php?deleted=1");
    exit;
}

// Save / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $holiday_name      = trim($_POST['holiday_name'] ?? '');
    $allow_early       = $_POST['allow_early'] ?? '';
    $must_checkin      = $_POST['must_checkin'] ?? '0';
    $day_pass          = $_POST['day_pass'] ?? '0';
    $begin_in_time     = $_POST['begin_in_time'] ?? null;
    $holiday_end_time  = $_POST['holiday_end_time'] ?? null;
    $must_checkout     = $_POST['must_checkout'] ?? '0';
    $ot_rate           = $_POST['ot_rate'] ?? '';
    $holiday_start_time= $_POST['holiday_start_time'] ?? null;
    $ending_out        = $_POST['ending_out'] ?? null;
    $shift_type        = $_POST['shift_type'] ?? '';
    $workday_count     = $_POST['workday_count'] ?? '';
    $allow_late        = $_POST['allow_late'] ?? '';
    $is_active         = $_POST['is_active'] ?? '0';
    $holiday_type      = $_POST['holiday_type'] ?? '';
    $is_paid           = $_POST['is_paid'] ?? '0';
    $ending_in         = $_POST['ending_in'] ?? null;
    $assign_break      = $_POST['assign_break'] ?? '';
    $holiday_category  = $_POST['holiday_category'] ?? '';
    $description       = $_POST['description'] ?? '';
    $begin_out         = $_POST['begin_out'] ?? null;
    $shift_category    = $_POST['shift_category'] ?? '';
    $holiday_color     = $_POST['holiday_color'] ?? '#000000';
    $edit_id           = $_POST['edit_id'] ?? '';

    if ($holiday_name !== '') {
        if (!empty($edit_id)) {
            $sql = "UPDATE holidays SET holiday_name=?, allow_early=?, must_checkin=?, day_pass=?, begin_in_time=?, holiday_end_time=?, must_checkout=?, ot_rate=?, holiday_start_time=?, ending_out=?, shift_type=?, workday_count=?, allow_late=?, is_active=?, holiday_type=?, is_paid=?, ending_in=?, assign_break=?, holiday_category=?, description=?, begin_out=?, shift_category=?, holiday_color=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiissssssssssissssssssi",
                $holiday_name, $allow_early, $must_checkin, $day_pass, $begin_in_time, $holiday_end_time,
                $must_checkout, $ot_rate, $holiday_start_time, $ending_out, $shift_type, $workday_count,
                $allow_late, $is_active, $holiday_type, $is_paid, $ending_in, $assign_break,
                $holiday_category, $description, $begin_out, $shift_category, $holiday_color, $edit_id
            );
        } else {
            $sql = "INSERT INTO holidays (holiday_name, allow_early, must_checkin, day_pass, begin_in_time, holiday_end_time, must_checkout, ot_rate, holiday_start_time, ending_out, shift_type, workday_count, allow_late, is_active, holiday_type, is_paid, ending_in, assign_break, holiday_category, description, begin_out, shift_category, holiday_color) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiissssssssssissssssss",
                $holiday_name, $allow_early, $must_checkin, $day_pass, $begin_in_time, $holiday_end_time,
                $must_checkout, $ot_rate, $holiday_start_time, $ending_out, $shift_type, $workday_count,
                $allow_late, $is_active, $holiday_type, $is_paid, $ending_in, $assign_break,
                $holiday_category, $description, $begin_out, $shift_category, $holiday_color
            );
        }
        $stmt->execute();
        $stmt->close();
        header("Location: create-holidays.php?success=1");
        exit;
    }
}

$rows = [];
$r = $conn->query("SELECT * FROM holidays ORDER BY id ASC");
if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;

function fmtTime($t) {
    if (!$t) return '';
    return date('g:i A', strtotime($t));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Holidays | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',system-ui,sans-serif;background:var(--page-bg);color:#0f172a;font-size:13px;min-height:100vh}
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
.container{max-width:1100px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:20px;color:#1e293b}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px}
.form-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px 20px}
.form-group{display:flex;flex-direction:column;gap:5px}
label{font-size:12px;font-weight:500;color:#334155}
label .req{color:#ef4444}
input[type="text"],input[type="time"],input[type="number"],select,textarea{
    height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;
    font-size:13px;outline:none;font-family:inherit;width:100%;background:#fff
}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(59,130,246,.12)}
input[type="color"]{height:34px;padding:2px 4px;cursor:pointer;width:60px}
.toggle-row{display:flex;align-items:center;gap:8px;margin-top:4px}
.toggle{width:40px;height:22px;background:#cbd5e1;border-radius:20px;position:relative;cursor:pointer;transition:.2s}
.toggle.on{background:#22c55e}
.toggle.red.on{background:#ef4444}
.toggle::after{content:'';position:absolute;width:18px;height:18px;background:#fff;border-radius:50%;top:2px;left:2px;transition:.2s}
.toggle.on::after{left:20px}

.btn-row{display:flex;gap:10px;margin-top:20px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;width:64px;height:54px;border:none;border-radius:8px;cursor:pointer;font-size:11px;font-weight:500;color:#fff;gap:3px}
.btn i{font-size:16px}
.btn-save{background:#16a34a}.btn-new{background:#ea580c}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:auto}
table{width:100%;border-collapse:collapse;font-size:12.5px;min-width:900px}
thead{background:#2563eb;color:#fff}
th{padding:10px 12px;text-align:left;font-weight:500;white-space:nowrap}
td{padding:9px 12px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
tr:hover{background:#eff6ff}
.color-box{width:22px;height:22px;border-radius:4px;border:1px solid #e2e8f0;display:inline-block}
.btn-edit{background:#2563eb;color:#fff;border:none;padding:3px 10px;border-radius:4px;font-size:11px;cursor:pointer;margin:1px}
.btn-del{background:#dc2626;color:#fff;border:none;padding:3px 10px;border-radius:4px;font-size:11px;cursor:pointer;margin:1px}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px}
.alert.red{background:#fee2e2;color:#991b1b}

@media(max-width:900px){.form-grid{grid-template-columns:1fr 1fr}.search-nav,.welcome{display:none}}
@media(max-width:600px){.form-grid{grid-template-columns:1fr}}
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
<h1 class="page-title">Create Holidays</h1>

<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Holiday saved successfully!</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert red"><i class="fas fa-trash"></i> Holiday deleted.</div><?php endif; ?>

<div class="form-card">
<form method="POST" id="holidayForm">
<input type="hidden" name="action" value="save">
<input type="hidden" name="edit_id" id="edit_id" value="">
<input type="hidden" name="must_checkin" id="must_checkin" value="0">
<input type="hidden" name="day_pass" id="day_pass" value="0">
<input type="hidden" name="must_checkout" id="must_checkout" value="0">
<input type="hidden" name="is_active" id="is_active" value="1">
<input type="hidden" name="is_paid" id="is_paid" value="0">

<div class="form-grid">
    <div class="form-group"><label>Holiday Name <span class="req">*</span></label><input type="text" name="holiday_name" id="holiday_name" required></div>
    <div class="form-group"><label>Allow Early <small>(hh:mm:ss)</small> <span class="req">*</span></label><input type="text" name="allow_early" id="allow_early" placeholder="00:00:00"></div>
    <div class="form-group"><label>Must CheckIN</label><div class="toggle-row"><div class="toggle red" id="tg_must_checkin" onclick="toggleSw(this,'must_checkin')"></div></div></div>
    <div class="form-group"><label>Day Pass</label><div class="toggle-row"><div class="toggle red" id="tg_day_pass" onclick="toggleSw(this,'day_pass')"></div></div></div>

    <div class="form-group"><label>Begin In Time <span class="req">*</span></label><input type="time" name="begin_in_time" id="begin_in_time" step="1"></div>
    <div class="form-group"><label>Holiday End Time <span class="req">*</span></label><input type="time" name="holiday_end_time" id="holiday_end_time" step="1"></div>
    <div class="form-group"><label>Must CheckOUT</label><div class="toggle-row"><div class="toggle red" id="tg_must_checkout" onclick="toggleSw(this,'must_checkout')"></div></div></div>
    <div class="form-group"><label>OT Rate <span class="req">*</span></label>
        <select name="ot_rate" id="ot_rate"><option value="">-select-</option><option value="1.5">1.5</option><option value="2.0">2.0</option><option value="2.5">2.5</option><option value="3.0">3.0</option></select>
    </div>

    <div class="form-group"><label>Holiday Start Time <span class="req">*</span></label><input type="time" name="holiday_start_time" id="holiday_start_time" step="1"></div>
    <div class="form-group"><label>Ending Out <span class="req">*</span></label><input type="time" name="ending_out" id="ending_out" step="1"></div>
    <div class="form-group"><label>Shift Type <span class="req">*</span></label>
        <select name="shift_type" id="shift_type"><option value="">-select-</option><option value="Full Day">Full Day</option><option value="Half Day">Half Day</option><option value="Custom">Custom</option></select>
    </div>
    <div class="form-group"><label>WorkDay Count <span class="req">*</span></label><input type="number" name="workday_count" id="workday_count" step="0.5" min="0"></div>

    <div class="form-group"><label>Allow Late <small>(hh:mm:ss)</small> <span class="req">*</span></label><input type="text" name="allow_late" id="allow_late" placeholder="00:00:00"></div>
    <div class="form-group"><label>Is Active Holiday <span class="req">*</span></label><div class="toggle-row"><div class="toggle on" id="tg_is_active" onclick="toggleSw(this,'is_active')"></div></div></div>
    <div class="form-group"><label>Holiday Type <span class="req">*</span></label>
        <select name="holiday_type" id="holiday_type"><option value="">-select-</option><option value="Public">Public</option><option value="Company">Company</option><option value="Religious">Religious</option><option value="National">National</option></select>
    </div>
    <div class="form-group"><label>Is Paid Holiday</label><div class="toggle-row"><div class="toggle red" id="tg_is_paid" onclick="toggleSw(this,'is_paid')"></div></div></div>

    <div class="form-group"><label>Ending In <span class="req">*</span></label><input type="time" name="ending_in" id="ending_in" step="1"></div>
    <div class="form-group"><label>Assign Break <span class="req">*</span></label>
        <select name="assign_break" id="assign_break"><option value="">-select-</option><option value="None">None</option><option value="Lunch">Lunch</option><option value="Tea">Tea</option></select>
    </div>
    <div class="form-group"><label>Holiday Category <span class="req">*</span></label>
        <select name="holiday_category" id="holiday_category"><option value="">-select-</option><option value="Statutory">Statutory</option><option value="Optional">Optional</option><option value="Restricted">Restricted</option></select>
    </div>
    <div class="form-group"><label>Description</label><input type="text" name="description" id="description"></div>

    <div class="form-group"><label>Begin Out <span class="req">*</span></label><input type="time" name="begin_out" id="begin_out" step="1"></div>
    <div class="form-group"><label>Shift Category <span class="req">*</span></label>
        <select name="shift_category" id="shift_category"><option value="">-select-</option><option value="General">General</option><option value="Holiday">Holiday</option><option value="Special">Special</option></select>
    </div>
    <div class="form-group"><label>Holiday Color <span class="req">*</span></label><input type="color" name="holiday_color" id="holiday_color" value="#ef4444"></div>
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
<th>Shift Name</th>
<th>BeginIn Time</th>
<th>Shift Start Time</th>
<th>Shift EndingIn</th>
<th>BeginOut Time</th>
<th>Shift End Time</th>
<th>Shift EndingOut</th>
<th>IsActive</th>
<th>Color</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($rows)): ?>
<tr><td colspan="10" style="text-align:center;color:#94a3b8;padding:24px">No holidays yet. Add one above.</td></tr>
<?php else: foreach ($rows as $r): ?>
<tr>
<td><?= htmlspecialchars($r['holiday_name'] ?? '') ?></td>
<td><?= fmtTime($r['begin_in_time'] ?? '') ?></td>
<td><?= fmtTime($r['holiday_start_time'] ?? '') ?></td>
<td><?= fmtTime($r['ending_in'] ?? '') ?></td>
<td><?= fmtTime($r['begin_out'] ?? '') ?></td>
<td><?= fmtTime($r['holiday_end_time'] ?? '') ?></td>
<td><?= fmtTime($r['ending_out'] ?? '') ?></td>
<td><?= ($r['is_active'] ?? 0) ? 'Yes' : 'No' ?></td>
<td><span class="color-box" style="background:<?= htmlspecialchars($r['holiday_color'] ?? '#000') ?>"></span></td>
<td>
    <button type="button" class="btn-del" onclick="if(confirm('Delete this holiday?')) location='?delete=<?= (int)$r['id'] ?>'">Delete</button>
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
function setToggle(id, val) {
    document.getElementById(id).value = val == 1 ? '1' : '0';
    const el = document.getElementById('tg_' + id);
    if (el) { if (val == 1) el.classList.add('on'); else el.classList.remove('on'); }
}
function clearForm() {
    document.getElementById('holidayForm').reset();
    document.getElementById('edit_id').value = '';
    document.getElementById('holiday_color').value = '#ef4444';
    setToggle('must_checkin', 0);
    setToggle('day_pass', 0);
    setToggle('must_checkout', 0);
    setToggle('is_active', 1);
    setToggle('is_paid', 0);
    document.getElementById('holiday_name').focus();
}
function editRow(r) {
    document.getElementById('edit_id').value = r.id;
    document.getElementById('holiday_name').value = r.holiday_name || '';
    document.getElementById('allow_early').value = r.allow_early || '';
    document.getElementById('begin_in_time').value = (r.begin_in_time || '').substring(0,8);
    document.getElementById('holiday_end_time').value = (r.holiday_end_time || '').substring(0,8);
    document.getElementById('ot_rate').value = r.ot_rate || '';
    document.getElementById('holiday_start_time').value = (r.holiday_start_time || '').substring(0,8);
    document.getElementById('ending_out').value = (r.ending_out || '').substring(0,8);
    document.getElementById('shift_type').value = r.shift_type || '';
    document.getElementById('workday_count').value = r.workday_count || '';
    document.getElementById('allow_late').value = r.allow_late || '';
    document.getElementById('holiday_type').value = r.holiday_type || '';
    document.getElementById('ending_in').value = (r.ending_in || '').substring(0,8);
    document.getElementById('assign_break').value = r.assign_break || '';
    document.getElementById('holiday_category').value = r.holiday_category || '';
    document.getElementById('description').value = r.description || '';
    document.getElementById('begin_out').value = (r.begin_out || '').substring(0,8);
    document.getElementById('shift_category').value = r.shift_category || '';
    document.getElementById('holiday_color').value = r.holiday_color || '#000000';
    setToggle('must_checkin', r.must_checkin);
    setToggle('day_pass', r.day_pass);
    setToggle('must_checkout', r.must_checkout);
    setToggle('is_active', r.is_active);
    setToggle('is_paid', r.is_paid);
    window.scrollTo({top:0, behavior:'smooth'});}
</script>
</body>
</html>