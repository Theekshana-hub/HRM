<?php
include '../db_connect.php';

// Delete calendar entry
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM calendar_holidays WHERE id = $id");
    $year = (int)($_GET['year'] ?? date('Y'));
    header("Location: define-calendar.php?year=$year&deleted=1");
    exit;
}

// Add holiday to calendar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $holiday_id   = $_POST['holiday_id'] ?? '';
    $holiday_date = $_POST['holiday_date'] ?? '';
    $color        = $_POST['color'] ?? '#facc15';
    $year         = (int)($_POST['year'] ?? date('Y'));
    $holiday_name = '';

    $hr = $conn->query("SELECT holiday_name FROM holidays WHERE id = " . (int)$holiday_id);
    if ($hr && $row = $hr->fetch_assoc()) {
        $holiday_name = $row['holiday_name'];
    }
    if ($holiday_name === '' && $holiday_id !== '') {
        $holiday_name = $_POST['holiday_label'] ?? 'Holiday';
    }

    if ($holiday_date !== '') {
        $sql = "INSERT INTO calendar_holidays (holiday_id, holiday_name, holiday_date, color, year) VALUES (?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $holiday_id, $holiday_name, $holiday_date, $color, $year);
        $stmt->execute();
        $stmt->close();
        header("Location: define-calendar.php?year=$year&success=1");
        exit;
    }
}

$year = (int)($_GET['year'] ?? $_POST['year'] ?? date('Y'));

// Load holidays dropdown
$holidays = [];
$hr = $conn->query("SELECT id, holiday_name, holiday_color FROM holidays ORDER BY holiday_name ASC");
if ($hr) while ($h = $hr->fetch_assoc()) $holidays[] = $h;

// Load calendar entries for year
$cal = []; // date => [id, name, color]
$tableCheck = $conn->query("SHOW TABLES LIKE 'calendar_holidays'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $cr = $conn->query("SELECT * FROM calendar_holidays WHERE year = $year ORDER BY holiday_date ASC");
    if ($cr) {
        while ($c = $cr->fetch_assoc()) {
            $d = $c['holiday_date'];
            $cal[$d] = $c;
        }
    }
}

$monthNames = ['','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER'];
$dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

