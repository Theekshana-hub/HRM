<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS leave_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_no VARCHAR(50) DEFAULT NULL,
    emp_name VARCHAR(150) DEFAULT NULL,
    leave_year INT DEFAULT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    leave_category VARCHAR(50) DEFAULT NULL,
    leave_type VARCHAR(50) DEFAULT NULL,
    apply_date DATE DEFAULT NULL,
    from_date DATE DEFAULT NULL,
    from_time TIME DEFAULT NULL,
    to_date DATE DEFAULT NULL,
    to_time TIME DEFAULT NULL,
    days DECIMAL(6,2) DEFAULT 0,
    hours INT DEFAULT 0,
    minutes INT DEFAULT 0,
    status VARCHAR(30) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$tab = $_GET['tab'] ?? 'apply';
$message = '';
$messageType = '';

// Submit leave
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit') {
    $year     = (int)($_POST['leave_year'] ?? date('Y'));
    $emp_name = trim($_POST['employee_name'] ?? '');
    $reason   = trim($_POST['reason'] ?? '');
    $category = $_POST['leave_category'] ?? '';
    $type     = $_POST['leave_type'] ?? '';
    $from_d   = $_POST['from_date'] ?: null;
    $from_t   = $_POST['from_time'] ?: null;
    $to_d     = $_POST['to_date'] ?: null;
    $to_t     = $_POST['to_time'] ?: null;
    $days     = (float)($_POST['days'] ?? 0);
    $hours    = (int)($_POST['hours'] ?? 0);
    $mins     = (int)($_POST['minutes'] ?? 0);
    $apply_date = date('Y-m-d');

    if ($emp_name !== '' && $from_d && $to_d) {
        $sql = "INSERT INTO leave_applications (emp_name, leave_year, reason, leave_category, leave_type, apply_date, from_date, from_time, to_date, to_time, days, hours, minutes, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissssssssdii", $emp_name, $year, $reason, $category, $type, $apply_date, $from_d, $from_t, $to_d, $to_t, $days, $hours, $mins);
        $stmt->execute();
        $stmt->close();
        $message = 'Leave application submitted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Please fill Employee Name, From Date and To Date.';
        $messageType = 'error';
    }
}

// Bulk upload CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bulk') {
    if (!empty($_FILES['bulk_file']['tmp_name'])) {
        $handle = fopen($_FILES['bulk_file']['tmp_name'], 'r');
        $header = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;
            $emp = trim($row[0] ?? '');
            $type = trim($row[1] ?? '');
            $from = trim($row[2] ?? '');
            $to = trim($row[3] ?? '');
            $days = (float)(trim($row[4] ?? 0));
            if ($emp === '') continue;
            $stmt = $conn->prepare("INSERT INTO leave_applications (emp_name, leave_type, from_date, to_date, days, apply_date, status, leave_year) VALUES (?,?,?,?,?,?, 'Pending', ?)");
            $yr = (int)date('Y');
            $ad = date('Y-m-d');
            $stmt->bind_param("ssssdsi", $emp, $type, $from, $to, $days, $ad, $yr);
            $stmt->execute();
            $stmt->close();
            $count++;
        }
        fclose($handle);
        $message = "$count leave applications uploaded.";
        $messageType = 'success';
        $tab = 'bulk';
    }
}

