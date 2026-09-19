<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS rosters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assign_type VARCHAR(20) DEFAULT 'employee',
    emp_group_name VARCHAR(150) DEFAULT NULL,
    year INT DEFAULT NULL,
    month INT DEFAULT NULL,
    day_date DATE DEFAULT NULL,
    shift_name VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$results = [];
$searched = false;
$emp_name = '';
$year = '';
$month = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') {
    $searched = true;
    $assign_type = $_POST['assign_type'] ?? 'employee';
    $emp_name    = trim($_POST['emp_group_name'] ?? '');
    $year        = (int)($_POST['year'] ?? 0);
    $month       = (int)($_POST['month'] ?? 0);

    // Load existing roster entries for this employee/year/month
    $sql = "SELECT * FROM rosters WHERE 1=1";
    $params = [];
    $types = '';
    if ($emp_name !== '') { $sql .= " AND emp_group_name LIKE ?"; $params[] = '%'.$emp_name.'%'; $types .= 's'; }
    if ($year) { $sql .= " AND year = ?"; $params[] = $year; $types .= 'i'; }
    if ($month) { $sql .= " AND month = ?"; $params[] = $month; $types .= 'i'; }
    $sql .= " ORDER BY day_date ASC";
    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $results[] = $row;
    $stmt->close();
}

// Save roster day
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_day') {
    $emp  = trim($_POST['emp_group_name'] ?? '');
    $yr   = (int)($_POST['year'] ?? 0);
    $mo   = (int)($_POST['month'] ?? 0);
    $day  = $_POST['day_date'] ?? '';
    $shift= $_POST['shift_name'] ?? '';
    if ($emp && $yr && $mo && $day && $shift) {
        // upsert
        $chk = $conn->prepare("SELECT id FROM rosters WHERE emp_group_name=? AND day_date=?");
        $chk->bind_param("ss", $emp, $day);
        $chk->execute();
        $ex = $chk->get_result()->fetch_assoc();
        $chk->close();
        if ($ex) {
            $u = $conn->prepare("UPDATE rosters SET shift_name=? WHERE id=?");
            $u->bind_param("si", $shift, $ex['id']);
            $u->execute();
            $u->close();
        } else {
            $i = $conn->prepare("INSERT INTO rosters (assign_type, emp_group_name, year, month, day_date, shift_name) VALUES ('employee',?,?,?,?,?)");
            $i->bind_param("siiss", $emp, $yr, $mo, $day, $shift);
            $i->execute();
            $i->close();
        }
    }
    header("Location: roster-creation.php?saved=1");
    exit;
}

$shifts = [];
$sr = @$conn->query("SELECT DISTINCT shift_name FROM shifts ORDER BY shift_name");
if ($sr) while ($s = $sr->fetch_assoc()) $shifts[] = $s['shift_name'];

