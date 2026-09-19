<?php
include '../db_connect.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM shifts WHERE id = $id");
    header("Location: create-shifts.php?deleted=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $fields = [
        'checkin_start','checkin_end','shift_start','late_allow','early_depart',
        'checkout_start','shift_end','checkout_end','shift_name','short_name',
        'shift_type','is_active','must_checkin','must_checkout','shift_category',
        'shift_color','assign_break','ot_rate','workday_count','day_pass'
    ];
    $data = [];
    foreach ($fields as $f) {
        if (in_array($f, ['is_active','must_checkin','must_checkout','day_pass'])) {
            $data[$f] = $_POST[$f] ?? '0';
        } else {
            $data[$f] = trim($_POST[$f] ?? '');
        }
    }
    $edit_id = $_POST['edit_id'] ?? '';

    if ($data['shift_name'] !== '') {
        if (!empty($edit_id)) {
            $sql = "UPDATE shifts SET checkin_start=?, checkin_end=?, shift_start=?, late_allow=?, early_depart=?, checkout_start=?, shift_end=?, checkout_end=?, shift_name=?, short_name=?, shift_type=?, is_active=?, must_checkin=?, must_checkout=?, shift_category=?, shift_color=?, assign_break=?, ot_rate=?, workday_count=?, day_pass=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssiiisssssii",
                $data['checkin_start'],$data['checkin_end'],$data['shift_start'],$data['late_allow'],$data['early_depart'],
                $data['checkout_start'],$data['shift_end'],$data['checkout_end'],$data['shift_name'],$data['short_name'],
                $data['shift_type'],$data['is_active'],$data['must_checkin'],$data['must_checkout'],$data['shift_category'],
                $data['shift_color'],$data['assign_break'],$data['ot_rate'],$data['workday_count'],$data['day_pass'],$edit_id
            );
        } else {
            $sql = "INSERT INTO shifts (checkin_start,checkin_end,shift_start,late_allow,early_depart,checkout_start,shift_end,checkout_end,shift_name,short_name,shift_type,is_active,must_checkin,must_checkout,shift_category,shift_color,assign_break,ot_rate,workday_count,day_pass) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssiiisssssi",
                $data['checkin_start'],$data['checkin_end'],$data['shift_start'],$data['late_allow'],$data['early_depart'],
                $data['checkout_start'],$data['shift_end'],$data['checkout_end'],$data['shift_name'],$data['short_name'],
                $data['shift_type'],$data['is_active'],$data['must_checkin'],$data['must_checkout'],$data['shift_category'],
                $data['shift_color'],$data['assign_break'],$data['ot_rate'],$data['workday_count'],$data['day_pass']
            );
        }
        $stmt->execute();
        $stmt->close();
        header("Location: create-shifts.php?success=1");
        exit;
    }
}

