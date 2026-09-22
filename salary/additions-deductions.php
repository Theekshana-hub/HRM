<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS pay_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) DEFAULT NULL,
    mode VARCHAR(30) DEFAULT NULL,
    allowance_type VARCHAR(50) DEFAULT NULL,
    pay_item_name VARCHAR(150) DEFAULT NULL,
    is_include_epf TINYINT(1) DEFAULT 0,
    is_tax_applicable TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    zone_name VARCHAR(50) DEFAULT NULL,
    lump_sum TINYINT(1) DEFAULT 0,
    is_fix TINYINT(1) DEFAULT 0,
    classification VARCHAR(50) DEFAULT 'Normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed sample if empty
$cnt = 0;
$cr = @$conn->query("SELECT COUNT(*) AS c FROM pay_items");
if ($cr) $cnt = (int)$cr->fetch_assoc()['c'];
if ($cnt === 0) {
    $samples = [
        ['Sipway Campus Pvt Ltd','ADDITIONS','Other','Single OT',0,1,1,'Addition Zone',0,0,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Other','Double OT',0,1,1,'Addition Zone',0,0,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Based on Nopay','Travelling Allowance',0,1,1,'Addition Zone',0,1,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Other','Lecture Fee',0,1,1,'Addition Zone',0,1,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Based on Nopay','Attendance Allowance',0,1,1,'Addition Zone',0,1,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Other','Performance Allowance',0,1,1,'Addition Zone',0,0,'Normal'],
        ['Sipway Campus Pvt Ltd','DEDUCTIONS','Other','Late',1,0,1,'EPF Zone',0,0,'Normal'],
        ['Sipway Campus Pvt Ltd','DEDUCTIONS','Other','Pay cut',1,0,1,'EPF Zone',0,0,'Normal'],
        ['Sipway Campus Pvt Ltd','DEDUCTIONS','Other','Welfare',0,0,1,'Deduction Zone',0,1,'Normal'],
        ['Sipway Campus Pvt Ltd','DEDUCTIONS','Other','Salary Advance',0,0,1,'Deduction Zone',0,0,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Other','Salary Adjustment',0,0,1,'Addition Zone',0,0,'Normal'],
        ['Sipway Campus Pvt Ltd','DEDUCTIONS','Other','Other deductions',0,0,1,'Deduction Zone',0,0,'Normal'],
        ['Sipway Campus Pvt Ltd','DEDUCTIONS','Other','Loan Deductions',0,0,1,'Deduction Zone',0,0,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Other','Fuel Allowance',0,0,1,'Addition Zone',0,1,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Other','Performance Based Allowance',0,0,1,'Addition Zone',0,1,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Other','Allowance from Thrimana Advertising',0,0,1,'Addition Zone',0,1,'Normal'],
        ['Sipway Campus Pvt Ltd','ADDITIONS','Other','Allowance from Sipway Campus',0,0,1,'Addition Zone',0,1,'Normal'],
    ];
    $st = $conn->prepare("INSERT INTO pay_items (company_name, mode, allowance_type, pay_item_name, is_include_epf, is_tax_applicable, is_active, zone_name, lump_sum, is_fix, classification) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($samples as $s) {
        $st->bind_param("ssssiiiisis", $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10]);
        $st->execute();
    }
    $st->close();
}

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_row = null;
if ($edit_id > 0) {
    $st = $conn->prepare("SELECT * FROM pay_items WHERE id=?");
    $st->bind_param("i", $edit_id);
    $st->execute();
    $edit_row = $st->get_result()->fetch_assoc();
    $st->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $company = trim($_POST['company_name'] ?? '');
    $mode = trim($_POST['mode'] ?? '');
    $atype = trim($_POST['allowance_type'] ?? '');
    $name = trim($_POST['pay_item_name'] ?? '');
    $epf = isset($_POST['is_include_epf']) ? 1 : 0;
    $tax = isset($_POST['is_tax_applicable']) ? 1 : 0;
    $active = isset($_POST['is_active']) ? 1 : 0;
    $zone = trim($_POST['zone_name'] ?? '');
    $lump = isset($_POST['lump_sum']) ? 1 : 0;
    $fix = isset($_POST['is_fix']) ? 1 : 0;
    $class = trim($_POST['classification'] ?? 'Normal');

    if ($company && $mode && $name && $zone && $class) {
        if ($id > 0) {
            $st = $conn->prepare("UPDATE pay_items SET company_name=?, mode=?, allowance_type=?, pay_item_name=?, is_include_epf=?, is_tax_applicable=?, is_active=?, zone_name=?, lump_sum=?, is_fix=?, classification=? WHERE id=?");
            $st->bind_param("ssssiiisiisi", $company, $mode, $atype, $name, $epf, $tax, $active, $zone, $lump, $fix, $class, $id);
        } else {
            $st = $conn->prepare("INSERT INTO pay_items (company_name, mode, allowance_type, pay_item_name, is_include_epf, is_tax_applicable, is_active, zone_name, lump_sum, is_fix, classification) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $st->bind_param("ssssiiisiis", $company, $mode, $atype, $name, $epf, $tax, $active, $zone, $lump, $fix, $class);
        }
        $st->execute();
        $st->close();
        header("Location: additions-deductions.php?saved=1");
        exit;
    }
}

$rows = [];
$r = $conn->query("SELECT * FROM pay_items ORDER BY id ASC LIMIT 300");
if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;

$companies = [];
$cr = @$conn->query("SELECT company_name FROM companies ORDER BY company_name");
if ($cr) while ($c = $cr->fetch_assoc()) $companies[] = $c['company_name'];
if (empty($companies)) $companies = ['Sipway Campus Pvt Ltd', 'Sipway Higher Education Pvt Ltd'];

function yn($v) { return $v ? 'Yes' : 'No'; }
function on($row, $key, $default = 0) {
    if (!$row) return $default;
    return !empty($row[$key]) ? 1 : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pay Item Registration | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0;--green:#16a34a;--orange:#ea580c}
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

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px 28px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px 24px;align-items:start}
.field label{display:block;font-size:12.5px;font-weight:500;color:#334155;margin-bottom:6px}
.field label .req{color:#ef4444}
.field input[type="text"],.field select{
    width:100%;height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff
}
.field input:focus,.field select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}

/* Toggle switch */
.toggle-wrap{display:flex;align-items:center;gap:10px;margin-top:4px}
.toggle{position:relative;width:44px;height:24px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0}
.slider{
    position:absolute;cursor:pointer;inset:0;background:#ef4444;border-radius:24px;transition:.2s
}
.slider:before{
    content:"";position:absolute;height:18px;width:18px;left:3px;bottom:3px;
    background:#fff;border-radius:50%;transition:.2s
}
.toggle input:checked + .slider{background:#22c55e}
.toggle input:checked + .slider:before{transform:translateX(20px)}

.btn-row{display:flex;gap:10px;margin-top:22px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;width:64px;height:54px;border:none;border-radius:8px;cursor:pointer;font-size:11px;font-weight:600;color:#fff;font-family:inherit}
.btn i{font-size:16px}
.btn-save{background:var(--green)}.btn-new{background:var(--orange)}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}

.table-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,.04)}
table{width:100%;border-collapse:collapse;font-size:12px}
thead{background:#2563eb;color:#fff}
th{padding:10px 10px;text-align:left;font-weight:500;white-space:nowrap}
td{padding:9px 10px;border-bottom:1px solid #e8eef5}
tr:nth-child(even){background:#eef4fb}
tr:nth-child(odd){background:#f8fafc}
.edit-link{display:inline-block;background:#2563eb;color:#fff;padding:3px 12px;border-radius:6px;font-size:11px;text-decoration:none;font-weight:500}
.edit-link:hover{background:#1d4ed8}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}

@media(max-width:900px){.grid3{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.grid3{grid-template-columns:1fr}.search-nav,.welcome{display:none}}
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
<h1 class="page-title">Pay Item Registration</h1>

<?php if (isset($_GET['saved'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Pay item saved!</div><?php endif; ?>

<div class="form-card">
<form method="POST">
<input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?= (int)($edit_row['id'] ?? 0) ?>">

<div class="grid3">
    <div class="field">
        <label>Company Name <span class="req">*</span></label>
        <select name="company_name" required>
            <option value="">-select-</option>
            <?php foreach ($companies as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= ($edit_row['company_name'] ?? '') === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Mode <span class="req">*</span></label>
        <select name="mode" required>
            <option value="">-select-</option>
            <option value="ADDITIONS" <?= ($edit_row['mode'] ?? '') === 'ADDITIONS' ? 'selected' : '' ?>>ADDITIONS</option>
            <option value="DEDUCTIONS" <?= ($edit_row['mode'] ?? '') === 'DEDUCTIONS' ? 'selected' : '' ?>>DEDUCTIONS</option>
        </select>
    </div>
    <div class="field">
        <label>Allowance Type <span class="req">*</span></label>
        <select name="allowance_type" required>
            <option value="">-select-</option>
            <option value="Other" <?= ($edit_row['allowance_type'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
            <option value="Based on Nopay" <?= ($edit_row['allowance_type'] ?? '') === 'Based on Nopay' ? 'selected' : '' ?>>Based on Nopay</option>
            <option value="Based on Attendance" <?= ($edit_row['allowance_type'] ?? '') === 'Based on Attendance' ? 'selected' : '' ?>>Based on Attendance</option>
        </select>
    </div>

    <div class="field">
        <label>Pay Item Name <span class="req">*</span></label>
        <input type="text" name="pay_item_name" required value="<?= htmlspecialchars($edit_row['pay_item_name'] ?? '') ?>">
    </div>
    <div class="field">
        <label>Is Include EPF</label>
        <div class="toggle-wrap">
            <label class="toggle">
                <input type="checkbox" name="is_include_epf" value="1" <?= on($edit_row, 'is_include_epf') ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <div class="field">
        <label>Is Tax Applicable</label>
        <div class="toggle-wrap">
            <label class="toggle">
                <input type="checkbox" name="is_tax_applicable" value="1" <?= on($edit_row, 'is_tax_applicable') ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <div class="field">
        <label>Is Active</label>
        <div class="toggle-wrap">
            <label class="toggle">
                <input type="checkbox" name="is_active" value="1" <?= !$edit_row || on($edit_row, 'is_active', 1) ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <div class="field">
        <label>Zone <span class="req">*</span></label>
        <select name="zone_name" required>
            <option value="">-select-</option>
            <?php foreach (['Addition Zone','Deduction Zone','EPF Zone'] as $z): ?>
            <option value="<?= $z ?>" <?= ($edit_row['zone_name'] ?? '') === $z ? 'selected' : '' ?>><?= $z ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>LumpSum Payment</label>
        <div class="toggle-wrap">
            <label class="toggle">
                <input type="checkbox" name="lump_sum" value="1" <?= on($edit_row, 'lump_sum') ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <div class="field">
        <label>Is Fix</label>
        <div class="toggle-wrap">
            <label class="toggle">
                <input type="checkbox" name="is_fix" value="1" <?= on($edit_row, 'is_fix') ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <div class="field"></div>
    <div class="field">
        <label>Payitem Classification <span class="req">*</span></label>
        <select name="classification" required>
            <option value="">-select-</option>
            <?php foreach (['Normal','Special','Statutory'] as $cl): ?>
            <option value="<?= $cl ?>" <?= ($edit_row['classification'] ?? 'Normal') === $cl ? 'selected' : '' ?>><?= $cl ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="btn-row">
    <button type="submit" class="btn btn-save"><i class="fas fa-save"></i>Save</button>
    <a href="additions-deductions.php" class="btn btn-new"><i class="fas fa-file"></i>New</a>
</div>
</form>
</div>

<div class="table-wrap">
<table>
<thead>
<tr>
<th>Allowances No</th>
<th>Allowance Name</th>
<th>IsActive</th>
<th>IsFix</th>
<th>Mode</th>
<th>Include EPF</th>
<th>Zone Name</th>
<th>Allowance Type</th>
<th>Tax Applicability</th>
<th>Lump Sum</th>
<th>Payitem Classification</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($rows)): ?>
<tr><td colspan="12" style="text-align:center;padding:28px;color:#94a3b8">No pay items found.</td></tr>
<?php else: foreach ($rows as $r): ?>
<tr>
<td><?= (int)$r['id'] ?></td>
<td><?= htmlspecialchars($r['pay_item_name'] ?? '') ?></td>
<td><?= yn($r['is_active'] ?? 0) ?></td>
<td><?= yn($r['is_fix'] ?? 0) ?></td>
<td><?= htmlspecialchars($r['mode'] ?? '') ?></td>
<td><?= yn($r['is_include_epf'] ?? 0) ?></td>
<td><?= htmlspecialchars($r['zone_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['allowance_type'] ?? '') ?></td>
<td><?= yn($r['is_tax_applicable'] ?? 0) ?></td>
<td><?= yn($r['lump_sum'] ?? 0) ?></td>
<td><?= htmlspecialchars($r['classification'] ?? '') ?></td>
<td><a href="?edit=<?= (int)$r['id'] ?>" class="edit-link">Edit</a></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</main>
</body>
</html>