$months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
$curYear = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Roster Creation | SMART HRIS™</title>
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
.container{max-width:960px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:20px;color:#1e293b}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px 28px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.radio-row{display:flex;align-items:center;gap:18px;margin-bottom:16px}
.radio-row label{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:500;cursor:pointer}

.field-row{display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap}
label.fld{font-size:13px;font-weight:500;color:#334155;min-width:100px}
label .req{color:#ef4444}
input[type="text"],select{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff
}
input[type="text"]{width:280px}
select{min-width:120px}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.btn-search{
    display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 18px;
    background:#2563eb;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;
    cursor:pointer;font-family:inherit
}
.btn-search:hover{background:#1d4ed8}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 14px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 14px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.empty{text-align:center;padding:32px;color:#94a3b8}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}

.day-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-top:16px}
.day-card{background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:12px;text-align:center}
.day-card .d{font-weight:600;font-size:14px;margin-bottom:6px}
.day-card select{width:100%;font-size:12px;height:32px}

@media(max-width:700px){.search-nav,.welcome{display:none}input[type="text"]{width:100%}}
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
<h1 class="page-title">Roster Creation</h1>

<?php if (isset($_GET['saved'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Roster saved!</div><?php endif; ?>

<div class="form-card">
<form method="POST">
<input type="hidden" name="action" value="search">

<div class="radio-row">
    <label><input type="radio" name="assign_type" value="employee" checked> Employee</label>
    <label><input type="radio" name="assign_type" value="group"> Group</label>
</div>

<div class="field-row">
    <label class="fld">By Employee <span class="req">*</span></label>
    <input type="text" name="emp_group_name" value="<?= htmlspecialchars($emp_name) ?>" placeholder="Employee name or number" required>
</div>

<div class="field-row">
    <label class="fld">Year <span class="req">*</span></label>
    <select name="year" required>
        <option value="">-select-</option>
        <?php for ($y = $curYear - 1; $y <= $curYear + 2; $y++): ?>
        <option value="<?= $y ?>" <?= (string)$year === (string)$y ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
    </select>

    <label class="fld" style="min-width:auto;margin-left:12px">Month <span class="req">*</span></label>
    <select name="month" required>
        <option value="">-select-</option>
        <?php foreach ($months as $num => $name): ?>
        <option value="<?= $num ?>" <?= (string)$month === (string)$num ? 'selected' : '' ?>><?= $name ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
</div>
</form>
</div>

<?php if ($searched && $year && $month): ?>
<?php
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $existing = [];
    foreach ($results as $r) {
        $existing[$r['day_date']] = $r['shift_name'];
    }
?>
<div class="form-card">
    <p style="font-weight:600;margin-bottom:12px;color:#334155">
        Roster for <strong><?= htmlspecialchars($emp_name) ?></strong> — <?= $months[$month] ?> <?= $year ?>
    </p>
    <div class="day-grid">
    <?php for ($d = 1; $d <= $daysInMonth; $d++):
        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $dayName = date('D', strtotime($dateStr));
        $curShift = $existing[$dateStr] ?? '';
    ?>
        <div class="day-card">
            <div class="d"><?= $d ?> <span style="font-weight:400;color:#94a3b8;font-size:11px"><?= $dayName ?></span></div>
            <form method="POST" style="margin:0">
                <input type="hidden" name="action" value="save_day">
                <input type="hidden" name="emp_group_name" value="<?= htmlspecialchars($emp_name) ?>">
                <input type="hidden" name="year" value="<?= $year ?>">
                <input type="hidden" name="month" value="<?= $month ?>">
                <input type="hidden" name="day_date" value="<?= $dateStr ?>">
                <select name="shift_name" onchange="this.form.submit()">
                    <option value="">- Off -</option>
                    <?php foreach ($shifts as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>" <?= $curShift === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                    <?php endforeach; ?>
                    <?php if (empty($shifts)): ?>
                    <option value="General Shift" <?= $curShift === 'General Shift' ? 'selected' : '' ?>>General Shift</option>
                    <option value="Evening Shift" <?= $curShift === 'Evening Shift' ? 'selected' : '' ?>>Evening Shift</option>
                    <option value="Work from home" <?= $curShift === 'Work from home' ? 'selected' : '' ?>>Work from home</option>
                    <?php endif; ?>
                </select>
            </form>
        </div>
    <?php endfor; ?>
    </div>
</div>

<?php if (!empty($results)): ?>
<div class="table-wrap" style="margin-top:16px">
<table>
<thead>
<tr><th>Date</th><th>Employee</th><th>Shift</th></tr>
</thead>
<tbody>
<?php foreach ($results as $r): ?>
<tr>
<td><?= htmlspecialchars($r['day_date'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_group_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['shift_name'] ?? '') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
<?php elseif ($searched): ?>
<div class="form-card"><div class="empty">Select Year and Month to view roster.</div></div>
<?php endif; ?>
</main>
</body>
</html>