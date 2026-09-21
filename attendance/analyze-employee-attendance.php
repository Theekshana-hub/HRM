<?php
include '../db_connect.php';
require_once __DIR__ . '/manual_helpers.php';

$results = [];
$processed = false;
$active_tab = $_POST['tab'] ?? $_GET['tab'] ?? 'single';
$selected_emp = trim($_POST['employee'] ?? '');
$selected_emp_name = trim($_POST['employee_name'] ?? '');
$from_date = $_POST['from_date'] ?? '';
$to_date = $_POST['to_date'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'process') {
    $processed = true;

    /* ---------- 1. attendance_analysis table eken (kalin wage) ---------- */
    if (mu_tableExists($conn, 'attendance_analysis')) {
        $sql = "SELECT * FROM attendance_analysis WHERE 1=1";
        $params = [];
        $types = '';
        if ($selected_emp !== '') {
            $sql .= " AND (emp_no LIKE ? OR emp_name LIKE ?)";
            $like = '%' . $selected_emp . '%';
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }
        if ($from_date !== '') {
            $sql .= " AND attendance_date >= ?";
            $params[] = $from_date;
            $types .= 's';
        }
        if ($to_date !== '') {
            $sql .= " AND attendance_date <= ?";
            $params[] = $to_date;
            $types .= 's';
        }
        $sql .= " ORDER BY attendance_date ASC LIMIT 300";
        $results = mu_run($conn, $sql, $types, $params);
    }

    /* ---------- 2. Manual attendance (Apply form + Excel upload) ---------- */
    $map = mu_employeeMap($conn);
    $manual = array_merge(
        mu_fetchApply($conn, $selected_emp, $from_date, $to_date, 300),
        mu_fetchDaily($conn, $map, $selected_emp, $from_date, $to_date, 300)
    );
    foreach ($manual as $m) {
        // Employee select karapu nisa emp_no eka exact match wenna one (emp_no thiyena rows walata)
        if ($selected_emp !== '' && !empty($m['emp_no']) && (string)$m['emp_no'] !== $selected_emp) continue;

        $results[] = [
            'emp_no'          => $m['emp_no'],
            'emp_name'        => $m['emp_name'],
            'attendance_date' => $m['attendance_date'],
            'in_time'         => $m['in_time'],
            'out_time'        => $m['out_time'],
            'work_hours'      => $m['work_hours'],
            'late_minutes'    => '-',          // shift time nathi nisa manual records walata late calculate karanna ba
            'status'          => $m['status'],
        ];
    }

    /* ---------- 3. Deka ekathu karala date eken (paran idan aluth) sort karanawa ---------- */
    usort($results, function ($a, $b) {
        $d = strcmp((string)($a['attendance_date'] ?? ''), (string)($b['attendance_date'] ?? ''));
        return $d !== 0 ? $d : strcmp((string)($a['in_time'] ?? ''), (string)($b['in_time'] ?? ''));
    });
    $results = array_slice($results, 0, 300);
}

