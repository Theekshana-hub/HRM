<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employee | SMART HRIS™</title>
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
            --accent-soft: #dbeafe;
            --header-bg: #60a5fa;
            --header-bg-hover: #3b82f6;
            --border: #e2e8f0;
            --radius: 14px;
            --shadow: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-hover: 0 8px 20px rgba(59,130,246,0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--page-bg);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.5;
        }

        /* ===================== NAVBAR ===================== */
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

        .nav-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
        }

        .brand-icon {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            border-radius: 7px;
            display: grid;
            place-items: center;
            font-size: 12px;
            color: white;
        }

        .nav-btn {
            width: 34px;
            height: 34px;
            border: none;
            background: transparent;
            color: #94a3b8;
            border-radius: 8px;
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .nav-btn:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }

        .support-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 7px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .support-btn:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        .search-wrap {
            flex: 1;
            max-width: 360px;
            margin: 0 24px;
            position: relative;
        }

        .search-wrap input {
            width: 100%;
            height: 34px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 0 12px 0 36px;
            color: #e2e8f0;
            font-size: 13px;
            outline: none;
            transition: all 0.2s;
        }

        .search-wrap input::placeholder { color: #64748b; }
        .search-wrap input:focus {
            background: rgba(255,255,255,0.11);
            border-color: #3b82f6;
        }

        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 12px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
        }

        .welcome {
            color: #94a3b8;
            font-size: 13px;
        }

        .welcome strong {
            color: #f1f5f9;
            font-weight: 500;
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: grid;
            place-items: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
        }

        /* ===================== MAIN ===================== */
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 24px 60px;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 32px;
        }

        .back-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: white;
            color: var(--text-muted);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 14px;
        }

        .back-btn:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-main);
            letter-spacing: -0.3px;
        }

        /* ===================== SECTIONS ===================== */
        .section {
            margin-bottom: 28px;
            animation: fadeUp 0.45s ease backwards;
        }

        .section:nth-child(1) { animation-delay: 0.05s; }
        .section:nth-child(2) { animation-delay: 0.12s; }
        .section:nth-child(3) { animation-delay: 0.19s; }
        .section:nth-child(4) { animation-delay: 0.26s; }
        .section:nth-child(5) { animation-delay: 0.33s; }

        .section-header {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            color: white;
            font-size: 13.5px;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 50px;
            margin-bottom: 14px;
            box-shadow: 0 3px 10px rgba(59,130,246,0.25);
            letter-spacing: 0.2px;
        }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
        }

        .item-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: all 0.28s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: var(--shadow);
            text-decoration: none;
            color: inherit;
        }

        .item-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: #bfdbfe;
        }

        .item-icon {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            font-size: 18px;
            color: white;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }

        .item-icon.blue   { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .item-icon.teal   { background: linear-gradient(135deg, #0d9488, #2dd4bf); }
        .item-icon.indigo { background: linear-gradient(135deg, #4f46e5, #818cf8); }
        .item-icon.orange { background: linear-gradient(135deg, #ea580c, #fb923c); }
        .item-icon.purple { background: linear-gradient(135deg, #7c3aed, #a78bfa); }
        .item-icon.rose   { background: linear-gradient(135deg, #e11d48, #fb7185); }

        .item-text {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-main);
            line-height: 1.35;
        }

        .item-text small {
            display: block;
            font-size: 12px;
            font-weight: 400;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Empty section style */
        .empty-section .section-header {
            opacity: 0.85;
        }

        /* ===================== TOAST ===================== */
        .toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            background: #0f172a;
            color: white;
            padding: 13px 20px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 500;
            box-shadow: 0 12px 28px rgba(0,0,0,0.25);
            transform: translateY(120%);
            opacity: 0;
            transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast i { color: #60a5fa; }

        /* Animation */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 700px) {
            .search-wrap { display: none; }
            .welcome { display: none; }
            .items-grid { grid-template-columns: 1fr; }
            .container { padding: 24px 16px 50px; }
        }
    </style>
</head>
<body>

    <!-- ===================== NAVBAR ===================== -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.php" class="brand">
                <div class="brand-icon"><i class="fas fa-th-large"></i></div>
                <span>SMART HRIS <sup style="font-size:9px;opacity:0.65;">™</sup></span>
            </a>
            <a href="index.php" class="nav-btn" title="Back"><i class="fas fa-arrow-left"></i></a>
            <a href="index.php" class="nav-btn" title="Home"><i class="fas fa-home"></i></a>
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

    <!-- ===================== MAIN ===================== -->
    <main class="container">

        <div class="page-header">
            <a href="index.php" class="back-btn" title="Back to HR Panel">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">Manage Employee</h1>
        </div>

        <!-- ========== Section 1: Manage Employee ========== -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-users" style="margin-right:8px;font-size:12px;"></i>
                Manage Employee
            </div>
            <div class="items-grid">
                <a href="add-employee.php" class="item-card">
                    <div class="item-icon blue">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="item-text">
                        Employee (Add / Edit)
                        <small>Create or update employee records</small>
                    </div>
                </a>

                <a href="#" class="item-card" onclick="openItem('Employee Transfer and Lifecycle'); return false;">
                    <div class="item-icon teal">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="item-text">
                        Employee Transfer and Lifecycle
                        <small>Transfers, promotions & lifecycle events</small>
                    </div>
                </a>

                <a href="#" class="item-card" onclick="openItem('Employee NIC Number Search'); return false;">
                    <div class="item-icon indigo">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="item-text">
                        Employee NIC Number Search
                        <small>Quick search by National ID</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- ========== Section 2: Employee Life Cycle Management ========== -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-sync-alt" style="margin-right:8px;font-size:12px;"></i>
                Employee Life Cycle Management
            </div>
            <div class="items-grid">
                <a href="#" class="item-card" onclick="openItem('Employee Lifecycle Detail View'); return false;">
                    <div class="item-icon purple">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="item-text">
                        Employee Lifecycle Detail View
                        <small>Full timeline of employee journey</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- ========== Section 3: Master Setup ========== -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-cogs" style="margin-right:8px;font-size:12px;"></i>
                Master Setup
            </div>
            <div class="items-grid">
                <a href="#" class="item-card" onclick="openItem('Employee Master Setup'); return false;">
                    <div class="item-icon orange">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <div class="item-text">
                        Employee Master Setup
                        <small>Configure departments, positions & more</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- ========== Section 4: Employee Profile Setting ========== -->
        <div class="section empty-section">
            <div class="section-header">
                <i class="fas fa-user-cog" style="margin-right:8px;font-size:12px;"></i>
                Employee Profile Setting
            </div>
            <!-- No items yet - matching original -->
        </div>

        <!-- ========== Section 5: Succession Planning ========== -->
        <div class="section empty-section">
            <div class="section-header">
                <i class="fas fa-sitemap" style="margin-right:8px;font-size:12px;"></i>
                Succession Planning
            </div>
            <!-- No items yet - matching original -->
        </div>

    </main>

    <!-- Toast -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toast-msg">Done</span>
    </div>

    <script>
        function openItem(name) {
            const el = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = `Opening ${name}...`;
            el.classList.add('show');
            setTimeout(() => el.classList.remove('show'), 2300);
        }
    </script>
</body>
</html>
