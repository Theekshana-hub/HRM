<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise | SMART HRIS™</title>
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

        .section { margin-bottom: 28px; animation: fadeUp 0.45s ease backwards; }
        .section:nth-child(1) { animation-delay: 0.05s; }
        .section:nth-child(2) { animation-delay: 0.12s; }
        .section:nth-child(3) { animation-delay: 0.19s; }

        .section-header {
            display: inline-flex; align-items: center;
            background: linear-gradient(135deg, #34d399, #10b981);
            color: white; font-size: 13.5px; font-weight: 600;
            padding: 8px 18px; border-radius: 50px; margin-bottom: 14px;
            box-shadow: 0 3px 10px rgba(16,185,129,0.25); letter-spacing: 0.2px;
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

        .item-text {
            font-size: 14px; font-weight: 500; color: var(--text-main); line-height: 1.35;
        }
        .item-text small {
            display: block; font-size: 12px; font-weight: 400;
            color: var(--text-muted); margin-top: 2px;
        }
        .toast {
            position: fixed; bottom: 28px; right: 28px;
            background: #0f172a; color: white; padding: 13px 20px;
            border-radius: 12px; font-size: 13.5px; font-weight: 500;
            box-shadow: 0 12px 28px rgba(0,0,0,0.25);
            transform: translateY(120%); opacity: 0;
            transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            z-index: 9999; display: flex; align-items: center; gap: 10px;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast i { color: #34d399; }
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
        <h1 class="page-title">Enterprise</h1>
    </div>

    <!-- ========== Company Hierarchy ========== -->
    <div class="section">
        <div class="section-header">
            <i class="fas fa-sitemap" style="margin-right:8px;font-size:12px;"></i>
            Company Hierarchy
        </div>
        <div class="items-grid">
            <a href="company-registration.php" class="item-card">
                <div class="item-icon teal"><i class="fas fa-building"></i></div>
                <div class="item-text">Company Registration<small>Register company details</small></div>
            </a>
            <a href="division-registration.php" class="item-card">
                <div class="item-icon blue"><i class="fas fa-project-diagram"></i></div>
                <div class="item-text">Division Registration<small>Manage divisions</small></div>
            </a>
            <a href="sub-division-registration.php" class="item-card">
                <div class="item-icon indigo"><i class="fas fa-network-wired"></i></div>
                <div class="item-text">Sub Division Registration<small>Sub division setup</small></div>
            </a>
            <a href="department-registration.php" class="item-card">
                <div class="item-icon orange"><i class="fas fa-users"></i></div>
                <div class="item-text">Department Registration<small>Create departments</small></div>
            </a>
            <a href="sub-department-registration.php" class="item-card">
                <div class="item-icon purple"><i class="fas fa-user-friends"></i></div>
                <div class="item-text">Sub Department Registration<small>Sub department setup</small></div>
            </a>
            <a href="section-registration.php" class="item-card">
                <div class="item-icon cyan"><i class="fas fa-th-large"></i></div>
                <div class="item-text">Section Registration<small>Section management</small></div>
            </a>
            <a href="sub-section-registration.php" class="item-card">
                <div class="item-icon green"><i class="fas fa-th"></i></div>
                <div class="item-text">Sub Section Registration<small>Sub section setup</small></div>
            </a>
        </div>
    </div>

   
    <div class="section">
        <div class="section-header">
            <i class="fas fa-briefcase" style="margin-right:8px;font-size:12px;"></i>
            Enterprise
        </div>
        <div class="items-grid">
            <a href="registration-years.php" class="item-card">
                <div class="item-icon rose"><i class="fas fa-calendar-alt"></i></div>
                <div class="item-text">Registration Years<small>Manage registration years</small></div>
            </a>
            <a href="company-rules.html" class="item-card">
                <div class="item-icon amber"><i class="fas fa-gavel"></i></div>
                <div class="item-text">Company Rules<small>Company policies & rules</small></div>
            </a>
            <a href="company-bank-account.html" class="item-card">
                <div class="item-icon teal"><i class="fas fa-university"></i></div>
                <div class="item-text">Company Bank Account Details<small>Bank account information</small></div>
            </a>
            <a href="carder-budgeting.html" class="item-card">
                <div class="item-icon blue"><i class="fas fa-calculator"></i></div>
                <div class="item-text">Carder Budgeting<small>Budget planning</small></div>
            </a>
            <a href="customer-registration.html" class="item-card">
                <div class="item-icon indigo"><i class="fas fa-desktop"></i></div>
                <div class="item-text">Customer Registration<small>Register customers</small></div>
            </a>
            <a href="company-location.html" class="item-card">
                <div class="item-icon orange"><i class="fas fa-map-marker-alt"></i></div>
                <div class="item-text">Company Location Registration<small>Company locations</small></div>
            </a>
            <a href="customer-location.html" class="item-card">
                <div class="item-icon purple"><i class="fas fa-map-marked-alt"></i></div>
                <div class="item-text">Customer Location Registration<small>Customer locations</small></div>
            </a>
            <a href="create-grades.html" class="item-card">
                <div class="item-icon cyan"><i class="fas fa-user-tag"></i></div>
                <div class="item-text">Create Grades<small>Employee grade setup</small></div>
            </a>
            <a href="lock-sites.html" class="item-card">
                <div class="item-icon slate"><i class="fas fa-lock"></i></div>
                <div class="item-text">Lock Sites<small>Site locking control</small></div>
            </a>
        </div>
    </div>

    <!-- ========== Allowances ========== -->
    <div class="section">
        <div class="section-header">
            <i class="fas fa-hand-holding-usd" style="margin-right:8px;font-size:12px;"></i>
            Allowances
        </div>
        <div class="items-grid">
            <a href="customer-site-allowances.html" class="item-card">
                <div class="item-icon green"><i class="fas fa-money-bill-wave"></i></div>
                <div class="item-text">Customer Site Allowances<small>Site based allowances</small></div>
            </a>
        </div>
    </div>

</main>

<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toast-msg">Done</span>
</div>

<script>
    function openItem(name) {
        const el = document.getElementById('toast');
        document.getElementById('toast-msg').textContent = 'Opening ' + name + '...';
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 2000);
    }
</script>
</body>
</html>
