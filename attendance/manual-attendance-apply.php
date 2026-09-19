<?php
include '../db_connect.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $emp_name   = trim($_POST['employee_name'] ?? '');
    $remarks    = trim($_POST['employee_remarks'] ?? '');
    $check_mode = $_POST['check_mode'] ?? '';
    $ci_date    = $_POST['checkin_date'] ?: null;
    $ci_time    = $_POST['checkin_time'] ?: null;
    $co_date    = $_POST['checkout_date'] ?: null;
    $co_time    = $_POST['checkout_time'] ?: null;
    $break_mode = $_POST['break_mode'] ?? '';
    $bi_date    = $_POST['breakin_date'] ?: null;
    $bi_time    = $_POST['breakin_time'] ?: null;
    $bo_date    = $_POST['breakout_date'] ?: null;
    $bo_time    = $_POST['breakout_time'] ?: null;

    if ($emp_name !== '') {
        $sql = "INSERT INTO manual_attendance (employee_name, employee_remarks, check_mode, checkin_date, checkin_time, checkout_date, checkout_time, break_mode, breakin_date, breakin_time, breakout_date, breakout_time) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssss", $emp_name, $remarks, $check_mode, $ci_date, $ci_time, $co_date, $co_time, $break_mode, $bi_date, $bi_time, $bo_date, $bo_time);
        $stmt->execute();
        $stmt->close();
        header("Location: manual-attendance-apply.php?success=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply Manual Attendance | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0;--green:#16a34a;--orange:#ea580c}
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
.container{max-width:720px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:20px;color:#1e293b}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px 28px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.field-row{display:flex;align-items:center;gap:10px;margin-bottom:14px}
label.fld{font-size:13px;font-weight:500;color:#334155;min-width:130px}
.input-search{position:relative;flex:1;max-width:320px}
.input-search input{width:100%;height:36px;border:1px solid #f59e0b;border-radius:7px;padding:0 36px 0 12px;font-size:13px;outline:none;font-family:inherit}
.input-search i{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#f59e0b}
input[type="text"],input[type="date"],input[type="time"]{
    height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;
    font-size:13px;outline:none;font-family:inherit;background:#fff
}
input[type="text"]{width:180px}
input:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(59,130,246,.12)}

.section-head{font-size:15px;font-weight:700;color:#1e293b;margin:20px 0 12px;line-height:1.2}
.mode-btns{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
.mode-btn{
    height:34px;padding:0 16px;border:1px solid #cbd5e1;border-radius:8px;
    background:#e2e8f0;color:#64748b;font-size:12.5px;font-weight:500;cursor:pointer;font-family:inherit;transition:all .15s
}
.mode-btn.active{background:#2563eb;color:#fff;border-color:#2563eb}
.mode-btn:hover:not(.active){background:#cbd5e1}

.dt-row{display:flex;align-items:center;gap:10px;margin-bottom:10px;flex-wrap:wrap}
.dt-row label{font-size:12.5px;font-weight:500;color:#475569;min-width:140px}

.btn-row{display:flex;gap:10px;margin-top:22px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;width:64px;height:54px;border:none;border-radius:8px;cursor:pointer;font-size:11px;font-weight:600;color:#fff;gap:3px;font-family:inherit}
.btn i{font-size:16px}
.btn-save{background:var(--green)}.btn-new{background:var(--orange)}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}

.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}

@media(max-width:700px){.search-nav,.welcome{display:none}.field-row{flex-direction:column;align-items:flex-start}}
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
<h1 class="page-title">Apply Manual Attendance</h1>

<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Manual attendance saved!</div><?php endif; ?>

<div class="form-card">
<form method="POST" id="manualForm">
<input type="hidden" name="action" value="save">
<input type="hidden" name="check_mode" id="check_mode" value="">
<input type="hidden" name="break_mode" id="break_mode" value="">

<div class="field-row">
    <label class="fld">Employee Name :</label>
    <div class="input-search">
        <input type="text" name="employee_name" id="employee_name" placeholder="Search employee..." required>
        <i class="fas fa-search"></i>
    </div>
</div>

<div class="field-row">
    <label class="fld">Employee Remarks :</label>
    <input type="text" name="employee_remarks" id="employee_remarks" placeholder="Remarks">
</div>

<!-- Check In/Out -->
<div class="section-head">Check<br>In/Out</div>
<div class="mode-btns">
    <button type="button" class="mode-btn" data-group="check" data-val="Check In" onclick="setMode(this)">Check In</button>
    <button type="button" class="mode-btn" data-group="check" data-val="Check Out" onclick="setMode(this)">Check Out</button>
    <button type="button" class="mode-btn" data-group="check" data-val="Both" onclick="setMode(this)">Both</button>
    <button type="button" class="mode-btn" data-group="check" data-val="With Break" onclick="setMode(this)">With Break</button>
</div>

<div class="dt-row">
    <label>Check In Date &amp; Time :</label>
    <input type="date" name="checkin_date" id="checkin_date">
    <input type="time" name="checkin_time" id="checkin_time" step="1">
</div>
<div class="dt-row">
    <label>Check Out Date &amp; Time :</label>
    <input type="date" name="checkout_date" id="checkout_date">
    <input type="time" name="checkout_time" id="checkout_time" step="1">
</div>

<!-- Break In/Out -->
<div class="section-head">Break<br>In/Out</div>
<div class="mode-btns">
    <button type="button" class="mode-btn" data-group="break" data-val="Break In" onclick="setMode(this)">Break In</button>
    <button type="button" class="mode-btn" data-group="break" data-val="BreakOut" onclick="setMode(this)">BreakOut</button>
    <button type="button" class="mode-btn" data-group="break" data-val="Both" onclick="setMode(this)">Both</button>
</div>

<div class="dt-row">
    <label>Break In Date &amp; Time :</label>
    <input type="date" name="breakin_date" id="breakin_date">
    <input type="time" name="breakin_time" id="breakin_time" step="1">
</div>
<div class="dt-row">
    <label>Break Out Date &amp; Time :</label>
    <input type="date" name="breakout_date" id="breakout_date">
    <input type="time" name="breakout_time" id="breakout_time" step="1">
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-save"><i class="fas fa-save"></i>Save</button>
    <button type="button" class="btn btn-new" onclick="clearForm()"><i class="fas fa-file"></i>New</button>
</div>
</form>
</div>
</main>

<script>
function setMode(btn) {
    const group = btn.dataset.group;
    document.querySelectorAll('.mode-btn[data-group="'+group+'"]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (group === 'check') document.getElementById('check_mode').value = btn.dataset.val;
    else document.getElementById('break_mode').value = btn.dataset.val;
}
function clearForm() {
    document.getElementById('manualForm').reset();
    document.getElementById('check_mode').value = '';
    document.getElementById('break_mode').value = '';
    document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
}
</script>
</body>
</html>