$curYear = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Apply | SMART HRIS™</title>
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
.container{max-width:900px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:16px;color:#1e293b}

.tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:0}
.tab{padding:10px 16px;font-size:13px;font-weight:500;color:#64748b;text-decoration:none;border:1px solid transparent;border-bottom:none;border-radius:8px 8px 0 0}
.tab:hover{color:var(--accent)}
.tab.active{color:#0f172a;background:#fff;border-color:var(--border);margin-bottom:-2px;font-weight:600}

.panel{background:#fff;border:1px solid var(--border);border-top:none;border-radius:0 0 12px 12px;padding:24px 28px;box-shadow:0 1px 3px rgba(0,0,0,.04)}

.form-grid{display:grid;grid-template-columns:160px 1fr auto;gap:12px 14px;align-items:center;max-width:640px}
label{font-size:13px;font-weight:500;color:#334155}
label .req{color:#ef4444}
input[type="text"],input[type="date"],input[type="time"],input[type="number"],select{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff;width:100%;max-width:280px
}
input.emp-search{border-color:#f59e0b;max-width:320px}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}
input:disabled,select:disabled{background:#f1f5f9;color:#94a3b8}

.btn-search{height:36px;padding:0 18px;background:var(--orange);color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit}
.btn-search:hover{background:#c2410c}
.btn-validate{height:34px;padding:0 14px;background:#e2e8f0;color:#64748b;border:none;border-radius:7px;font-size:12px;cursor:pointer;font-family:inherit}
.btn-submit{height:38px;padding:0 28px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;margin-top:16px}
.btn-submit:hover{background:#1d4ed8}

.dt-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.dt-row input[type="date"]{max-width:150px}
.dt-row input[type="time"]{max-width:110px}
.small-inputs{display:flex;gap:16px;align-items:center;margin-top:8px;flex-wrap:wrap}
.small-inputs label{min-width:auto}
.small-inputs input{max-width:80px}

.lieu-bar{margin-top:28px;background:linear-gradient(135deg,#1e3a5f,#1e40af);color:#fff;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:8px}

.alert{padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
.alert.success{background:#dcfce7;color:#166534}
.alert.error{background:#fee2e2;color:#991b1b}

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
<h1 class="page-title">Leave Apply</h1>

<div class="tabs">
    <a href="?tab=apply" class="tab <?= $tab === 'apply' ? 'active' : '' ?>">Leave Application</a>
    <a href="?tab=bulk" class="tab <?= $tab === 'bulk' ? 'active' : '' ?>">Leave Application Bulk Upload</a>
</div>

<div class="panel">
<?php if ($message): ?><div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<?php if ($tab === 'apply'): ?>
<form method="POST" id="leaveForm">
<input type="hidden" name="action" value="submit">

<div class="form-grid">
    <label>Leave Registration Year <span class="req">*</span></label>
    <select name="leave_year" required>
        <?php for ($y = $curYear - 1; $y <= $curYear + 1; $y++): ?>
        <option value="<?= $y ?>" <?= $y === $curYear ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
    </select>
    <div></div>

    <label>Employee Name <span class="req">*</span></label>
    <input type="text" name="employee_name" class="emp-search" id="employee_name" placeholder="Type employee name..." required>
    <button type="button" class="btn-search" onclick="alert('Search employee from directory')">search</button>

    <label>Reason For Leave <span class="req">*</span></label>
    <input type="text" name="reason" id="reason" required>
    <div></div>

    <label>Leave Category</label>
    <select name="leave_category" id="leave_category">
        <option value="">-select-</option>
        <option value="Full Day">Full Day</option>
        <option value="Half Day">Half Day</option>
        <option value="Short Leave">Short Leave</option>
    </select>
    <div></div>

    <label>Leave Type</label>
    <select name="leave_type" id="leave_type">
        <option value="">-select-</option>
        <option value="Annual">Annual</option>
        <option value="Casual">Casual</option>
        <option value="Medical">Medical</option>
        <option value="No Pay">No Pay</option>
        <option value="Lieu">Lieu</option>
    </select>
    <div></div>

    <label>Leave application date</label>
    <span style="color:#64748b;font-size:13px"><?= date('Y-m-d') ?></span>
    <div></div>

    <label>From Date &amp; Time</label>
    <div class="dt-row">
        <input type="date" name="from_date" id="from_date" required>
        <input type="time" name="from_time" id="from_time">
    </div>
    <div></div>

    <label>To Date &amp; Time</label>
    <div class="dt-row">
        <input type="date" name="to_date" id="to_date" required>
        <input type="time" name="to_time" id="to_time">
        <button type="button" class="btn-validate" onclick="calcDays()">Validate</button>
    </div>
    <div></div>
</div>

<div class="small-inputs">
    <label>Days</label>
    <input type="number" name="days" id="days" step="0.5" min="0" value="0">
    <label>Hours</label>
    <input type="number" name="hours" id="hours" min="0" max="23" value="0">
    <label>Minutes</label>
    <input type="number" name="minutes" id="minutes" min="0" max="59" value="0">
</div>

<div style="text-align:center;margin-top:8px">
    <button type="submit" class="btn-submit">Submit</button>
</div>
</form>

<div class="lieu-bar"><i class="fas fa-calendar-check"></i> Lieu Leave Balance</div>

<?php else: /* bulk */ ?>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="bulk">
<p style="margin-bottom:14px;color:#475569;font-size:13px">Upload CSV with columns: <strong>Employee, Leave Type, From Date, To Date, Days</strong></p>
<input type="file" name="bulk_file" accept=".csv" required style="margin-bottom:14px">
<br>
<button type="submit" class="btn-submit">Upload Bulk Leaves</button>
</form>
<?php endif; ?>
</div>
</main>

<script>
function calcDays() {
    const from = document.getElementById('from_date').value;
    const to = document.getElementById('to_date').value;
    if (!from || !to) { alert('Select From and To dates'); return; }
    const d1 = new Date(from);
    const d2 = new Date(to);
    const diff = Math.round((d2 - d1) / (1000*60*60*24)) + 1;
    document.getElementById('days').value = diff > 0 ? diff : 0;
}
</script>
</body>
</html>