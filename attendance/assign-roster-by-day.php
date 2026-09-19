<?php
include '../db_connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS roster_by_day (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assign_type VARCHAR(20) DEFAULT 'employee',
    day_name VARCHAR(20) NOT NULL,
    shift1 VARCHAR(150) DEFAULT NULL,
    shift2 VARCHAR(150) DEFAULT NULL,
    shift3 VARCHAR(150) DEFAULT NULL,
    shift4 VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS roster_presets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    preset_name VARCHAR(150) NOT NULL,
    assign_type VARCHAR(20) DEFAULT 'employee',
    config_json TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

// Load current config
$current = [];
foreach ($days as $d) {
    $current[$d] = ['shift1'=>'','shift2'=>'','shift3'=>'','shift4'=>''];
}
$r = $conn->query("SELECT * FROM roster_by_day ORDER BY FIELD(day_name,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $current[$row['day_name']] = [
            'shift1' => $row['shift1'] ?? '',
            'shift2' => $row['shift2'] ?? '',
            'shift3' => $row['shift3'] ?? '',
            'shift4' => $row['shift4'] ?? '',
            'id' => $row['id']
        ];
    }
}

// Save day config
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $assign_type = $_POST['assign_type'] ?? 'employee';
    foreach ($days as $d) {
        $s1 = $_POST['shift'][$d][0] ?? '';
        $s2 = $_POST['shift'][$d][1] ?? '';
        $s3 = $_POST['shift'][$d][2] ?? '';
        $s4 = $_POST['shift'][$d][3] ?? '';
        // upsert
        $chk = $conn->prepare("SELECT id FROM roster_by_day WHERE day_name=?");
        $chk->bind_param("s", $d);
        $chk->execute();
        $ex = $chk->get_result()->fetch_assoc();
        $chk->close();
        if ($ex) {
            $u = $conn->prepare("UPDATE roster_by_day SET assign_type=?, shift1=?, shift2=?, shift3=?, shift4=? WHERE id=?");
            $u->bind_param("sssssi", $assign_type, $s1, $s2, $s3, $s4, $ex['id']);
            $u->execute();
            $u->close();
        } else {
            $i = $conn->prepare("INSERT INTO roster_by_day (assign_type, day_name, shift1, shift2, shift3, shift4) VALUES (?,?,?,?,?,?)");
            $i->bind_param("ssssss", $assign_type, $d, $s1, $s2, $s3, $s4);
            $i->execute();
            $i->close();
        }
    }
    header("Location: assign-roster-by-day.php?success=1");
    exit;
}

// Save preset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_preset') {
    $preset_name = trim($_POST['preset_name'] ?? '');
    $assign_type = $_POST['assign_type'] ?? 'employee';
    if ($preset_name !== '') {
        $config = [];
        foreach ($days as $d) {
            $config[$d] = [
                $_POST['shift'][$d][0] ?? '',
                $_POST['shift'][$d][1] ?? '',
                $_POST['shift'][$d][2] ?? '',
                $_POST['shift'][$d][3] ?? ''
            ];
        }
        $json = json_encode($config);
        $stmt = $conn->prepare("INSERT INTO roster_presets (preset_name, assign_type, config_json) VALUES (?,?,?)");
        $stmt->bind_param("sss", $preset_name, $assign_type, $json);
        $stmt->execute();
        $stmt->close();
        header("Location: assign-roster-by-day.php?preset=1");
        exit;
    }
}