$rows = [];
$r = $conn->query("SELECT * FROM shifts ORDER BY id ASC");
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
<title>Create Shifts | SMART HRIS™</title>
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
.container{max-width:1100px;margin:0 auto;padding:20px 18px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:18px;color:#1e293b}

/* Timeline visual */
.timeline-box{background:#f0f7ff;border:1px solid #bfdbfe;border-radius:12px;padding:20px 24px 28px;margin-bottom:22px;position:relative}
.tl-row{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:8px}
.tl-field{display:flex;flex-direction:column;gap:4px;min-width:120px}
.tl-field label{font-size:11px;font-weight:600;color:#1e40af}
.tl-field input{height:32px;border:1px solid #93c5fd;border-radius:6px;padding:0 8px;font-size:12px;background:#fef9c3;outline:none;width:130px}
.tl-field input:focus{border-color:#2563eb;background:#fff}
.tl-line{height:3px;background:linear-gradient(90deg,#f59e0b,#fbbf24,#f59e0b);border-radius:2px;margin:12px 0 4px;position:relative}
.tl-hints{display:flex;justify-content:space-between;font-size:10px;color:#64748b;margin-bottom:8px}
.tl-grace{display:flex;gap:24px;flex-wrap:wrap;margin-top:8px}
.tl-grace .g{display:flex;flex-direction:column;gap:3px}
.tl-grace label{font-size:11px;color:#15803d;font-weight:500}
.tl-grace input{height:30px;border:1px solid #86efac;border-radius:6px;padding:0 8px;width:140px;font-size:12px;outline:none}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:22px;margin-bottom:20px}
.form-grid{display:grid;grid-template-columns:160px 1fr;gap:12px 16px;align-items:center;max-width:560px}
label{font-size:12.5px;font-weight:500;color:#334155}
label .req{color:#ef4444}
input[type="text"],input[type="time"],input[type="number"],select{
    height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px;outline:none;font-family:inherit;width:100%;max-width:320px;background:#fff
}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(59,130,246,.12)}
input[type="color"]{height:34px;width:50px;padding:2px;cursor:pointer;border:1px solid #cbd5e1;border-radius:6px}
.hint{font-size:11px;color:#ef4444;grid-column:2}
.toggle{width:40px;height:22px;background:#cbd5e1;border-radius:20px;position:relative;cursor:pointer;transition:.2s;display:inline-block}
.toggle.on{background:#22c55e}
.toggle.red.on{background:#ef4444}
.toggle::after{content:'';position:absolute;width:18px;height:18px;background:#fff;border-radius:50%;top:2px;left:2px;transition:.2s}
.toggle.on::after{left:20px}
.toggle-row{display:flex;align-items:center;gap:16px}

.btn-row{display:flex;gap:10px;margin-top:18px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;width:64px;height:54px;border:none;border-radius:8px;cursor:pointer;font-size:11px;font-weight:500;color:#fff;gap:3px}
.btn i{font-size:16px}
.btn-save{background:#16a34a}.btn-new{background:#ea580c}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:auto}
table{width:100%;border-collapse:collapse;font-size:12px;min-width:1000px}
thead{background:#2563eb;color:#fff}
th{padding:9px 10px;text-align:left;font-weight:500;white-space:nowrap}
td{padding:8px 10px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.color-box{width:20px;height:20px;border-radius:4px;border:1px solid #e2e8f0;display:inline-block}
.btn-edit{background:#2563eb;color:#fff;border:none;padding:2px 8px;border-radius:4px;font-size:11px;cursor:pointer;margin:1px}
.btn-del{background:#dc2626;color:#fff;border:none;padding:2px 8px;border-radius:4px;font-size:11px;cursor:pointer;margin:1px}
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
<h1 class="page-title">Create Shifts</h1>

<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Shift saved!</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert red"><i class="fas fa-trash"></i> Shift deleted.</div><?php endif; ?>

<form method="POST" id="shiftForm">
<input type="hidden" name="action" value="save">
<input type="hidden" name="edit_id" id="edit_id" value="">
<input type="hidden" name="is_active" id="is_active" value="1">
<input type="hidden" name="must_checkin" id="must_checkin" value="0">
<input type="hidden" name="must_checkout" id="must_checkout" value="0">
<input type="hidden" name="day_pass" id="day_pass" value="0">

<!-- Timeline visual -->
<div class="timeline-box">
    <div class="tl-row">
        <div class="tl-field"><label>Check-In Start Time *</label><input type="time" name="checkin_start" id="checkin_start" step="1"></div>
        <div class="tl-field"><label>Check-In End Time *</label><input type="time" name="checkin_end" id="checkin_end" step="1"></div>
        <div class="tl-field"><label>Early Departure Grace Period (Allow Early) *</label><input type="text" name="early_depart" id="early_depart" placeholder="00:00:00"></div>
    </div>
    <div class="tl-field" style="margin:6px 0"><label>Shift Start time *</label><input type="time" name="shift_start" id="shift_start" step="1"></div>
    <div class="tl-line"></div>
    <div class="tl-grace">
        <div class="g"><label>Late Allow Grace Period (Allow Late) *</label><input type="text" name="late_allow" id="late_allow" placeholder="00:00:00"></div>
        <div class="g"><label>Check-Out Start time *</label><input type="time" name="checkout_start" id="checkout_start" step="1"></div>
        <div class="g"><label>Shift End time *</label><input type="time" name="shift_end" id="shift_end" step="1"></div>
        <div class="g"><label>Check-Out End time *</label><input type="time" name="checkout_end" id="checkout_end" step="1"></div>
    </div>
</div>

<div class="form-card">
<div class="form-grid">
    <label>Shift Name <span class="req">*</span></label>
    <input type="text" name="shift_name" id="shift_name" required>

    <label>Short Name</label>
    <input type="text" name="short_name" id="short_name" maxlength="5" placeholder="e.g. GS">
    <div class="hint">*Add only three characters</div>

    <label>Shift Type <span class="req">*</span></label>
    <select name="shift_type" id="shift_type"><option value="">-select-</option><option value="Day">Day</option><option value="Night">Night</option><option value="Flexible">Flexible</option><option value="Half Day">Half Day</option></select>

    <label>Is Active <span class="req">*</span></label>
    <div class="toggle on" id="tg_is_active" onclick="toggleSw(this,'is_active')"></div>

    <label>Must Check-In</label>
    <div class="toggle-row">
        <div class="toggle red" id="tg_must_checkin" onclick="toggleSw(this,'must_checkin')"></div>
        <span style="font-size:12px;color:#64748b;margin-left:8px">Must Check-Out</span>
        <div class="toggle red" id="tg_must_checkout" onclick="toggleSw(this,'must_checkout')"></div>
    </div>

    <label>Shift Category <span class="req">*</span></label>
    <select name="shift_category" id="shift_category"><option value="">-select-</option><option value="General">General</option><option value="OT">OT</option><option value="WFH">WFH</option><option value="Special">Special</option></select>

    <label>Shift Color <span class="req">*</span></label>
    <input type="color" name="shift_color" id="shift_color" value="#2563eb">

    <label>Assign Break <span class="req">*</span></label>
    <select name="assign_break" id="assign_break"><option value="">-select-</option><option value="None">None</option><option value="Lunch">Lunch</option><option value="Tea">Tea</option><option value="Both">Both</option></select>

    <label>OT Rate <span class="req">*</span></label>
    <select name="ot_rate" id="ot_rate"><option value="">-select-</option><option value="1.5">1.5</option><option value="2.0">2.0</option><option value="2.5">2.5</option><option value="3.0">3.0</option></select>

    <label>Work Day Count <span class="req">*</span></label>
    <input type="number" name="workday_count" id="workday_count" step="0.5" min="0" max="2">

    <label>Day Pass</label>
    <div>
        <div class="toggle red" id="tg_day_pass" onclick="toggleSw(this,'day_pass')"></div>
        <div class="hint" style="margin-top:4px">*Day time cross over shift</div>
    </div>
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-save"><i class="fas fa-save"></i>Save</button>
    <button type="button" class="btn btn-new" onclick="clearForm()"><i class="fas fa-file"></i>New</button>
</div>
</div>
</form>

<div class="table-wrap">
<table>
<thead>
<tr>
<th>ShiftID</th><th>Shift Name</th><th>BeginIn Time</th><th>Shift Start Time</th><th>Shift EndingIn</th>
<th>BeginOut Time</th><th>Shift End Time</th><th>Shift EndingOut</th><th>Is Active</th><th>Color</th><th>Short Name</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($rows)): ?>
<tr><td colspan="12" style="text-align:center;color:#94a3b8;padding:20px">No shifts yet.</td></tr>
<?php else: foreach ($rows as $r): ?>
<tr>
<td><?= (int)$r['id'] ?></td>
<td><?= htmlspecialchars($r['shift_name'] ?? '') ?></td>
<td><?= fmtT($r['checkin_start'] ?? '') ?></td>
<td><?= fmtT($r['shift_start'] ?? '') ?></td>
<td><?= fmtT($r['checkin_end'] ?? '') ?></td>
<td><?= fmtT($r['checkout_start'] ?? '') ?></td>
<td><?= fmtT($r['shift_end'] ?? '') ?></td>
<td><?= fmtT($r['checkout_end'] ?? '') ?></td>
<td><?= ($r['is_active'] ?? 0) ? 'Yes' : 'No' ?></td>
<td><span class="color-box" style="background:<?= htmlspecialchars($r['shift_color'] ?? '#000') ?>"></span></td>
<td><?= htmlspecialchars($r['short_name'] ?? '') ?></td>
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
function toggleSw(el,id){el.classList.toggle('on');document.getElementById(id).value=el.classList.contains('on')?'1':'0';}
function setT(id,v){document.getElementById(id).value=v==1?'1':'0';const el=document.getElementById('tg_'+id);if(el){if(v==1)el.classList.add('on');else el.classList.remove('on');}}
function clearForm(){
    document.getElementById('shiftForm').reset();
    document.getElementById('edit_id').value='';
    document.getElementById('shift_color').value='#2563eb';
    setT('is_active',1);setT('must_checkin',0);setT('must_checkout',0);setT('day_pass',0);
}
function tSub(v){return (v||'').substring(0,8);}
function editRow(r){
    document.getElementById('edit_id').value=r.id;
    document.getElementById('checkin_start').value=tSub(r.checkin_start);
    document.getElementById('checkin_end').value=tSub(r.checkin_end);
    document.getElementById('shift_start').value=tSub(r.shift_start);
    document.getElementById('late_allow').value=r.late_allow||'';
    document.getElementById('early_depart').value=r.early_depart||'';
    document.getElementById('checkout_start').value=tSub(r.checkout_start);
    document.getElementById('shift_end').value=tSub(r.shift_end);
    document.getElementById('checkout_end').value=tSub(r.checkout_end);
    document.getElementById('shift_name').value=r.shift_name||'';
    document.getElementById('short_name').value=r.short_name||'';
    document.getElementById('shift_type').value=r.shift_type||'';
    document.getElementById('shift_category').value=r.shift_category||'';
    document.getElementById('shift_color').value=r.shift_color||'#000000';
    document.getElementById('assign_break').value=r.assign_break||'';
    document.getElementById('ot_rate').value=r.ot_rate||'';
    document.getElementById('workday_count').value=r.workday_count||'';
    setT('is_active',r.is_active);setT('must_checkin',r.must_checkin);setT('must_checkout',r.must_checkout);setT('day_pass',r.day_pass);
    window.scrollTo({top:0,behavior:'smooth'});
}
</script>
</body>
</html>