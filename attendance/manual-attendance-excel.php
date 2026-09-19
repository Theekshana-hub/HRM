<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS manual_attendance_upload (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) DEFAULT NULL,
    session_name VARCHAR(100) DEFAULT NULL,
    emp_no VARCHAR(50) DEFAULT NULL,
    att_date DATE DEFAULT NULL,
    att_time TIME DEFAULT NULL,
    status VARCHAR(30) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$tab = $_GET['tab'] ?? 'upload';
$message = '';
$messageType = '';

// Sample Excel download (CSV)
if (isset($_GET['sample'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="manual_attendance_sample.csv"');
    echo "Employee,Session,Date,Time,Status\n";
    echo "60591,Jan 2018,2018/01/01,00:00:00,CheckIn\n";
    echo "60591,Jan 2018,2018/01/01,00:00:00,CheckOut\n";
    echo "60591,Jan 2018,2018/01/02,08:15:00,CheckIn\n";
    echo "60591,Jan 2018,2018/01/02,17:00:00,CheckOut\n";
    exit;
}

// Download template
if (isset($_GET['download'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="manual_attendance_template.csv"');
    echo "Employee,Session,Date,Time,Status\n";
    echo ",,,,\n";
    exit;
}

// Upload Excel/CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    $company = trim($_POST['company_name'] ?? '');
    $session = trim($_POST['session_name'] ?? '');
    if (!empty($_FILES['excel_file']['tmp_name']) && $company !== '') {
        $handle = fopen($_FILES['excel_file']['tmp_name'], 'r');
        $header = fgetcsv($handle); // skip header
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;
            $emp = trim($row[0] ?? '');
            $sess = trim($row[1] ?? $session);
            $date = str_replace('/', '-', trim($row[2] ?? ''));
            $time = trim($row[3] ?? '');
            $status = trim($row[4] ?? '');
            if ($emp === '') continue;
            // normalize date
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                // ok
            } elseif (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $date, $m)) {
                $date = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
            }
            $stmt = $conn->prepare("INSERT INTO manual_attendance_upload (company_name, session_name, emp_no, att_date, att_time, status) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssssss", $company, $sess, $emp, $date, $time, $status);
            $stmt->execute();
            $stmt->close();
            $count++;
        }
        fclose($handle);
        $message = "$count records uploaded successfully!";
        $messageType = 'success';
    } else {
        $message = 'Please select company and choose a file.';
        $messageType = 'error';
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $company = trim($_POST['del_company'] ?? '');
    $session = trim($_POST['del_session'] ?? '');
    $from = $_POST['del_from'] ?? '';
    $to = $_POST['del_to'] ?? '';
    $sql = "DELETE FROM manual_attendance_upload WHERE 1=1";
    $params = [];
    $types = '';
    if ($company !== '') { $sql .= " AND company_name = ?"; $params[] = $company; $types .= 's'; }
    if ($session !== '') { $sql .= " AND session_name = ?"; $params[] = $session; $types .= 's'; }
    if ($from !== '') { $sql .= " AND att_date >= ?"; $params[] = $from; $types .= 's'; }
    if ($to !== '') { $sql .= " AND att_date <= ?"; $params[] = $to; $types .= 's'; }
    if ($params) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();
        $message = "$deleted records deleted.";
        $messageType = 'success';
        $tab = 'delete';
    }
}