$shifts = [];
$sr = @$conn->query("SELECT DISTINCT shift_name FROM shifts ORDER BY shift_name");
if ($sr) while ($s = $sr->fetch_assoc()) $shifts[] = $s['shift_name'];
if (empty($shifts)) {
    $shifts = ['General Shift','Evening Shift','Work from home','Morning Half Day Shift','Off day','OT shift General'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign To Roster By Day | SMART HRIS™</title>
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
.container{max-width:900px;margin:0 auto;padding:22px 20px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:18px;color:#1e293b}

.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:24px 28px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.radio-row{display:flex;align-items:center;gap:18px;margin-bottom:18px}
.radio-row label{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:500;cursor:pointer}

.day-row{display:grid;grid-template-columns:100px 1fr 1fr 1fr 1fr;gap:10px;align-items:center;margin-bottom:10px}
.day-row .day-label{font-size:13px;font-weight:500;color:#334155}
select{
    height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 8px;
    font-size:12.5px;outline:none;font-family:inherit;width:100%;background:#fff
}
select:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(59,130,246,.12)}

.btn-row{display:flex;gap:10px;margin-top:18px}
.btn{display:flex;flex-direction:column;align-items:center;justify-content:center;width:64px;height:54px;border:none;border-radius:8px;cursor:pointer;font-size:11px;font-weight:600;color:#fff;gap:3px;font-family:inherit}
.btn-save{background:var(--green)}.btn-new{background:var(--orange)}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}

.preset-section{margin-top:8px;padding-top:8px}
.preset-section label{font-size:13px;font-weight:500;color:#334155;display:block;margin-bottom:8px}
.preset-section label .req{color:#ef4444}
.preset-section input[type="text"]{
    height:36px;border:1px solid #cbd5e1;border-radius:7px;padding:0 12px;
    font-size:13px;outline:none;font-family:inherit;width:100%;max-width:360px;background:#fff;margin-bottom:14px
}
.preset-section input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.btn-preset{
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
    width:72px;height:64px;background:var(--green);color:#fff;border:none;border-radius:10px;
    font-size:11px;font-weight:600;cursor:pointer;font-family:inherit
}
.btn-preset i{font-size:18px}
.btn-preset:hover{background:#15803d}

.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}

@media(max-width:700px){
    .day-row{grid-template-columns:1fr 1fr;gap:8px}
    .day-row .day-label{grid-column:1/-1}
    .search-nav,.welcome{display:none}
}
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
<h1 class="page-title">Assign To Roster By Day</h1>

<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Roster by day saved!</div><?php endif; ?>
<?php if (isset($_GET['preset'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Preset saved successfully!</div><?php endif; ?>

<div class="form-card">
<form method="POST" id="rosterForm">
<input type="hidden" name="action" value="save">

<div class="radio-row">
    <label><input type="radio" name="assign_type" value="employee" checked> By Employee</label>
    <label><input type="radio" name="assign_type" value="group"> By Group</label>
</div>

<?php foreach ($days as $d): ?>
<div class="day-row">
    <div class="day-label"><?= $d ?></div>
    <?php for ($i = 0; $i < 4; $i++):
        $key = 'shift' . ($i + 1);
        $val = $current[$d][$key] ?? '';
    ?>
    <select name="shift[<?= $d ?>][]">
        <option value="">-select-</option>
        <?php foreach ($shifts as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>" <?= $val === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
        <?php endforeach; ?>
    </select>
    <?php endfor; ?>
</div>
<?php endforeach; ?>

<div class="btn-row">
    <button type="submit" class="btn btn-save"><i class="fas fa-save"></i>Save</button>
    <button type="button" class="btn btn-new" onclick="clearForm()"><i class="fas fa-file"></i>New</button>
</div>
</form>
</div>

<div class="form-card">
<form method="POST">
<input type="hidden" name="action" value="save_preset">
<input type="hidden" name="assign_type" id="preset_assign_type" value="employee">
<?php foreach ($days as $d): ?>
    <?php for ($i = 0; $i < 4; $i++): ?>
    <input type="hidden" name="shift[<?= $d ?>][]" id="preset_<?= $d ?>_<?= $i ?>" value="">
    <?php endfor; ?>
<?php endforeach; ?>

<div class="preset-section">
    <label>Preset Name <span class="req">*</span></label>
    <input type="text" name="preset_name" id="preset_name" required placeholder="e.g. Standard Week">
    <button type="submit" class="btn-preset" onclick="copyShiftsToPreset()"><i class="fas fa-save"></i>Save Preset</button>
</div>
</form>
</div>
</main>

<script>
function clearForm() {
    document.querySelectorAll('#rosterForm select').forEach(s => s.selectedIndex = 0);
}
function copyShiftsToPreset() {
    const type = document.querySelector('#rosterForm input[name="assign_type"]:checked');
    if (type) document.getElementById('preset_assign_type').value = type.value;
    const days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    days.forEach(d => {
        const selects = document.querySelectorAll('#rosterForm select[name="shift['+d+'][]"]');
        selects.forEach((sel, i) => {
            const hidden = document.getElementById('preset_'+d+'_'+i);
            if (hidden) hidden.value = sel.value;
        });
    });
}
</script>
</body>
</html>