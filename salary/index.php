<?php
session_start();
if (empty($_SESSION['logged_in'])) {
   
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salary | SMART HRIS™</title>
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
    --pill: #6ee7b7;
    --pill-text: #065f46;
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
.nav-left { display: flex; align-items: center; gap: 16px; }
.brand {
    display: flex; align-items: center; gap: 8px;
    color: #fff; font-weight: 600; font-size: 14px; text-decoration: none;
}
.brand-icon {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    border-radius: 7px; display: grid; place-items: center;
    font-size: 12px; color: #fff;
}
.nav-btn {
    width: 34px; height: 34px; border: none; background: transparent;
    color: #94a3b8; border-radius: 7px; cursor: pointer;
    display: grid; place-items: center; font-size: 14px; text-decoration: none;
}
.nav-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
.support-btn {
    background: transparent; border: none; color: #94a3b8;
    font-size: 13px; padding: 6px 10px; border-radius: 7px; cursor: pointer;
}
.search-nav {
    flex: 1; max-width: 360px; margin: 0 24px; position: relative;
}
.search-nav input {
    width: 100%; height: 34px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; padding: 0 12px 0 34px;
    color: #e2e8f0; font-size: 13px; outline: none;
}
.search-nav i {
    position: absolute; left: 12px; top: 50%;
    transform: translateY(-50%); color: #64748b; font-size: 12px;
}
.nav-right { display: flex; align-items: center; gap: 12px; margin-left: auto; }
.welcome { color: #94a3b8; font-size: 13px; }
.welcome strong { color: #f1f5f9; font-weight: 500; }
.avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    display: grid; place-items: center; color: #fff; font-weight: 600; font-size: 12px;
}

main { max-width: 1100px; margin: 0 auto; padding: 28px 22px 60px; }

.section { margin-bottom: 28px; }
.section-header {
    display: inline-flex; align-items: center;
    background: #a7f3d0;
    color: #065f46;
    font-size: 13px; font-weight: 600;
    padding: 7px 16px;
    border-radius: 20px;
    margin-bottom: 14px;
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}
@media (max-width: 900px) { .items-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) {
    .items-grid { grid-template-columns: 1fr; }
    .search-nav, .welcome { display: none; }
}

.item-card {
    display: flex; align-items: center; gap: 14px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px 18px;
    text-decoration: none;
    color: #1e3a5f;
    font-size: 13.5px;
    font-weight: 500;
    box-shadow: var(--shadow);
    transition: all 0.25s ease;
}
.item-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
    border-color: #bfdbfe;
}
.item-card small {
    display: block;
    font-size: 11.5px;
    font-weight: 400;
    color: var(--text-muted);
    margin-top: 2px;
}
.item-icon {
    width: 46px; height: 46px;
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 18px;
    flex-shrink: 0;
    color: #fff;
}
.item-icon.teal { background: linear-gradient(135deg, #2dd4bf, #0d9488); }
.item-icon.blue { background: linear-gradient(135deg, #60a5fa, #2563eb); }
.item-icon.indigo { background: linear-gradient(135deg, #818cf8, #4f46e5); }
.item-icon.purple { background: linear-gradient(135deg, #a78bfa, #7c3aed); }
.item-icon.rose { background: linear-gradient(135deg, #fb7185, #e11d48); }
.item-icon.orange { background: linear-gradient(135deg, #fb923c, #ea580c); }
.item-icon.amber { background: linear-gradient(135deg, #fbbf24, #d97706); }
.item-icon.green { background: linear-gradient(135deg, #4ade80, #16a34a); }
.item-icon.cyan { background: linear-gradient(135deg, #22d3ee, #0891b2); }
.item-icon.slate { background: linear-gradient(135deg, #94a3b8, #475569); }

.footer {
    text-align: center;
    margin-top: 40px;
    color: var(--text-muted);
    font-size: 12.5px;
}
</style>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="../index.php" class="brand">
            <div class="brand-icon"><i class="fas fa-th-large"></i></div>
            <span>SMART HRIS <sup style="font-size:8px;opacity:.7">™</sup></span>
        </a>
        <a href="../index.php" class="nav-btn" title="Back"><i class="fas fa-arrow-left"></i></a>
        <a href="../index.php" class="nav-btn" title="Home"><i class="fas fa-home"></i></a>
        <button class="support-btn">Support <i class="fas fa-chevron-down" style="font-size:9px"></i></button>
    </div>
    <div class="search-nav">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search...">
    </div>
    <div class="nav-right">
        <span class="welcome">Welcome <strong>Shakila</strong></span>
        <button class="nav-btn"><i class="fas fa-cog"></i></button>
        <div class="avatar">S</div>
    </div>
</nav>

<main>

    <!-- ========== Process Salary ========== -->
    <div class="section">
        <div class="section-header">Process Salary</div>
        <div class="items-grid">
            <a href="salary-session.php" class="item-card">
                <div class="item-icon teal"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="item-text">Salary Session<small>Create salary period</small></div>
            </a>
            <a href="attendance-summary.php" class="item-card">
                <div class="item-icon blue"><i class="fas fa-user-check"></i></div>
                <div class="item-text">Attendance Summary<small>Monthly attendance summary</small></div>
            </a>
            <a href="additions-deductions.php" class="item-card">
                <div class="item-icon indigo"><i class="fas fa-plus-minus"></i></div>
                <div class="item-text">Additions/Deductions<small>Pay items setup</small></div>
            </a>
            <a href="fix-additions.php" class="item-card">
                <div class="item-icon purple"><i class="fas fa-square-plus"></i></div>
                <div class="item-text">Fix Additions<small>Fixed addition setup</small></div>
            </a>
            <a href="fix-additions-assign.php" class="item-card">
                <div class="item-icon blue"><i class="fas fa-folder-plus"></i></div>
                <div class="item-text">Fix Additions Assign<small>Assign fixed additions</small></div>
            </a>
            <a href="fix-deductions.php" class="item-card">
                <div class="item-icon cyan"><i class="fas fa-scissors"></i></div>
                <div class="item-text">Fix Deductions<small>Fixed deduction setup</small></div>
            </a>
            <a href="fix-deductions-assign.php" class="item-card">
                <div class="item-icon rose"><i class="fas fa-circle-minus"></i></div>
                <div class="item-text">Fix Deductions Assign<small>Assign fixed deductions</small></div>
            </a>
            <a href="variable-addition-assign.php" class="item-card">
                <div class="item-icon teal"><i class="fas fa-circle-plus"></i></div>
                <div class="item-text">Variable Addition Assign<small>Variable additions</small></div>
            </a>
            <a href="variable-deduction-assign.php" class="item-card">
                <div class="item-icon slate"><i class="fas fa-minus"></i></div>
                <div class="item-text">Variable Deduction Assign<small>Variable deductions</small></div>
            </a>
            <a href="process-salary.php" class="item-card">
                <div class="item-icon green"><i class="fas fa-calculator"></i></div>
                <div class="item-text">Process Salary<small>Run salary process</small></div>
            </a>
            <a href="salary-increment-form.php" class="item-card">
                <div class="item-icon amber"><i class="fas fa-chart-line"></i></div>
                <div class="item-text">Salary Increment Form<small>Employee increment</small></div>
            </a>
            <a href="pay-item-delete.php" class="item-card">
                <div class="item-icon rose"><i class="fas fa-trash-alt"></i></div>
                <div class="item-text">Pay Item Delete<small>Remove pay items</small></div>
            </a>
            <a href="single-touch-payroll.php" class="item-card">
                <div class="item-icon cyan"><i class="fas fa-hand-pointer"></i></div>
                <div class="item-text">Single Touch Payroll<small>One-click payroll</small></div>
            </a>
            <a href="single-touch-payroll-process.php" class="item-card">
                <div class="item-icon blue"><i class="fas fa-hand-point-up"></i></div>
                <div class="item-text">Single Touch Payroll Process<small>Process STP</small></div>
            </a>
        </div>
    </div>

    <!-- ========== Loan ========== -->
    <div class="section">
        <div class="section-header">Loan</div>
        <div class="items-grid">
            <a href="loan-type.php" class="item-card">
                <div class="item-icon amber"><i class="fas fa-clipboard-list"></i></div>
                <div class="item-text">Loan Type<small>Define loan types</small></div>
            </a>
            <a href="loan.php" class="item-card">
                <div class="item-icon orange"><i class="fas fa-file-invoice"></i></div>
                <div class="item-text">Loan<small>Employee loan entry</small></div>
            </a>
            <a href="loan-settlement.php" class="item-card">
                <div class="item-icon green"><i class="fas fa-handshake"></i></div>
                <div class="item-text">Loan Settlement<small>Settle loans</small></div>
            </a>
            <a href="loan-process.php" class="item-card">
                <div class="item-icon teal"><i class="fas fa-cogs"></i></div>
                <div class="item-text">Loan Process<small>Process loan recovery</small></div>
            </a>
        </div>
    </div>

    <!-- ========== Bank Details ========== -->
    <div class="section">
        <div class="section-header">Bank Details</div>
        <div class="items-grid">
            <a href="bank-registration.php" class="item-card">
                <div class="item-icon blue"><i class="fas fa-university"></i></div>
                <div class="item-text">Bank Registration<small>Register banks</small></div>
            </a>
            <a href="branch-registration.php" class="item-card">
                <div class="item-icon indigo"><i class="fas fa-code-branch"></i></div>
                <div class="item-text">Branch Registration<small>Bank branches</small></div>
            </a>
            <a href="bank-account-type.php" class="item-card">
                <div class="item-icon cyan"><i class="fas fa-wallet"></i></div>
                <div class="item-text">Bank Account Type<small>Account types</small></div>
            </a>
            <a href="employee-bank-transfer.php" class="item-card">
                <div class="item-icon green"><i class="fas fa-exchange-alt"></i></div>
                <div class="item-text">Employee Bank Transfer<small>Salary to bank</small></div>
            </a>
            <a href="change-bank-transfer-to-cash.php" class="item-card">
                <div class="item-icon orange"><i class="fas fa-money-bill-wave"></i></div>
                <div class="item-text">Change Bank Transfer To Cash<small>Switch to cash</small></div>
            </a>
            <a href="employee-search-by-bank.php" class="item-card">
                <div class="item-icon teal"><i class="fas fa-search-dollar"></i></div>
                <div class="item-text">Employee Search By Bank Account<small>Find by account</small></div>
            </a>
        </div>
    </div>

    <!-- ========== Work Flow Hierarchy ========== -->
    <div class="section">
        <div class="section-header">Work Flow Hierarchy</div>
    </div>

    <!-- ========== Incentives / Products ========== -->
    <div class="section">
        <div class="section-header">Incentives/Products</div>
    </div>

    <!-- ========== Salary Master Setup ========== -->
    <div class="section">
        <div class="section-header">Salary Master Setup</div>
        <div class="items-grid">
            <a href="gl-master-setup.php" class="item-card">
                <div class="item-icon slate"><i class="fas fa-book"></i></div>
                <div class="item-text">GL Master Setup<small>General ledger mapping</small></div>
            </a>
        </div>
    </div>

    <!-- ========== Generate Notifications ========== -->
    <div class="section">
        <div class="section-header">Generate Notifications</div>
    </div>

    <p class="footer">SMART HRIS™ &copy; 2026 — Salary Module</p>
</main>

</body>
</html>