$companies = [];
$cr = @$conn->query("SELECT company_name FROM companies ORDER BY company_name");
if ($cr) while ($c = $cr->fetch_assoc()) $companies[] = $c['company_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manual Attendance Excel Upload | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0;--cyan:#0ea5e9}
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
.tab{padding:10px 18px;font-size:13px;font-weight:500;color:#64748b;text-decoration:none;border:1px solid transparent;border-bottom:none;border-radius:8px 8px 0 0;background:transparent}
.tab:hover{color:var(--accent)}
.tab.active{color:#0f172a;background:#fff;border-color:var(--border);margin-bottom:-2px;font-weight:600}

.panel{background:#fff;border:1px solid var(--border);border-top:none;border-radius:0 0 12px 12px;padding:24px 28px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.panel-title{font-size:16px;font-weight:600;margin-bottom:18px;color:#1e293b;padding-bottom:10px;border-bottom:1px solid #f1f5f9}

.form-row{display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap}
label.fld{font-size:13px;font-weight:500;color:#334155;min-width:90px}
label .req{color:#ef4444}
select,input[type="date"],input[type="file"]{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;background:#fff;min-width:180px
}
select:focus{border-color:var(--accent)}

.btn-box{
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
    width:90px;height:72px;border:none;border-radius:10px;cursor:pointer;font-size:11px;font-weight:600;
    color:#fff;font-family:inherit;text-decoration:none;transition:all .2s
}
.btn-box i{font-size:22px}
.btn-cyan{background:var(--cyan)}.btn-cyan:hover{background:#0284c7;transform:translateY(-2px)}
.btn-gray{background:#94a3b8}.btn-gray:hover{background:#64748b;transform:translateY(-2px)}
.btn-actions{display:flex;gap:12px;margin-left:12px;align-items:center}

.example{margin-top:20px}
.example label{font-size:13px;font-weight:500;color:#334155;display:block;margin-bottom:10px}
.example label .req{color:#ef4444}
.excel-preview{border:1px solid #cbd5e1;border-radius:8px;overflow:hidden;max-width:520px;font-size:12px}
.excel-preview table{width:100%;border-collapse:collapse}
.excel-preview th{background:#374151;color:#fff;padding:8px 10px;text-align:left;font-weight:500}
.excel-preview td{padding:7px 10px;border-bottom:1px solid #e5e7eb}
.excel-preview tr:nth-child(even){background:#f9fafb}
.excel-bar{background:#f3f4f6;padding:6px 10px;font-size:11px;color:#6b7280;border-bottom:1px solid #e5e7eb}

.alert{padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
.alert.success{background:#dcfce7;color:#166534}
.alert.error{background:#fee2e2;color:#991b1b}
.btn-del{height:40px;padding:0 22px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;font-family:inherit}
.btn-del:hover{background:#b91c1c}

@media(max-width:700px){.search-nav,.welcome{display:none}.form-row{flex-direction:column;align-items:flex-start}}
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
    <a href="?tab=upload" class="tab <?= $tab === 'upload' ? 'active' : '' ?>">Manual Attendance Excel Upload</a>
    <a href="?tab=delete" class="tab <?= $tab === 'delete' ? 'active' : '' ?>">Manual Attendance Delete</a>
</div>

<div class="panel">
<?php if ($message): ?>
<div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($tab === 'upload'): ?>
<h2 class="panel-title">Manual Attendance Excel Upload</h2>

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="upload">

<div class="form-row">
    <label class="fld">Company <span class="req">*</span></label>
    <select name="company_name" required>
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
        <?php if (empty($companies)): ?>
        <option value="Sipway Campus Pvt Ltd">Sipway Campus Pvt Ltd</option>
        <option value="Sipway Higher Education Pvt Ltd">Sipway Higher Education Pvt Ltd</option>
        <?php endif; ?>
    </select>
</div>

<div class="form-row">
    <label class="fld">Session <span class="req">*</span></label>
    <select name="session_name" required>
        <option value="">-select-</option>
        <option value="Jan 2026">Jan 2026</option>
        <option value="Feb 2026">Feb 2026</option>
        <option value="Mar 2026">Mar 2026</option>
        <option value="Apr 2026">Apr 2026</option>
        <option value="May 2026">May 2026</option>
        <option value="Jun 2026">Jun 2026</option>
        <option value="Jul 2026">Jul 2026</option>
        <option value="Aug 2026">Aug 2026</option>
        <option value="Sep 2026">Sep 2026</option>
        <option value="Oct 2026">Oct 2026</option>
        <option value="Nov 2026">Nov 2026</option>
        <option value="Dec 2026">Dec 2026</option>
    </select>

    <div class="btn-actions">
        <a href="?download=1" class="btn-box btn-cyan"><i class="fas fa-download"></i>Download Excel</a>
        <label class="btn-box btn-cyan" style="cursor:pointer">
            <i class="fas fa-upload"></i>Upload Excel
            <input type="file" name="excel_file" accept=".csv,.xls,.xlsx" style="display:none" onchange="this.form.submit()">
        </label>
        <a href="?sample=1" class="btn-box btn-gray"><i class="fas fa-file-download"></i>Sample Excel Download</a>
    </div>
</div>
</form>

<div class="example">
    <label>Example Format: <span class="req">*</span></label>
    <div class="excel-preview">
        <div class="excel-bar">G13 &nbsp; fx</div>
        <table>
            <thead>
                <tr>
                    <th style="width:30px;background:#6b7280"></th>
                    <th>A</th><th>B</th><th>C</th><th>D</th><th>E</th>
                </tr>
            </thead>
            <tbody>
                <tr><td style="background:#f3f4f6;color:#6b7280">1</td><td><strong>Employee</strong></td><td><strong>Session</strong></td><td><strong>Date</strong></td><td><strong>Time</strong></td><td><strong>Status</strong></td></tr>
                <tr><td style="background:#f3f4f6;color:#6b7280">2</td><td>60591</td><td>Jan 2018</td><td>2018/01/01</td><td>00:00:00</td><td>CheckIn</td></tr>
                <tr><td style="background:#f3f4f6;color:#6b7280">3</td><td>60591</td><td>Jan 2018</td><td>2018/01/01</td><td>00:00:00</td><td>CheckOut</td></tr>
                <tr><td style="background:#f3f4f6;color:#6b7280">4</td><td>60591</td><td>Jan 2018</td><td>2018/01/02</td><td>08:15:00</td><td>CheckIn</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php else: /* delete tab */ ?>
<h2 class="panel-title">Manual Attendance Delete</h2>
<form method="POST">
<input type="hidden" name="action" value="delete">
<div class="form-row">
    <label class="fld">Company</label>
    <select name="del_company">
        <option value="">-select-</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
        <?php if (empty($companies)): ?>
        <option value="Sipway Campus Pvt Ltd">Sipway Campus Pvt Ltd</option>
        <?php endif; ?>
    </select>
</div>
<div class="form-row">
    <label class="fld">Session</label>
    <select name="del_session">
        <option value="">-select-</option>
        <option value="Jan 2026">Jan 2026</option>
        <option value="Feb 2026">Feb 2026</option>
        <option value="Mar 2026">Mar 2026</option>
    </select>
</div>
<div class="form-row">
    <label class="fld">From Date</label>
    <input type="date" name="del_from">
    <label class="fld" style="min-width:auto">To Date</label>
    <input type="date" name="del_to">
</div>
<button type="submit" class="btn-del" onclick="return confirm('Delete matching records?')"><i class="fas fa-trash"></i> Delete</button>
</form>
<?php endif; ?>
</div>
</main>
</body>
</html>