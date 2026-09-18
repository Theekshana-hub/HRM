<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $company_name = $_POST['company_name'] ?? '';
    $division_no = $_POST['division_no'] ?? '';
    $division_name = $_POST['division_name'] ?? '';
    $edit_id = $_POST['edit_id'] ?? '';

    if (!empty($division_name)) {
        if (!empty($edit_id)) {
            $sql = "UPDATE divisions SET company_name=?, division_no=?, division_name=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $company_name, $division_no, $division_name, $edit_id);
        } else {
            $sql = "INSERT INTO divisions (company_name, division_no, division_name) VALUES (?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $company_name, $division_no, $division_name);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: division-registration.php?success=1");
        exit;
    }
}

// Companies for dropdown
$companyList = [];
$cr = $conn->query("SELECT company_name FROM companies ORDER BY company_name");
if ($cr) while ($r = $cr->fetch_assoc()) $companyList[] = $r['company_name'];

// Divisions
$divisions = [];
$result = $conn->query("SELECT * FROM divisions ORDER BY id ASC");
if ($result) while ($row = $result->fetch_assoc()) $divisions[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Division Registration | SMART HRIS™</title>
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
.form-grid{display:grid;grid-template-columns:200px 1fr;gap:12px 16px;align-items:center;max-width:500px}
label{font-size:12.5px;font-weight:500;color:#334155}
input,select{height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px;outline:none;font-family:inherit;width:100%}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(59,130,246,.12)}
.btn-row{display:flex;gap:10px;margin-top:18px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;width:64px;height:54px;border:none;border-radius:8px;cursor:pointer;font-size:11px;font-weight:500;color:#fff;gap:3px}
.btn i{font-size:16px}
.btn-save{background:#16a34a}.btn-new{background:#ea580c}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}
.table-wrap{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden;overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 14px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 14px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
tr:hover{background:#eff6ff}
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
<h1 class="page-title">Division Registration</h1>
<?php if (isset($_GET['success'])): ?>
<div class="alert"><i class="fas fa-check-circle"></i> Division saved successfully!</div>
<?php endif; ?>
<div class="form-card">
<form method="POST">
<input type="hidden" name="action" value="save">
<input type="hidden" name="edit_id" id="edit_id" value="">
<div class="form-grid">
<label>Company Name</label>
<select name="company_name" id="company_name">
<option value="">-select-</option>
<?php foreach ($companyList as $cn): ?>
<option value="<?= htmlspecialchars($cn) ?>"><?= htmlspecialchars($cn) ?></option>
<?php endforeach; ?>
</select>
<label>Division No</label><input type="text" name="division_no" id="division_no">
<label>Division Name</label><input type="text" name="division_name" id="division_name" required>
</div>
<div class="btn-row">
<button type="submit" class="btn btn-save"><i class="fas fa-save"></i>Save</button>
<button type="button" class="btn btn-new" onclick="clearForm()"><i class="fas fa-file"></i>New</button>
</div>
</form>
</div>
<div class="table-wrap">
<table>
<thead><tr><th>Division No</th><th>Division Name</th><th>Company Name</th><th>Actions</th></tr></thead>
<tbody>
<?php if (empty($divisions)): ?>
<tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">No divisions yet.</td></tr>
<?php else: ?>
<?php foreach ($divisions as $d): ?>
<tr>
<td><?= htmlspecialchars($d['division_no'] ?? '') ?></td>
<td><?= htmlspecialchars($d['division_name'] ?? '') ?></td>
<td><?= htmlspecialchars($d['company_name'] ?? '') ?></td>
<td><button type="button" class="btn-edit" onclick='editDiv(<?= json_encode($d) ?>)'>Edit</button></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</main>
<script>
function clearForm(){document.getElementById('edit_id').value='';document.getElementById('company_name').value='';document.getElementById('division_no').value='';document.getElementById('division_name').value='';}
function editDiv(d){
document.getElementById('edit_id').value=d.id;
document.getElementById('company_name').value=d.company_name||'';
document.getElementById('division_no').value=d.division_no||'';
document.getElementById('division_name').value=d.division_name||'';
window.scrollTo({top:0,behavior:'smooth'});
}
</script>
</body>
</html>
