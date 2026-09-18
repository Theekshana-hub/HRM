<?php
include '../db_connect.php';

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $company_no = $_POST['company_no'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $phone_no = $_POST['phone_no'] ?? '';
    $address = $_POST['address'] ?? '';
    $registration_year = $_POST['registration_year'] ?? '';
    $epf_no = $_POST['epf_no'] ?? '';
    $tin_no = $_POST['tin_no'] ?? '';
    $edit_id = $_POST['edit_id'] ?? '';

    if (!empty($company_name)) {
        if (!empty($edit_id)) {
            $sql = "UPDATE companies SET company_no=?, company_name=?, phone_no=?, address=?, registration_year=?, epf_no=?, tin_no=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $company_no, $company_name, $phone_no, $address, $registration_year, $epf_no, $tin_no, $edit_id);
        } else {
            $sql = "INSERT INTO companies (company_no, company_name, phone_no, address, registration_year, epf_no, tin_no) VALUES (?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $company_no, $company_name, $phone_no, $address, $registration_year, $epf_no, $tin_no);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: company-registration.php?success=1");
        exit;
    }
}

// Fetch all
$companies = [];
$result = $conn->query("SELECT * FROM companies ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Company Registration | SMART HRIS™</title>
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
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 40px}
.form-group{display:flex;flex-direction:column;gap:4px}
.form-group.full{grid-column:1/-1}
label{font-size:12.5px;font-weight:500;color:#334155}
input,select,textarea{height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px;outline:none;font-family:inherit;width:100%}
input:focus,select:focus,textarea:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(59,130,246,.12)}
textarea{height:70px;padding:8px 10px;resize:vertical}
.btn-row{display:flex;gap:10px;margin-top:18px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;width:64px;height:54px;border:none;border-radius:8px;cursor:pointer;font-size:11px;font-weight:500;color:#fff;gap:3px}
.btn i{font-size:16px}
.btn-save{background:#16a34a}.btn-new{background:#ea580c}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}
.table-wrap{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:#2563eb;color:#fff}
th{padding:11px 14px;text-align:left;font-weight:500;font-size:12.5px}
td{padding:10px 14px;border-bottom:1px solid #f1f5f9}
tr:nth-child(even){background:#f8fafc}
tr:hover{background:#eff6ff}
.btn-edit{background:#2563eb;color:#fff;border:none;padding:4px 12px;border-radius:5px;font-size:12px;cursor:pointer}
.btn-edit:hover{background:#1d4ed8}
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
<h1 class="page-title">Company Registration</h1>

<?php if (isset($_GET['success'])): ?>
<div class="alert"><i class="fas fa-check-circle"></i> Company saved successfully!</div>
<?php endif; ?>

<div class="form-card">
<form method="POST" action="">
<input type="hidden" name="action" value="save">
<input type="hidden" name="edit_id" id="edit_id" value="">
<div class="form-grid">
<div class="form-group"><label>Company No</label><input type="text" name="company_no" id="company_no"></div>
<div class="form-group"><label>Registration Years</label>
<select name="registration_year" id="registration_year">
<option value="">-select-</option>
<option value="2023">2023</option><option value="2024">2024</option>
<option value="2025">2025</option><option value="2026">2026</option>
</select></div>
<div class="form-group"><label>Company Name</label><input type="text" name="company_name" id="company_name" required></div>
<div class="form-group"><label>EPF Registration No</label><input type="text" name="epf_no" id="epf_no"></div>
<div class="form-group"><label>Phone No</label><input type="text" name="phone_no" id="phone_no"></div>
<div class="form-group"><label>Tin No</label><input type="text" name="tin_no" id="tin_no"></div>
<div class="form-group full"><label>Address</label><textarea name="address" id="address"></textarea></div>
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
<th>Company No</th><th>Company Name</th><th>Company Tel</th><th>Company Address</th>
<th>Company Year</th><th>EPF No</th><th>Tin No</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($companies)): ?>
<tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:20px">No companies yet. Add one above.</td></tr>
<?php else: ?>
<?php foreach ($companies as $c): ?>
<tr>
<td><?= htmlspecialchars($c['company_no'] ?? '') ?></td>
<td><?= htmlspecialchars($c['company_name'] ?? '') ?></td>
<td><?= htmlspecialchars($c['phone_no'] ?? '') ?></td>
<td><?= htmlspecialchars($c['address'] ?? '') ?></td>
<td><?= htmlspecialchars($c['registration_year'] ?? '') ?></td>
<td><?= htmlspecialchars($c['epf_no'] ?? '') ?></td>
<td><?= htmlspecialchars($c['tin_no'] ?? '') ?></td>
<td>
<button type="button" class="btn-edit" onclick='editCompany(<?= json_encode($c) ?>)'>Edit</button>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</main>

<script>
function clearForm() {
    document.getElementById('edit_id').value = '';
    document.getElementById('company_no').value = '';
    document.getElementById('company_name').value = '';
    document.getElementById('phone_no').value = '';
    document.getElementById('address').value = '';
    document.getElementById('registration_year').value = '';
    document.getElementById('epf_no').value = '';
    document.getElementById('tin_no').value = '';
    document.getElementById('company_no').focus();
}
function editCompany(c) {
    document.getElementById('edit_id').value = c.id;
    document.getElementById('company_no').value = c.company_no || '';
    document.getElementById('company_name').value = c.company_name || '';
    document.getElementById('phone_no').value = c.phone_no || '';
    document.getElementById('address').value = c.address || '';
    document.getElementById('registration_year').value = c.registration_year || '';
    document.getElementById('epf_no').value = c.epf_no || '';
    document.getElementById('tin_no').value = c.tin_no || '';
    window.scrollTo({top:0, behavior:'smooth'});
}
</script>
</body>
</html>
