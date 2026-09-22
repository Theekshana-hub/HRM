<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS fix_additions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) DEFAULT NULL,
    emp_no VARCHAR(50) DEFAULT NULL,
    emp_name VARCHAR(150) DEFAULT NULL,
    addition_name VARCHAR(150) DEFAULT NULL,
    amount DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$tab = $_GET['tab'] ?? 'upload';
$message = '';
$messageType = '';

$companies = [];
$cr = @$conn->query("SELECT company_name FROM companies ORDER BY company_name");
if ($cr) while ($c = $cr->fetch_assoc()) $companies[] = $c['company_name'];
if (empty($companies)) $companies = ['Sipway Campus Pvt Ltd', 'Sipway Higher Education Pvt Ltd'];

$fixItems = [];
$pr = @$conn->query("SELECT pay_item_name FROM pay_items WHERE mode='ADDITIONS' AND is_fix=1 ORDER BY pay_item_name");
if ($pr) while ($p = $pr->fetch_assoc()) $fixItems[] = $p['pay_item_name'];
if (empty($fixItems)) {
    $fixItems = ['Travelling Allowance', 'Lecture Fee', 'Attendance Allowance', 'Fuel Allowance', 'Performance Based Allowance'];
}

// Download Excel
if (isset($_GET['download'])) {
    $withData = ($_GET['with_data'] ?? '0') === '1';
    $company = $_GET['company'] ?? '';
    $addition = $_GET['addition'] ?? '';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="fix_addition_' . ($withData ? 'data' : 'template') . '.csv"');
    echo "Emp No,Employee Name,Addition Name,Amount\n";
    if ($withData) {
        $sql = "SELECT * FROM fix_additions WHERE 1=1";
        if ($company) $sql .= " AND company_name='" . $conn->real_escape_string($company) . "'";
        if ($addition) $sql .= " AND addition_name='" . $conn->real_escape_string($addition) . "'";
        $sql .= " ORDER BY emp_no LIMIT 500";
        $r = $conn->query($sql);
        if ($r) while ($row = $r->fetch_assoc()) {
            echo '"' . $row['emp_no'] . '","' . $row['emp_name'] . '","' . $row['addition_name'] . '",' . $row['amount'] . "\n";
        }
    } else {
        echo ",,,\n";
    }
    exit;
}

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    $company = trim($_POST['company_name'] ?? '');
    $addition = trim($_POST['fix_addition'] ?? '');
    if (!empty($_FILES['excel_file']['tmp_name']) && $company && $addition) {
        $handle = fopen($_FILES['excel_file']['tmp_name'], 'r');
        $header = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $emp_no = trim($row[0] ?? '');
            $emp_name = trim($row[1] ?? '');
            $add_name = trim($row[2] ?? $addition);
            $amount = (float)(trim($row[3] ?? 0));
            if ($emp_no === '') continue;
            $st = $conn->prepare("INSERT INTO fix_additions (company_name, emp_no, emp_name, addition_name, amount) VALUES (?,?,?,?,?)");
            $st->bind_param("ssssd", $company, $emp_no, $emp_name, $add_name, $amount);
            $st->execute();
            $st->close();
            $count++;
        }
        fclose($handle);
        $message = "$count records uploaded.";
        $messageType = 'success';
        $tab = 'manage';
    } else {
        $message = 'Select Company, Fix Addition and file.';
        $messageType = 'error';
    }
}

// Manual save (management)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_one') {
    $company = trim($_POST['company_name'] ?? '');
    $emp_no = trim($_POST['emp_no'] ?? '');
    $emp_name = trim($_POST['emp_name'] ?? '');
    $addition = trim($_POST['addition_name'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    if ($company && $emp_no && $addition) {
        $st = $conn->prepare("INSERT INTO fix_additions (company_name, emp_no, emp_name, addition_name, amount) VALUES (?,?,?,?,?)");
        $st->bind_param("ssssd", $company, $emp_no, $emp_name, $addition, $amount);
        $st->execute();
        $st->close();
        $message = 'Record saved.';
        $messageType = 'success';
        $tab = 'manage';
    }
}

// Bulk delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bulk_delete') {
    $company = trim($_POST['del_company'] ?? '');
    $addition = trim($_POST['del_addition'] ?? '');
    $sql = "DELETE FROM fix_additions WHERE 1=1";
    if ($company) $sql .= " AND company_name='" . $conn->real_escape_string($company) . "'";
    if ($addition) $sql .= " AND addition_name='" . $conn->real_escape_string($addition) . "'";
    $conn->query($sql);
    $message = 'Matching records deleted.';
    $messageType = 'success';
    $tab = 'delete';
}

