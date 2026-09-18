<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $company_name = $_POST['company_name'] ?? '';
    $division_name = $_POST['division_name'] ?? '';
    $sub_division_name = $_POST['sub_division_name'] ?? '';
    $department_no = $_POST['department_no'] ?? '';
    $department_name = $_POST['department_name'] ?? '';
    $edit_id = $_POST['edit_id'] ?? '';

    if (!empty($department_name)) {
        if (!empty($edit_id)) {
            $sql = "UPDATE departments SET company_name=?, division_name=?, sub_division_name=?, department_no=?, department_name=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $company_name, $division_name, $sub_division_name, $department_no, $department_name, $edit_id);
        } else {
            $sql = "INSERT INTO departments (company_name, division_name, sub_division_name, department_no, department_name) VALUES (?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $company_name, $division_name, $sub_division_name, $department_no, $department_name);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: department-registration.php?success=1");
        exit;
    }
}

$companyList = []; $r = $conn->query("SELECT company_name FROM companies ORDER BY company_name");
if ($r) while ($row = $r->fetch_assoc()) $companyList[] = $row['company_name'];
$divisionList = []; $r = $conn->query("SELECT DISTINCT division_name FROM divisions ORDER BY division_name");
if ($r) while ($row = $r->fetch_assoc()) $divisionList[] = $row['division_name'];
$subDivList = []; $r = $conn->query("SELECT DISTINCT sub_division_name FROM sub_divisions ORDER BY sub_division_name");
if ($r) while ($row = $r->fetch_assoc()) $subDivList[] = $row['sub_division_name'];

$rows = []; $r = $conn->query("SELECT * FROM departments ORDER BY id ASC");
if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Department Registration | SMART HRIS™</title>
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
.container{max-width:1100px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:20px;color:#1e293b}
.form-card{background:#fff;border:1px solid var(--border);border-radius:10px;padding:24px;margin-bottom:20px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 30px;max-width:700px}
.form-group{display:flex;flex-direction:column;gap:4px}
label{font-size:12.5px;font-weight:500;color:#334155}
input,select{height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px;outline:none;font-family:inherit;width:100%}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(59,130,246,.12)}
.btn-row{display:flex;gap:10px;margin-top:18px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;width:64px;height:54px;border:none;border-radius:8px;cursor:pointer;font-size:11px;font-weight:500;color:#fff;gap:3px}
.btn i{font-size:16px}.btn-save{background:#16a34a}.btn-new{background:#ea580c}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}
.table-wrap{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden;overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 14px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 14px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}tr:hover{background:#eff6ff}
.btn-edit{background:#2563eb;color:#fff;border:none;padding:4px 12px;border-radius:5px;font-size:12px;cursor:pointer}
.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px}
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
<h1 class="page-title">Department Registration</h1>
<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Department saved successfully!</div><?php endif; ?>
<div class="form-card">
<form method="POST">
<input type="hidden" name="action" value="save">
<input type="hidden" name="edit_id" id="edit_id" value="">
<div class="form-grid">
<div class="form-group"><label>Company Name</label>
<select name="company_name" id="company_name"><option value="">-select-</option>
<?php foreach ($companyList as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option><?php endforeach; ?>
</select></div>
<div class="form-group"><label>Department No</label><input type="text" name="department_no" id="department_no"></div>
<div class="form-group"><label>Division Name</label>
<select name="division_name" id="division_name"><option value="">-select-</option>
<?php foreach ($divisionList as $d): ?><option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option><?php endforeach; ?>
</select></div>
<div class="form-group"><label>Department Name</label><input type="text" name="department_name" id="department_name" required></div>
<div class="form-group"><label>Sub Division Name</label>
<select name="sub_division_name" id="sub_division_name"><option value="">-select-</option>
<?php foreach ($subDivList as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option><?php endforeach; ?>
</select></div>
</div>
<div class="btn-row">
<button type="submit" class="btn btn-save"><i class="fas fa-save"></i>Save</button>
<button type="button" class="btn btn-new" onclick="clearForm()"><i class="fas fa-file"></i>New</button>
</div>
</form>
</div>
<div class="table-wrap">
<table>
<thead><tr><th>Department Name</th><th>Department No</th><th>Company Name</th><th>Division Name</th><th>Sub Division</th><th>Actions</th></tr></thead>
<tbody>
<?php if (empty($rows)): ?>
<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:20px">No records yet.</td></tr>
<?php else: foreach ($rows as $r): ?>
<tr>
<td><?= htmlspecialchars($r['department_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['department_no'] ?? '') ?></td>
<td><?= htmlspecialchars($r['company_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['division_name'] ?? '') ?></td>
<td><?= htmlspecialchars($r['sub_division_name'] ?? '') ?></td>
<td><button type="button" class="btn-edit" onclick='editRow(<?= json_encode($r) ?>)'>Edit</button></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</main>
<script>
function clearForm(){['edit_id','company_name','division_name','sub_division_name','department_no','department_name'].forEach(id=>document.getElementById(id).value='')}
function editRow(r){
document.getElementById('edit_id').value=r.id;
document.getElementById('company_name').value=r.company_name||'';
document.getElementById('division_name').value=r.division_name||'';
document.getElementById('sub_division_name').value=r.sub_division_name||'';
document.getElementById('department_no').value=r.department_no||'';
document.getElementById('department_name').value=r.department_name||'';
window.scrollTo({top:0,behavior:'smooth'});
}
</script>
</body>
</html>