// Employee list for select modal / datalist
$employees = [];
$empCheck = $conn->query("SHOW TABLES LIKE 'employees'");
if ($empCheck && $empCheck->num_rows > 0) {
    $er = $conn->query("SELECT emp_no, name_with_initials, full_name FROM employees ORDER BY emp_no LIMIT 500");
    if ($er) while ($e = $er->fetch_assoc()) $employees[] = $e;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analyze Employee Attendance | SMART HRIS™</title>
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
.page-title{font-size:18px;font-weight:600;margin-bottom:16px;color:#1e293b}

/* Tabs */
.tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:24px}
.tab{
    padding:10px 20px;font-size:13px;font-weight:500;color:#64748b;cursor:pointer;
    border:1px solid transparent;border-bottom:none;border-radius:8px 8px 0 0;
    background:transparent;text-decoration:none;transition:all .2s
}
.tab:hover{color:var(--accent);background:#f8fafc}
.tab.active{
    color:var(--accent);background:#fff;border-color:var(--border);border-bottom:2px solid #fff;
    margin-bottom:-2px;font-weight:600
}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:28px;margin-bottom:22px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.form-grid{display:grid;grid-template-columns:110px 1fr auto;gap:14px 14px;align-items:center;max-width:560px}
.form-grid .full{grid-column:1 / -1}
label{font-size:13px;font-weight:500;color:#334155}
label .req{color:#ef4444}
input[type="text"],input[type="date"]{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;width:100%;max-width:280px;background:#fff
}
input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.btn-select{
    height:36px;padding:0 18px;background:#2563eb;color:#fff;border:none;border-radius:7px;
    font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;white-space:nowrap
}
.btn-select:hover{background:#1d4ed8}
.emp-display{
    grid-column:2 / 3;font-size:13px;color:#0f172a;font-weight:500;min-height:20px
}
.date-row{display:contents}
.btn-row{display:flex;gap:10px;margin-top:22px}
.btn{
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    width:68px;height:58px;border:none;border-radius:10px;cursor:pointer;
    font-size:11px;font-weight:600;color:#fff;gap:3px;font-family:inherit;transition:all .2s
}
.btn i{font-size:18px}
.btn-process{background:var(--green)}
.btn-new{background:var(--orange)}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}

/* Modal */
.modal-overlay{
    display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:200;
    align-items:center;justify-content:center
}
.modal-overlay.show{display:flex}
.modal{
    background:#fff;border-radius:12px;width:90%;max-width:480px;max-height:70vh;
    display:flex;flex-direction:column;box-shadow:0 20px 40px rgba(0,0,0,.2)
}
.modal-header{
    padding:14px 18px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;font-weight:600;font-size:14px
}
.modal-header button{border:none;background:transparent;font-size:18px;cursor:pointer;color:#64748b}
.modal-body{padding:12px 18px;overflow-y:auto}
.modal-search{width:100%;height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;margin-bottom:12px;font-size:13px;outline:none}
.emp-item{
    padding:10px 12px;border-radius:8px;cursor:pointer;font-size:13px;
    display:flex;justify-content:space-between;align-items:center
}
.emp-item:hover{background:#eff6ff}
.emp-item span{color:#64748b;font-size:12px}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:12px 14px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:11px 14px;border-bottom:1px solid #f1f5f9;color:#334155}
tr:nth-child(even){background:#f8fafc}
tr:hover{background:#eff6ff}
.empty-state{text-align:center;padding:40px 20px;color:#94a3b8;font-size:13.5px}
.empty-state i{font-size:36px;margin-bottom:12px;display:block;color:#cbd5e1}
.result-count{font-size:12.5px;color:#64748b;margin-bottom:10px}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px}

@media(max-width:700px){
    .form-grid{grid-template-columns:1fr}
    .search-nav,.welcome{display:none}
    input[type="text"],input[type="date"]{max-width:100%}
}
</style>
</head>
<body>
<nav class="navbar">
<div class="nav-left">
<a href="../index.php" class="brand"><div class="brand-icon"><i class="fas fa-th-large"></i></div><span>SMART HRIS <sup style="font-size:8px;opacity:.7">™</sup></span></a>
<a href="index.php" class="nav-btn" title="Back"><i class="fas fa-arrow-left"></i></a>
<a href="../index.php" class="nav-btn" title="Home"><i class="fas fa-home"></i></a>
<button class="support-btn">Support <i class="fas fa-chevron-down" style="font-size:9px"></i></button>
</div>
<div class="search-nav"><i class="fas fa-search"></i><input type="text" placeholder="Search..."></div>
<div class="nav-right"><span class="welcome">Welcome <strong>Shakila</strong></span><button class="nav-btn"><i class="fas fa-cog"></i></button><div class="avatar">S</div></div>
</nav>

<main class="container">
<h1 class="page-title">Analyze Employee Attendance</h1>

<!-- Tabs -->
<div class="tabs">
    <a href="?tab=single" class="tab <?= $active_tab === 'single' ? 'active' : '' ?>">Analyze Single Employee Attendance</a>
    <a href="?tab=bulk" class="tab <?= $active_tab === 'bulk' ? 'active' : '' ?>">Analyze Employee Attendance</a>
</div>

<?php if ($processed): ?>
<div class="alert"><i class="fas fa-check-circle"></i> Attendance analysis processed successfully.</div>
<?php endif; ?>

<div class="form-card">
<form method="POST" action="" id="analyzeForm">
<input type="hidden" name="action" value="process">
<input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
<input type="hidden" name="employee_name" id="employee_name" value="<?= htmlspecialchars($selected_emp_name) ?>">

<div class="form-grid">
    <label>Employee <span class="req">*</span></label>
    <input type="text" name="employee" id="employee" value="<?= htmlspecialchars($selected_emp) ?>"
           placeholder="Emp No or Name" required readonly
           style="background:#f8fafc;cursor:pointer" onclick="openEmpModal()">
    <button type="button" class="btn-select" onclick="openEmpModal()">Select</button>

    <label>Employee <span class="req">*</span></label>
    <div class="emp-display" id="empDisplay">
        <?= $selected_emp_name ? htmlspecialchars($selected_emp_name) : '<span style="color:#94a3b8">No employee selected</span>' ?>
    </div>
    <div></div>

    <label>From Date <span class="req">*</span></label>
    <input type="date" name="from_date" id="from_date" value="<?= htmlspecialchars($from_date) ?>" required>
    <div></div>

    <label>To Date <span class="req">*</span></label>
    <input type="date" name="to_date" id="to_date" value="<?= htmlspecialchars($to_date) ?>" required>
    <div></div>
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-process"><i class="fas fa-sync-alt"></i>Process</button>
    <button type="button" class="btn btn-new" onclick="clearForm()"><i class="fas fa-file"></i>New</button>
</div>
</form>
</div>

<?php if ($processed): ?>
<p class="result-count"><i class="fas fa-list"></i> <?= count($results) ?> record(s) found</p>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Emp No</th>
<th>Employee Name</th>
<th>Date</th>
<th>In Time</th>
<th>Out Time</th>
<th>Work Hours</th>
<th>Late (min)</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php if (empty($results)): ?>
<tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox"></i>No attendance data found for the selected employee and date range.</div></td></tr>
<?php else: foreach ($results as $r): ?>
<tr>
<td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['attendance_date'] ?? '') ?></td>
<td><?= htmlspecialchars($r['in_time'] ?? '') ?></td>
<td><?= htmlspecialchars($r['out_time'] ?? '') ?></td>
<td><?= htmlspecialchars($r['work_hours'] ?? '') ?></td>
<td><?= htmlspecialchars($r['late_minutes'] ?? '0') ?></td>
<td><?= htmlspecialchars($r['status'] ?? '') ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</main>

<!-- Employee Select Modal -->
<div class="modal-overlay" id="empModal">
    <div class="modal">
        <div class="modal-header">
            <span>Select Employee</span>
            <button type="button" onclick="closeEmpModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="text" class="modal-search" id="empSearch" placeholder="Search by name or emp no..." oninput="filterEmp()">
            <div id="empList">
                <?php if (empty($employees)): ?>
                <div class="emp-item" onclick="pickEmp('EMP001','Kamal Perera')"><strong>EMP001</strong><span>Kamal Perera</span></div>
                <div class="emp-item" onclick="pickEmp('EMP002','Nimal Silva')"><strong>EMP002</strong><span>Nimal Silva</span></div>
                <div class="emp-item" onclick="pickEmp('EMP003','Sunil Fernando')"><strong>EMP003</strong><span>Sunil Fernando</span></div>
                <?php else: foreach ($employees as $e):
                    $name = $e['name_with_initials'] ?? $e['full_name'] ?? '';
                    $no = $e['emp_no'] ?? '';
                ?>
                <div class="emp-item" data-search="<?= htmlspecialchars(strtolower($no.' '.$name)) ?>" onclick="pickEmp('<?= htmlspecialchars($no) ?>','<?= htmlspecialchars($name) ?>')">
                    <strong><?= htmlspecialchars($no) ?></strong>
                    <span><?= htmlspecialchars($name) ?></span>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function openEmpModal(){ document.getElementById('empModal').classList.add('show'); document.getElementById('empSearch').focus(); }
function closeEmpModal(){ document.getElementById('empModal').classList.remove('show'); }
function pickEmp(no, name){
    document.getElementById('employee').value = no;
    document.getElementById('employee_name').value = name;
    document.getElementById('empDisplay').innerHTML = name + ' <span style="color:#64748b">(' + no + ')</span>';
    closeEmpModal();
}
function filterEmp(){
    const q = document.getElementById('empSearch').value.toLowerCase();
    document.querySelectorAll('#empList .emp-item').forEach(el => {
        const s = el.getAttribute('data-search') || el.textContent.toLowerCase();
        el.style.display = s.includes(q) ? '' : 'none';
    });
}
function clearForm(){
    document.getElementById('employee').value = '';
    document.getElementById('employee_name').value = '';
    document.getElementById('empDisplay').innerHTML = '<span style="color:#94a3b8">No employee selected</span>';
    document.getElementById('from_date').value = '';
    document.getElementById('to_date').value = '';
}
document.getElementById('empModal').addEventListener('click', function(e){
    if (e.target === this) closeEmpModal();
});
</script>
</body>
</html>