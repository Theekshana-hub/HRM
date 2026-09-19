 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management | SMART HRIS™</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --nav-bg: #111827;
            --page-bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent: #3b82f6;
            --border: #e2e8f0;
            --radius: 14px;
            --shadow: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-hover: 0 8px 20px rgba(59,130,246,0.12);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--page-bg);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.5;
        }
        .navbar {
            background: var(--nav-bg);
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 22px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .nav-left { display: flex; align-items: center; gap: 18px; }
        .brand {
            display: flex; align-items: center; gap: 10px;
            color: #fff; font-weight: 600; font-size: 15px; text-decoration: none;
        }
        .brand-icon {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            border-radius: 7px; display: grid; place-items: center;
            font-size: 12px; color: white;
        }
        .nav-btn {
            width: 34px; height: 34px; border: none; background: transparent;
            color: #94a3b8; border-radius: 8px; cursor: pointer;
            display: grid; place-items: center; font-size: 14px; text-decoration: none;
        }
        .nav-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .support-btn {
            display: flex; align-items: center; gap: 5px;
            background: transparent; border: none; color: #94a3b8;
            font-size: 13px; font-weight: 500; padding: 6px 10px;
            border-radius: 7px; cursor: pointer;
        }
        .support-btn:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .search-wrap {
            flex: 1; max-width: 360px; margin: 0 24px; position: relative;
        }
        .search-wrap input {
            width: 100%; height: 34px; background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
            padding: 0 12px 0 36px; color: #e2e8f0; font-size: 13px; outline: none;
        }
        .search-wrap input::placeholder { color: #64748b; }
        .search-wrap input:focus { background: rgba(255,255,255,0.11); border-color: #3b82f6; }
        .search-wrap i {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: #64748b; font-size: 12px;
        }
        .nav-right { display: flex; align-items: center; gap: 12px; margin-left: auto; }
        .welcome { color: #94a3b8; font-size: 13px; }
        .welcome strong { color: #f1f5f9; font-weight: 500; }
        .avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: grid; place-items: center; color: white;
            font-weight: 600; font-size: 12px; cursor: pointer;
        }
        .container { max-width: 1100px; margin: 0 auto; padding: 32px 24px 60px; }
        .page-header {
            display: flex; align-items: center; gap: 14px; margin-bottom: 28px;
        }
        .back-btn {
            width: 38px; height: 38px; border-radius: 10px;
            border: 1px solid var(--border); background: white;
            color: var(--text-muted); display: grid; place-items: center;
            cursor: pointer; transition: all 0.2s; text-decoration: none; font-size: 14px;
        }
        .back-btn:hover { background: var(--accent); border-color: var(--accent); color: white; }
        .page-title { font-size: 22px; font-weight: 600; color: var(--text-main); letter-spacing: -0.3px; }

        .section { margin-bottom: 26px; animation: fadeUp 0.45s ease backwards; }
        .section-header {
            display: inline-flex; align-items: center;
            background: linear-gradient(135deg, #34d399, #10b981);
            color: white; font-size: 13.5px; font-weight: 600;
            padding: 8px 18px; border-radius: 50px; margin-bottom: 14px;
            box-shadow: 0 3px 10px rgba(16,185,129,0.25); letter-spacing: 0.2px;
        }
        .section-header.solo {
            display: block;
            margin-bottom: 10px;
        }
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
        }
        .item-card {
            background: var(--card-bg); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 16px 18px;
            display: flex; align-items: center; gap: 14px;
            cursor: pointer; transition: all 0.28s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: var(--shadow); text-decoration: none; color: inherit;
        }
        .item-card:hover {
            transform: translateY(-4px); box-shadow: var(--shadow-hover);
            border-color: #a7f3d0;
        }
        .item-icon {
            width: 46px; height: 46px; border-radius: 50%;
            display: grid; place-items: center; flex-shrink: 0;
            font-size: 18px; color: white;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        .item-icon.teal   { background: linear-gradient(135deg, #0d9488, #2dd4bf); }
        .item-icon.blue   { background: linear-gradient(135deg, #2563eb, #60a5fa); }
        .item-icon.indigo { background: linear-gradient(135deg, #4f46e5, #818cf8); }
        .item-icon.orange { background: linear-gradient(135deg, #ea580c, #fb923c); }
        .item-icon.purple { background: linear-gradient(135deg, #7c3aed, #a78bfa); }
        .item-icon.rose   { background: linear-gradient(135deg, #e11d48, #fb7185); }
        .item-icon.amber  { background: linear-gradient(135deg, #d97706, #fbbf24); }
        .item-icon.cyan   { background: linear-gradient(135deg, #0891b2, #22d3ee); }
        .item-icon.green  { background: linear-gradient(135deg, #16a34a, #4ade80); }
        .item-icon.slate  { background: linear-gradient(135deg, #475569, #94a3b8); }
        .item-icon.pink   { background: linear-gradient(135deg, #db2777, #f472b6); }
        .item-icon.sky    { background: linear-gradient(135deg, #0284c7, #38bdf8); }

        .item-text {
            font-size: 14px; font-weight: 500; color: var(--text-main); line-height: 1.35;
        }
        .item-text small {
            display: block; font-size: 12px; font-weight: 400;
            color: var(--text-muted); margin-top: 2px;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 700px) {
            .search-wrap, .welcome { display: none; }
            .items-grid { grid-template-columns: 1fr; }
            .container { padding: 24px 16px 50px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="../index.php" class="brand">
            <div class="brand-icon"><i class="fas fa-th-large"></i></div>
            <span>SMART HRIS <sup style="font-size:9px;opacity:0.65;">™</sup></span>
        </a>
        <a href="../index.php" class="nav-btn" title="Back"><i class="fas fa-arrow-left"></i></a>
        <a href="../index.php" class="nav-btn" title="Home"><i class="fas fa-home"></i></a>
        <button class="support-btn">Support <i class="fas fa-chevron-down" style="font-size:10px;"></i></button>
    </div>
    <div class="search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search...">
    </div>
    <div class="nav-right">
        <span class="welcome">Welcome <strong>Shakila</strong></span>
        <button class="nav-btn"><i class="fas fa-cog"></i></button>
        <div class="avatar">S</div>
    </div>
</nav>

<main class="container">
    <div class="page-header">
        <a href="../index.php" class="back-btn" title="Back to HR Panel">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="page-title">Attendance Management</h1>
    </div>

    
    <div class="section">
        <div class="section-header"><i class="fas fa-chart-bar" style="margin-right:8px;font-size:12px;"></i>Attendance Reports</div>
        <div class="items-grid">
            <a href="#" class="item-card">
                <div class="item-icon teal"><i class="fas fa-database"></i></div>
                <div class="item-text">UnAnalyzed Attendance Data<small>Raw attendance records</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon blue"><i class="fas fa-desktop"></i></div>
                <div class="item-text">Attendance Display<small>View attendance data</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon indigo"><i class="fas fa-clipboard-list"></i></div>
                <div class="item-text">Attendance Shift Report<small>Shift based reports</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon purple"><i class="fas fa-chart-pie"></i></div>
                <div class="item-text">Attendance Dynamic Report<small>Custom dynamic reports</small></div>
            </a>
        </div>
    </div>

   
    <div class="section">
        <div class="section-header"><i class="fas fa-cogs" style="margin-right:8px;font-size:12px;"></i>Attendance Processing & Summery</div>
        <div class="items-grid">
            <a href="#" class="item-card">
                <div class="item-icon orange"><i class="fas fa-user-check"></i></div>
                <div class="item-text">Analyze Employee Attendance Single<small>Single employee analysis</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon cyan"><i class="fas fa-file-alt"></i></div>
                <div class="item-text">Attendance Summary<small>Summary overview</small></div>
            </a>
        </div>
    </div>

    
    <div class="section">
        <div class="section-header"><i class="fas fa-umbrella-beach" style="margin-right:8px;font-size:12px;"></i>Holiday Management</div>
        <div class="items-grid">
            <a href="#" class="item-card">
                <div class="item-icon rose"><i class="fas fa-calendar-plus"></i></div>
                <div class="item-text">Holiday Registration<small>Register holidays</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon amber"><i class="fas fa-calendar-check"></i></div>
                <div class="item-text">Assign Holidays<small>Assign holidays to staff</small></div>
            </a>
        </div>
    </div>

   
    <div class="section">
        <div class="section-header"><i class="fas fa-users" style="margin-right:8px;font-size:12px;"></i>Group Creation</div>
        <div class="items-grid">
            <a href="#" class="item-card">
                <div class="item-icon blue"><i class="fas fa-user-plus"></i></div>
                <div class="item-text">Create Group<small>Create attendance groups</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon teal"><i class="fas fa-user-tag"></i></div>
                <div class="item-text">Assign to Group<small>Assign employees to groups</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon indigo"><i class="fas fa-calendar-alt"></i></div>
                <div class="item-text">Define Calendar<small>Define work calendars</small></div>
            </a>
        </div>
    </div>

    
    <div class="section">
        <div class="section-header"><i class="fas fa-clock" style="margin-right:8px;font-size:12px;"></i>Shift & Work Pattern Setup</div>
        <div class="items-grid">
            <a href="#" class="item-card">
                <div class="item-icon green"><i class="fas fa-business-time"></i></div>
                <div class="item-text">Create Shift<small>Define work shifts</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon orange"><i class="fas fa-coffee"></i></div>
                <div class="item-text">Create Breaks<small>Setup break times</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon purple"><i class="fas fa-calendar-day"></i></div>
                <div class="item-text">Leave Shift Creation<small>Create leave shifts</small></div>
            </a>
        </div>
    </div>

   
    <div class="section">
        <div class="section-header"><i class="fas fa-tasks" style="margin-right:8px;font-size:12px;"></i>Work Pattern & Shift Assignment</div>
        <div class="items-grid">
            <a href="#" class="item-card">
                <div class="item-icon cyan"><i class="fas fa-exchange-alt"></i></div>
                <div class="item-text">Assign To Shift<small>Assign employees to shifts</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon blue"><i class="fas fa-clipboard-check"></i></div>
                <div class="item-text">Roster Creation<small>Create work rosters</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon teal"><i class="fas fa-calendar-week"></i></div>
                <div class="item-text">Roster Assign By Day<small>Day-wise roster assign</small></div>
            </a>
        </div>
    </div>

    
    <div class="section">
        <div class="section-header solo"><i class="fas fa-layer-group" style="margin-right:8px;font-size:12px;"></i>Advanced Roster Management</div>
    </div>

    
    <div class="section">
        <div class="section-header"><i class="fas fa-edit" style="margin-right:8px;font-size:12px;"></i>Attendance Edit and Manual Attendance Management</div>
        <div class="items-grid">
            <a href="#" class="item-card">
                <div class="item-icon slate"><i class="fas fa-hand-pointer"></i></div>
                <div class="item-text">Manual Attendance Apply<small>Apply attendance manually</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon sky"><i class="fas fa-file-excel"></i></div>
                <div class="item-text">Manual Attendance Excel Upload<small>Upload via Excel</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon rose"><i class="fas fa-times-circle"></i></div>
                <div class="item-text">Manual Attendance Cancel<small>Cancel manual entries</small></div>
            </a>
        </div>
    </div>

    
    <div class="section">
        <div class="section-header solo"><i class="fas fa-sync-alt" style="margin-right:8px;font-size:12px;"></i>Override Attendance and recall override</div>
    </div>

  
    <div class="section">
        <div class="section-header"><i class="fas fa-plane-departure" style="margin-right:8px;font-size:12px;"></i>Leave Management</div>
        <div class="items-grid">
            <a href="#" class="item-card">
                <div class="item-icon amber"><i class="fas fa-balance-scale"></i></div>
                <div class="item-text">Leave Balance<small>View leave balances</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon rose"><i class="fas fa-ban"></i></div>
                <div class="item-text">Leave Cancel<small>Cancel leave requests</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon pink"><i class="fas fa-hand-point-up"></i></div>
                <div class="item-text">Leave Apply<small>Apply for leave</small></div>
            </a>
        </div>
    </div>

   
    <div class="section">
        <div class="section-header"><i class="fas fa-hourglass-half" style="margin-right:8px;font-size:12px;"></i>OverTime (OT) Management</div>
        <div class="items-grid">
            <a href="#" class="item-card">
                <div class="item-icon green"><i class="fas fa-check-double"></i></div>
                <div class="item-text">Daily OT Approvals<small>Approve daily overtime</small></div>
            </a>
            <a href="#" class="item-card">
                <div class="item-icon blue"><i class="fas fa-user-clock"></i></div>
                <div class="item-text">OT Approvals by Sessions<small>Session-wise OT approval</small></div>
            </a>
        </div>
    </div>

    
    <div class="section">
        <div class="section-header solo"><i class="fas fa-file-invoice" style="margin-right:8px;font-size:12px;"></i>TimeSheet Process</div>
    </div>
    <div class="section">
        <div class="section-header solo"><i class="fas fa-plus-circle" style="margin-right:8px;font-size:12px;"></i>Adding Extra No-Pay to session</div>
    </div>
    <div class="section">
        <div class="section-header solo"><i class="fas fa-thumbs-up" style="margin-right:8px;font-size:12px;"></i>Session Approval</div>
    </div>

</main>
</body>
</html>