function buildMonth($year, $month, $cal) {
    $first = mktime(0,0,0,$month,1,$year);
    $daysInMonth = (int)date('t', $first);
    // Monday=0 ... Sunday=6
    $startDow = (int)date('N', $first) - 1;
    $cells = [];
    for ($i = 0; $i < $startDow; $i++) $cells[] = null;
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $cells[] = [
            'day' => $d,
            'date' => $dateStr,
            'holiday' => $cal[$dateStr] ?? null
        ];
    }
    return $cells;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Define Calendar | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--nav-bg:#1a2332;--page-bg:#f1f5f9;--accent:#3b82f6;--border:#e2e8f0}
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
.container{max-width:1100px;margin:0 auto;padding:20px 18px 50px}
.page-title{font-size:18px;font-weight:600;margin-bottom:18px;color:#1e293b}

.toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:14px 20px;margin-bottom:22px;background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px 20px}
.toolbar label{font-size:12.5px;font-weight:500;color:#475569;margin-right:6px}
.toolbar select,.toolbar input[type="date"]{height:34px;border:1px solid #cbd5e1;border-radius:7px;padding:0 10px;font-size:13px;outline:none;font-family:inherit}
.toolbar input[type="color"]{width:40px;height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:2px;cursor:pointer}
.btn-add{height:34px;padding:0 20px;background:#2563eb;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;margin-left:auto}
.btn-add:hover{background:#1d4ed8}

.months{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.month-card{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.month-header{background:#2563eb;color:#fff;text-align:center;padding:10px;font-weight:600;font-size:13px;letter-spacing:.3px}
.month-body{padding:8px}
.dow{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:4px}
.dow span{text-align:center;font-size:11px;font-weight:500;color:#94a3b8;padding:4px 0}
.days{display:grid;grid-template-columns:repeat(7,1fr);gap:3px}
.day{
    aspect-ratio:1;display:flex;align-items:center;justify-content:center;
    font-size:12px;border:1px solid #e2e8f0;border-radius:5px;background:#fff;
    color:#334155;min-height:28px;position:relative
}
.day.empty{border:none;background:transparent}
.day.holiday{font-weight:600;color:#0f172a}
.day.today{outline:2px solid #f59e0b;outline-offset:-1px}
.legend{display:flex;flex-wrap:wrap;gap:6px;padding:8px 10px;border-top:1px solid #f1f5f9;min-height:36px;align-items:center}
.legend-item{display:inline-flex;align-items:center;gap:5px;background:#f1f5f9;border-radius:20px;padding:3px 8px;font-size:11px;color:#334155}
.legend-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.legend-item a{color:#94a3b8;margin-left:2px;text-decoration:none;font-size:10px}
.legend-item a:hover{color:#dc2626}
.today-tag{display:inline-flex;align-items:center;gap:4px;background:#1e293b;color:#fff;border-radius:4px;padding:2px 6px;font-size:10px;margin-right:4px}
.today-tag i{color:#f59e0b;font-size:8px}

.alert{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
.alert.red{background:#fee2e2;color:#991b1b}

@media(max-width:900px){.months{grid-template-columns:1fr 1fr}.search-nav,.welcome{display:none}}
@media(max-width:600px){.months{grid-template-columns:1fr}.toolbar{flex-direction:column;align-items:stretch}.btn-add{margin-left:0}}
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
<h1 class="page-title">Define Calendar</h1>

<?php if (isset($_GET['success'])): ?><div class="alert"><i class="fas fa-check-circle"></i> Holiday added to calendar!</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert red"><i class="fas fa-trash"></i> Holiday removed from calendar.</div><?php endif; ?>

<form method="POST" class="toolbar">
<input type="hidden" name="action" value="add">
<input type="hidden" name="year" value="<?= $year ?>">
<input type="hidden" name="holiday_label" id="holiday_label" value="">

<div>
    <label>Holiday</label>
    <select name="holiday_id" id="holiday_id" required onchange="onHolidayChange()">
        <option value="">-select-</option>
        <?php foreach ($holidays as $h): ?>
        <option value="<?= (int)$h['id'] ?>" data-color="<?= htmlspecialchars($h['holiday_color'] ?? '#facc15') ?>" data-name="<?= htmlspecialchars($h['holiday_name']) ?>">
            <?= htmlspecialchars($h['holiday_name']) ?>
        </option>
        <?php endforeach; ?>
        <?php if (empty($holidays)): ?>
        <option value="1" data-color="#facc15" data-name="Poya Day">Poya Day</option>
        <option value="2" data-color="#ef4444" data-name="National Day">National Day</option>
        <option value="3" data-color="#ef4444" data-name="Christmas Day">Christmas Day</option>
        <?php endif; ?>
    </select>
</div>
<div>
    <label>Date</label>
    <input type="date" name="holiday_date" id="holiday_date" required>
</div>
<div>
    <label>Color</label>
    <input type="color" name="color" id="color" value="#facc15">
</div>
<div>
    <label>Year</label>
    <select name="year_select" id="year_select" onchange="location='?year='+this.value">
        <?php for ($y = $year - 2; $y <= $year + 3; $y++): ?>
        <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
    </select>
</div>
<button type="submit" class="btn-add">Add</button>
</form>

<div class="months">
<?php for ($m = 1; $m <= 12; $m++):
    $cells = buildMonth($year, $m, $cal);
    $monthHolidays = [];
    foreach ($cal as $d => $info) {
        if ((int)substr($d, 5, 2) === $m) $monthHolidays[] = $info;
    }
    $todayStr = date('Y-m-d');
?>
<div class="month-card">
    <div class="month-header"><?= $year ?> <?= $monthNames[$m] ?></div>
    <div class="month-body">
        <div class="dow">
            <?php foreach ($dayNames as $dn): ?><span><?= $dn ?></span><?php endforeach; ?>
        </div>
        <div class="days">
            <?php foreach ($cells as $cell):
                if ($cell === null): ?>
                <div class="day empty"></div>
                <?php else:
                    $isHoliday = $cell['holiday'] !== null;
                    $isToday = $cell['date'] === $todayStr;
                    $bg = $isHoliday ? htmlspecialchars($cell['holiday']['color'] ?? '#facc15') : '';
                    $cls = 'day' . ($isHoliday ? ' holiday' : '') . ($isToday ? ' today' : '');
                ?>
                <div class="<?= $cls ?>" style="<?= $bg ? 'background:'.$bg : '' ?>" title="<?= $isHoliday ? htmlspecialchars($cell['holiday']['holiday_name']) : '' ?>">
                    <?= $cell['day'] ?>
                </div>
                <?php endif;
            endforeach; ?>
        </div>
    </div>
    <div class="legend">
        <?php if ($m === (int)date('n') && $year === (int)date('Y')): ?>
        <span class="today-tag"><i class="fas fa-square"></i> Today</span>
        <?php else: ?>
        <span class="today-tag" style="opacity:.5"><i class="fas fa-square"></i> Today</span>
        <?php endif; ?>
        <?php foreach ($monthHolidays as $mh): ?>
        <span class="legend-item">
            <span class="legend-dot" style="background:<?= htmlspecialchars($mh['color'] ?? '#facc15') ?>"></span>
            <?= (int)substr($mh['holiday_date'], 8, 2) ?> - <?= htmlspecialchars($mh['holiday_name']) ?>
            <a href="?delete=<?= (int)$mh['id'] ?>&year=<?= $year ?>" onclick="return confirm('Remove this holiday?')" title="Remove"><i class="fas fa-trash"></i></a>
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endfor; ?>
</div>
</main>

<script>
function onHolidayChange() {
    const sel = document.getElementById('holiday_id');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.color) {
        document.getElementById('color').value = opt.dataset.color;
    }
    if (opt && opt.dataset.name) {
        document.getElementById('holiday_label').value = opt.dataset.name;
    }
}
</script>
</body>
</html>