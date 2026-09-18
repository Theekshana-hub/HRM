<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMART HRIS™ | HR Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --nav-bg: #111827;
            --card-bg: #ffffff;
            --page-bg: #f1f5f9;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent: #3b82f6;
            --accent-soft: #dbeafe;
            --border: #e2e8f0;
            --radius: 18px;
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 10px 15px -3px rgba(0,0,0,0.04);
            --shadow-hover: 0 20px 25px -5px rgba(59,130,246,0.12), 0 10px 10px -5px rgba(59,130,246,0.06);
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
            height: 58px;
            display: flex;
            align-items: center;
            padding: 0 22px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.25);
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.2px;
            text-decoration: none;
        }

        .brand-icon {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-size: 13px;
            color: white;
            box-shadow: 0 2px 8px rgba(59,130,246,0.4);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .nav-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: transparent;
            color: #94a3b8;
            border-radius: 8px;
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 15px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .nav-btn:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }

        .support-dropdown {
            display: flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 13.5px;
            font-weight: 500;
            padding: 7px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .support-dropdown:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        .search-wrap {
            flex: 1;
            max-width: 380px;
            margin: 0 28px;
            position: relative;
        }

        .search-wrap input {
            width: 100%;
            height: 36px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 9px;
            padding: 0 14px 0 38px;
            color: #e2e8f0;
            font-size: 13.5px;
            outline: none;
            transition: all 0.2s;
        }

        .search-wrap input::placeholder {
            color: #64748b;
        }

        .search-wrap input:focus {
            background: rgba(255,255,255,0.11);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
        }

        .search-wrap i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 13px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-left: auto;
        }

        .welcome {
            color: #94a3b8;
            font-size: 13.5px;
        }

        .welcome strong {
            color: #f1f5f9;
            font-weight: 500;
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: grid;
            place-items: center;
            color: white;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: 2px solid rgba(255,255,255,0.15);
        }

        /* ===================== MAIN ===================== */
        .container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 36px 24px 70px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 26px;
            letter-spacing: -0.3px;
        }

        /* ===================== CARDS GRID ===================== */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            overflow: hidden;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: var(--shadow);
            position: relative;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: #bfdbfe;
        }

        .card:active {
            transform: translateY(-4px);
        }

        .card-visual {
            height: 168px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .c1 .card-visual { background: linear-gradient(160deg, #dbeafe 0%, #eff6ff 60%, #f0f9ff 100%); }
        .c2 .card-visual { background: linear-gradient(160deg, #e0f2fe 0%, #f0f9ff 100%); }
        .c3 .card-visual { background: linear-gradient(160deg, #dbeafe 0%, #eff6ff 100%); }
        .c4 .card-visual { background: linear-gradient(160deg, #dbeafe 0%, #e0f2fe 70%, #f0f9ff 100%); }
        .c5 .card-visual { background: linear-gradient(160deg, #e0f2fe 0%, #f0f9ff 100%); }
        .c6 .card-visual { background: linear-gradient(160deg, #dbeafe 0%, #eff6ff 100%); }
        .c7 .card-visual { background: linear-gradient(160deg, #e0f2fe 0%, #dbeafe 100%); }

        .card-visual svg {
            width: 145px;
            height: 125px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.06));
            transition: transform 0.4s ease;
        }

        .card:hover .card-visual svg {
            transform: scale(1.05);
        }

        .card-bottom {
            padding: 15px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border-top: 1px solid #f1f5f9;
        }

        .card-name {
            font-size: 14.5px;
            font-weight: 500;
            color: var(--text-main);
            letter-spacing: -0.2px;
        }

        .dl-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1.5px solid #cbd5e1;
            background: #fff;
            color: #64748b;
            display: grid;
            place-items: center;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .dl-btn:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
            transform: scale(1.08);
        }

        @media (max-width: 1100px) {
            .cards-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 820px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
            .search-wrap { display: none; }
            .welcome { display: none; }
        }

        @media (max-width: 520px) {
            .cards-grid { grid-template-columns: 1fr; gap: 16px; }
            .container { padding: 24px 16px 50px; }
            .section-title { font-size: 18px; margin-bottom: 20px; }
            .card-visual { height: 150px; }
        }

        .toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            background: #0f172a;
            color: white;
            padding: 14px 22px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
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

        .toast i {
            color: #60a5fa;
        }

        .footer {
            text-align: center;
            margin-top: 48px;
            color: var(--text-muted);
            font-size: 13px;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            animation: fadeUp 0.5s ease backwards;
        }

        .card:nth-child(1) { animation-delay: 0.05s; }
        .card:nth-child(2) { animation-delay: 0.10s; }
        .card:nth-child(3) { animation-delay: 0.15s; }
        .card:nth-child(4) { animation-delay: 0.20s; }
        .card:nth-child(5) { animation-delay: 0.25s; }
        .card:nth-child(6) { animation-delay: 0.30s; }
        .card:nth-child(7) { animation-delay: 0.35s; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-left">
            <a href="smart-hris-dashboard-v2.html" class="brand">
                <div class="brand-icon">
                    <i class="fas fa-th-large"></i>
                </div>
                <span>SMART HRIS <sup style="font-size:9px;opacity:0.65;font-weight:400;">™</sup></span>
            </a>

            <div class="nav-actions">
                <a href="smart-hris-dashboard-v2.html" class="nav-btn" title="Home"><i class="fas fa-home"></i></a>
            </div>

            <button class="support-dropdown">
                Support <i class="fas fa-chevron-down" style="font-size:10px;margin-left:2px;"></i>
            </button>
        </div>

        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search modules, employees...">
        </div>

        <div class="nav-right">
            <span class="welcome">Welcome <strong>Shakila</strong></span>
            <button class="nav-btn"><i class="fas fa-cog"></i></button>
            <div class="avatar" title="Profile">S</div>
        </div>
    </nav>

    <main class="container">
        <h1 class="section-title">HR Panel</h1>

        <div class="cards-grid">

            <!-- 1. Manage Employee -->
            <a href="manage-employee.php" class="card c1">
                <div class="card-visual">
                    <svg viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="35" cy="28" r="16" fill="#93c5fd" opacity="0.45"/>
                        <circle cx="168" cy="22" r="11" fill="#60a5fa" opacity="0.35"/>
                        <rect x="28" y="112" width="144" height="7" rx="2" fill="#94a3b8"/>
                        <circle cx="68" cy="68" r="15" fill="#3b82f6"/>
                        <rect x="54" y="82" width="28" height="26" rx="5" fill="#1d4ed8"/>
                        <rect x="58" y="92" width="20" height="12" rx="2" fill="#60a5fa"/>
                        <rect x="48" y="110" width="40" height="5" rx="1.5" fill="#64748b"/>
                        <circle cx="128" cy="72" r="13" fill="#f472b6"/>
                        <rect x="116" y="84" width="24" height="23" rx="4" fill="#db2777"/>
                        <circle cx="98" cy="92" r="9" fill="#34d399"/>
                        <rect x="91" y="100" width="14" height="14" rx="3" fill="#059669"/>
                        <ellipse cx="22" cy="98" rx="11" ry="18" fill="#6ee7b7" opacity="0.65"/>
                        <ellipse cx="178" cy="95" rx="10" ry="16" fill="#34d399" opacity="0.55"/>
                    </svg>
                </div>
                <div class="card-bottom">
                    <span class="card-name">Manage Employee</span>
                    <button class="dl-btn" onclick="event.preventDefault(); event.stopPropagation(); toast('Download started')">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
            </a>

            <!-- 2. Enterprise -->
            <a href="enterprise/index.php" class="card c2">
                <div class="card-visual">
                    <svg viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="38" y="38" width="124" height="78" rx="7" fill="#bfdbfe"/>
                        <rect x="48" y="48" width="42" height="26" rx="4" fill="#3b82f6"/>
                        <rect x="100" y="48" width="52" height="11" rx="2.5" fill="#60a5fa"/>
                        <rect x="100" y="65" width="38" height="8" rx="2" fill="#93c5fd"/>
                        <rect x="52" y="90" width="9" height="18" rx="1.5" fill="#3b82f6"/>
                        <rect x="66" y="84" width="9" height="24" rx="1.5" fill="#60a5fa"/>
                        <rect x="80" y="94" width="9" height="14" rx="1.5" fill="#93c5fd"/>
                        <rect x="94" y="80" width="9" height="28" rx="1.5" fill="#2563eb"/>
                        <circle cx="138" cy="98" r="10" fill="#f59e0b"/>
                        <rect x="130" y="107" width="16" height="13" rx="2.5" fill="#d97706"/>
                        <circle cx="158" cy="102" r="8" fill="#10b981"/>
                        <rect x="152" y="109" width="12" height="11" rx="2" fill="#059669"/>
                    </svg>
                </div>
                <div class="card-bottom">
                    <span class="card-name">Enterprise</span>
                    <button class="dl-btn" onclick="event.preventDefault(); event.stopPropagation(); toast('Download started')">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
            </a>

            <!-- 3. Attendance Management -->
            <a href="attendance/index.php" class="card c3">
                <div class="card-visual">
                    <svg viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="68" y="22" width="64" height="108" rx="11" fill="#1e40af"/>
                        <rect x="74" y="32" width="52" height="82" rx="5" fill="#dbeafe"/>
                        <circle cx="100" cy="68" r="17" fill="white" stroke="#3b82f6" stroke-width="3"/>
                        <line x1="100" y1="68" x2="100" y2="56" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="100" y1="68" x2="110" y2="68" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="88" cy="98" r="3.2" fill="#3b82f6"/>
                        <circle cx="100" cy="98" r="3.2" fill="#60a5fa"/>
                        <circle cx="112" cy="98" r="3.2" fill="#93c5fd"/>
                        <circle cx="148" cy="82" r="12" fill="#f472b6"/>
                        <rect x="138" y="93" width="20" height="17" rx="3" fill="#db2777"/>
                        <ellipse cx="42" cy="115" rx="13" ry="20" fill="#6ee7b7" opacity="0.7"/>
                        <ellipse cx="162" cy="118" rx="11" ry="16" fill="#34d399" opacity="0.55"/>
                    </svg>
                </div>
                <div class="card-bottom">
                    <span class="card-name">Attendance Management</span>
                    <button class="dl-btn" onclick="event.preventDefault(); event.stopPropagation(); toast('Download started')">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
            </a>

            <!-- 4. Salary Processing -->
            <a href="salary.html" class="card c4">
                <div class="card-visual">
                    <svg viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="68" cy="98" rx="34" ry="38" fill="#fbbf24"/>
                        <path d="M48 70 Q68 50 88 70" fill="#f59e0b"/>
                        <text x="68" y="108" text-anchor="middle" font-size="26" font-weight="700" fill="#92400e">$</text>
                        <rect x="112" y="38" width="52" height="72" rx="4" fill="white" stroke="#3b82f6" stroke-width="2"/>
                        <rect x="120" y="50" width="36" height="5" rx="1.5" fill="#93c5fd"/>
                        <rect x="120" y="62" width="30" height="5" rx="1.5" fill="#bfdbfe"/>
                        <rect x="120" y="74" width="36" height="5" rx="1.5" fill="#93c5fd"/>
                        <rect x="120" y="86" width="22" height="5" rx="1.5" fill="#bfdbfe"/>
                        <circle cx="148" cy="122" r="10" fill="#fbbf24" stroke="#d97706" stroke-width="2"/>
                        <circle cx="162" cy="112" r="8" fill="#f59e0b" stroke="#b45309" stroke-width="1.5"/>
                        <circle cx="38" cy="48" r="10" fill="#3b82f6"/>
                        <rect x="30" y="57" width="16" height="13" rx="2.5" fill="#1e40af"/>
                    </svg>
                </div>
                <div class="card-bottom">
                    <span class="card-name">Salary Processing</span>
                    <button class="dl-btn" onclick="event.preventDefault(); event.stopPropagation(); toast('Download started')">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
            </a>

            <!-- 5. Reports -->
            <a href="reports.html" class="card c5">
                <div class="card-visual">
                    <svg viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M38 52 L38 118 Q38 126 46 126 L154 126 Q162 126 162 118 L162 58 Q162 50 154 50 L93 50 L83 40 L46 40 Q38 40 38 48 Z" fill="#fbbf24"/>
                        <path d="M38 52 L38 118 Q38 126 46 126 L154 126 Q162 126 162 118 L162 62 L38 62 Z" fill="#f59e0b"/>
                        <rect x="68" y="72" width="48" height="32" rx="3" fill="white" opacity="0.92"/>
                        <rect x="76" y="90" width="6" height="9" fill="#3b82f6"/>
                        <rect x="86" y="84" width="6" height="15" fill="#60a5fa"/>
                        <rect x="96" y="87" width="6" height="12" fill="#93c5fd"/>
                        <rect x="106" y="80" width="6" height="19" fill="#2563eb"/>
                        <circle cx="145" cy="88" r="11" fill="#a78bfa"/>
                        <rect x="136" y="98" width="18" height="16" rx="3" fill="#7c3aed"/>
                        <circle cx="52" cy="36" r="8" fill="#93c5fd" opacity="0.5"/>
                        <circle cx="172" cy="42" r="10" fill="#c4b5fd" opacity="0.4"/>
                    </svg>
                </div>
                <div class="card-bottom">
                    <span class="card-name">Reports</span>
                    <button class="dl-btn" onclick="event.preventDefault(); event.stopPropagation(); toast('Download started')">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
            </a>

            <!-- 6. Self Service Portal -->
            <a href="self-service.html" class="card c6">
                <div class="card-visual">
                    <svg viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="68" y="26" width="64" height="88" rx="7" fill="#1e40af"/>
                        <rect x="74" y="34" width="52" height="68" rx="4" fill="#dbeafe"/>
                        <rect x="84" y="46" width="32" height="5" rx="1.5" fill="#3b82f6"/>
                        <rect x="84" y="57" width="24" height="4" rx="1" fill="#93c5fd"/>
                        <rect x="84" y="66" width="28" height="4" rx="1" fill="#93c5fd"/>
                        <circle cx="100" cy="88" r="9" fill="#60a5fa"/>
                        <rect x="92" y="114" width="16" height="7" rx="2" fill="#64748b"/>
                        <rect x="82" y="121" width="36" height="5" rx="2" fill="#94a3b8"/>
                        <circle cx="148" cy="82" r="13" fill="#38bdf8"/>
                        <rect x="136" y="94" width="24" height="20" rx="4" fill="#0284c7"/>
                        <circle cx="38" cy="48" r="14" fill="#bae6fd" opacity="0.5"/>
                        <circle cx="52" cy="108" r="9" fill="#7dd3fc" opacity="0.4"/>
                    </svg>
                </div>
                <div class="card-bottom">
                    <span class="card-name">Self Service Portal</span>
                    <button class="dl-btn" onclick="event.preventDefault(); event.stopPropagation(); toast('Download started')">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
            </a>

            <!-- 7. Work Flow Hierarchy -->
            <a href="workflow.html" class="card c7">
                <div class="card-visual">
                    <svg viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="100" cy="78" r="44" fill="none" stroke="#93c5fd" stroke-width="7" stroke-dasharray="18 9"/>
                        <circle cx="100" cy="78" r="29" fill="none" stroke="#60a5fa" stroke-width="5.5" stroke-dasharray="14 7"/>
                        <circle cx="100" cy="78" r="15" fill="#3b82f6"/>
                        <path d="M95 78 L100 73 L105 78 L100 83 Z" fill="white"/>
                        <circle cx="52" cy="52" r="11" fill="#f472b6"/>
                        <rect x="43" y="62" width="18" height="13" rx="2.5" fill="#db2777"/>
                        <circle cx="148" cy="52" r="11" fill="#34d399"/>
                        <rect x="139" y="62" width="18" height="13" rx="2.5" fill="#059669"/>
                        <circle cx="52" cy="108" r="11" fill="#fbbf24"/>
                        <rect x="43" y="118" width="18" height="13" rx="2.5" fill="#d97706"/>
                        <circle cx="148" cy="108" r="11" fill="#a78bfa"/>
                        <rect x="139" y="118" width="18" height="13" rx="2.5" fill="#7c3aed"/>
                        <line x1="68" y1="68" x2="86" y2="73" stroke="#94a3b8" stroke-width="1.5"/>
                        <line x1="132" y1="68" x2="114" y2="73" stroke="#94a3b8" stroke-width="1.5"/>
                    </svg>
                </div>
                <div class="card-bottom">
                    <span class="card-name">Work Flow Hierarchy</span>
                    <button class="dl-btn" onclick="event.preventDefault(); event.stopPropagation(); toast('Download started')">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
            </a>

        </div>

        <p class="footer">SMART HRIS™ &copy; 2026 — Built for modern HR teams</p>
    </main>

    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toast-msg">Done</span>
    </div>

    <script>
        function toast(msg) {
            const el = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            el.classList.add('show');
            setTimeout(() => el.classList.remove('show'), 2400);
        }
    </script>
</body>
</html>