// Delete one
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM fix_additions WHERE id=$id");
    header("Location: fix-additions.php?tab=manage&deleted=1");
    exit;
}

// Search results
$filter_company = $_POST['filter_company'] ?? $_GET['company'] ?? '';
$filter_employee = trim($_POST['filter_employee'] ?? '');
$filter_addition = $_POST['filter_addition'] ?? '';
$results = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') {
    $searched = true;
    $sql = "SELECT * FROM fix_additions WHERE 1=1";
    $params = []; $types = '';
    if ($filter_company) { $sql .= " AND company_name=?"; $params[] = $filter_company; $types .= 's'; }
    if ($filter_employee) { $sql .= " AND (emp_no LIKE ? OR emp_name LIKE ?)"; $like = '%'.$filter_employee.'%'; $params[] = $like; $params[] = $like; $types .= 'ss'; }
    if ($filter_addition) { $sql .= " AND addition_name=?"; $params[] = $filter_addition; $types .= 's'; }
    $sql .= " ORDER BY id DESC LIMIT 300";
    $st = $conn->prepare($sql);
    if ($params) $st->bind_param($types, ...$params);
    $st->execute();
    $res = $st->get_result();
    while ($row = $res->fetch_assoc()) $results[] = $row;
    $st->close();
    $tab = 'manage';
}

// Default list for manage tab
if ($tab === 'manage' && !$searched) {
    $r = $conn->query("SELECT * FROM fix_additions ORDER BY id DESC LIMIT 100");
    if ($r) while ($row = $r->fetch_assoc()) $results[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fix Additions | SMART HRIS™</title>
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

.tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:0}
.tab{padding:10px 16px;font-size:13px;font-weight:500;color:#64748b;text-decoration:none;border:1px solid transparent;border-bottom:none;border-radius:8px 8px 0 0}
.tab:hover{color:var(--accent)}
.tab.active{color:#0f172a;background:#fff;border-color:var(--border);margin-bottom:-2px;font-weight:600}

.panel{background:#fff;border:1px solid var(--border);border-top:none;border-radius:0 0 12px 12px;padding:24px 28px;box-shadow:0 1px 3px rgba(0,0,0,.04);margin-bottom:20px}
.panel-title{font-size:15px;font-weight:600;margin-bottom:18px;color:#1e293b;padding-bottom:10px;border-bottom:1px solid #f1f5f9}

.form-grid{display:grid;grid-template-columns:120px 1fr;gap:12px 16px;align-items:center;max-width:560px}
label{font-size:13px;font-weight:500;color:#334155}
label .req{color:#ef4444}
select,input[type="text"],input[type="number"]{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff;width:100%;max-width:360px
}
select:focus,input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}

.check-row{display:flex;gap:24px;margin:14px 0;flex-wrap:wrap}
.check-row label{display:flex;align-items:center;gap:8px;font-weight:400;cursor:pointer}

.btn-row{display:flex;gap:10px;margin-top:16px;flex-wrap:wrap}
.btn{height:38px;padding:0 18px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;color:#fff}
.btn-blue{background:#2563eb}.btn-blue:hover{background:#1d4ed8}
.btn-gray{background:#94a3b8}.btn-gray:hover{background:#64748b}
.btn-red{background:#dc2626}.btn-red:hover{background:#b91c1c}
.btn-search{background:#2563eb;margin:16px auto 0;display:flex}

.search-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:22px 28px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-top:16px}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 12px;text-align:left;font-weight:500}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
.alert{padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
.alert.success{background:#dcfce7;color:#166534}
.alert.error{background:#fee2e2;color:#991b1b}
.empty{text-align:center;padding:24px;color:#94a3b8}
.del-link{color:#dc2626;text-decoration:none;font-size:12px}
.del-link:hover{text-decoration:underline}

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
<div class="tabs">
    <a href="?tab=upload" class="tab <?= $tab === 'upload' ? 'active' : '' ?>">Fix Addition Bulk Upload</a>
    <a href="?tab=manage" class="tab <?= $tab === 'manage' ? 'active' : '' ?>">Fix Addition Management</a>
    <a href="?tab=delete" class="tab <?= $tab === 'delete' ? 'active' : '' ?>">Fix Addition Bulk Delete</a>
</div>

<div class="panel">
<?php if ($message): ?><div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert success">Record deleted.</div><?php endif; ?>

<?php if ($tab === 'upload'): ?>
<h2 class="panel-title">Fix Addition Bulk Upload</h2>
<form method="POST" enctype="multipart/form-data" id="uploadForm">
<input type="hidden" name="action" value="upload">
<div class="form-grid">
    <label>Company <span class="req">*</span></label>
    <select name="company_name" id="company_name" required>
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>
    <label>fix Addition <span class="req">*</span></label>
    <select name="fix_addition" id="fix_addition" required>
        <option value="">-select-</option>
        <?php foreach ($fixItems as $f): ?>
        <option value="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="check-row">
    <label><input type="radio" name="dl_type" value="0" checked id="dl_empty"> Download with out Data</label>
    <label><input type="radio" name="dl_type" value="1" id="dl_data"> Download with Data</label>
</div>
<div class="btn-row">
    <button type="button" class="btn btn-blue" onclick="doDownload()"><i class="fas fa-download"></i> Download Excel</button>
    <label class="btn btn-blue" style="cursor:pointer">
        <i class="fas fa-upload"></i> Upload Excel
        <input type="file" name="excel_file" accept=".csv" style="display:none" onchange="this.form.submit()">
    </label>
    <a href="?tab=upload" class="btn btn-gray"><i class="fas fa-file"></i> New</a>
</div>
</form>

<?php elseif ($tab === 'manage'): ?>
<h2 class="panel-title">Fix Addition Management</h2>
<form method="POST" style="margin-bottom:20px">
<input type="hidden" name="action" value="save_one">
<div class="form-grid">
    <label>Company <span class="req">*</span></label>
    <select name="company_name" required>
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Emp No <span class="req">*</span></label>
    <input type="text" name="emp_no" required>
    <label>Employee Name</label>
    <input type="text" name="emp_name">
    <label>Addition <span class="req">*</span></label>
    <select name="addition_name" required>
        <option value="">-select-</option>
        <?php foreach ($fixItems as $f): ?>
        <option value="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Amount</label>
    <input type="number" name="amount" step="0.01" value="0">
</div>
<div class="btn-row">
    <button type="submit" class="btn btn-blue"><i class="fas fa-save"></i> Save</button>
</div>
</form>
<?php endif; ?>

<?php if ($tab === 'delete'): ?>
<h2 class="panel-title">Fix Addition Bulk Delete</h2>
<form method="POST">
<input type="hidden" name="action" value="bulk_delete">
<div class="form-grid">
    <label>Company</label>
    <select name="del_company">
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Addition</label>
    <select name="del_addition">
        <option value="">-select-</option>
        <?php foreach ($fixItems as $f): ?>
        <option value="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="btn-row">
    <button type="submit" class="btn btn-red" onclick="return confirm('Delete matching fix additions?')"><i class="fas fa-trash"></i> Delete</button>
</div>
</form>
<?php endif; ?>
</div>

<!-- Search section (screenshot bottom) -->
<div class="search-card">
<form method="POST">
<input type="hidden" name="action" value="search">
<div class="form-grid">
    <label>Company</label>
    <select name="filter_company">
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= $filter_company === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Employee</label>
    <input type="text" name="filter_employee" value="<?= htmlspecialchars($filter_employee) ?>" placeholder="Emp no or name">
    <label>Addition</label>
    <select name="filter_addition">
        <option value="">-select-</option>
        <?php foreach ($fixItems as $f): ?>
        <option value="<?= htmlspecialchars($f) ?>" <?= $filter_addition === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<button type="submit" class="btn btn-search"><i class="fas fa-search"></i> Search</button>
</form>

<?php if (!empty($results) || $searched): ?>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>ID</th>
<th>Company</th>
<th>Emp No</th>
<th>Employee</th>
<th>Addition</th>
<th>Amount</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if (empty($results)): ?>
<tr><td colspan="7" class="empty">No records found.</td></tr>
<?php else: foreach ($results as $r): ?>
<tr>
<td><?= (int)$r['id'] ?></td>
<td><?= htmlspecialchars($r['company_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['emp_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['addition_name'] ?? '') ?></td>
<td><?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
<td><a href="?tab=manage&delete=<?= (int)$r['id'] ?>" class="del-link" onclick="return confirm('Delete?')">Delete</a></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
</main>
<script>
function doDownload() {
    const c = document.getElementById('company_name').value;
    const a = document.getElementById('fix_addition').value;
    const withData = document.getElementById('dl_data').checked ? '1' : '0';
    window.location = '?download=1&with_data=' + withData + '&company=' + encodeURIComponent(c) + '&addition=' + encodeURIComponent(a);
}
</script>
</body>
</html>