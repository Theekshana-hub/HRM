<?php
// add-employee.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Profile | SMART HRIS™</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --nav-bg: #1a2332;
            --page-bg: #f1f5f9;
            --accent: #3b82f6;
            --required: #ef4444;
            --input-bg: #dbeafe;
            --border: #e2e8f0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--page-bg);
            color: #0f172a;
            min-height: 100vh;
            font-size: 13.5px;
        }
        .navbar {
            background: var(--nav-bg);
            height: 52px;
            display: flex;
            align-items: center;
            padding: 0 18px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-left { display: flex; align-items: center; gap: 14px; }
        .brand {
            display: flex; align-items: center; gap: 8px;
            color: #fff; font-weight: 600; font-size: 14px; text-decoration: none;
        }
        .brand-icon {
            width: 26px; height: 26px;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            border-radius: 6px; display: grid; place-items: center;
            font-size: 11px; color: white;
        }
        .nav-btn {
            width: 32px; height: 32px; border: none; background: transparent;
            color: #94a3b8; border-radius: 6px; cursor: pointer;
            display: grid; place-items: center; font-size: 13px; text-decoration: none;
        }
        .nav-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .support-btn {
            background: transparent; border: none; color: #94a3b8;
            font-size: 12.5px; padding: 5px 8px; border-radius: 6px; cursor: pointer;
        }
        .search-nav {
            flex: 1; max-width: 340px; margin: 0 20px; position: relative;
        }
        .search-nav input {
            width: 100%; height: 32px; background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 7px;
            padding: 0 12px 0 32px; color: #e2e8f0; font-size: 12.5px; outline: none;
        }
        .search-nav i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 11px; }
        .nav-right { display: flex; align-items: center; gap: 10px; margin-left: auto; }
        .welcome { color: #94a3b8; font-size: 12.5px; }
        .welcome strong { color: #f1f5f9; font-weight: 500; }
        .avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: grid; place-items: center; color: white; font-weight: 600; font-size: 12px;
        }
        .container { max-width: 1100px; margin: 0 auto; padding: 22px 20px 50px; }
        .page-title { font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #1e293b; }
        .top-bar {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 18px; gap: 16px; flex-wrap: wrap;
        }
        .search-box {
            flex: 1; max-width: 420px; position: relative;
        }
        .search-box input {
            width: 100%; height: 38px; border: 1px solid #cbd5e1; border-radius: 8px;
            padding: 0 14px 0 38px; font-size: 13px; outline: none;
        }
        .search-box input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        .search-box i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .action-btns { display: flex; gap: 8px; }
        .btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            width: 64px; height: 54px; border: none; border-radius: 8px; cursor: pointer;
            font-size: 11px; font-weight: 500; color: white; gap: 3px; transition: all 0.2s;
        }
        .btn i { font-size: 16px; }
        .btn-search { background: #0ea5e9; }
        .btn-save { background: #2563eb; }
        .btn-new { background: #16a34a; }
        .btn:hover { filter: brightness(1.08); transform: translateY(-1px); }
        .photo-box {
            background: white; border: 1px solid var(--border); border-radius: 10px;
            padding: 20px; margin-bottom: 18px; display: flex; align-items: center; gap: 20px;
        }
        .photo-preview {
            width: 90px; height: 90px; border-radius: 50%; background: #e2e8f0;
            display: grid; place-items: center; color: #94a3b8; font-size: 36px; overflow: hidden;
        }
        .photo-preview img { width: 100%; height: 100%; object-fit: cover; }
        .file-label {
            display: inline-block; padding: 6px 14px; border: 1px solid #cbd5e1;
            border-radius: 6px; font-size: 12px; cursor: pointer; background: #f8fafc;
        }
        .file-label:hover { background: #f1f5f9; }
        #photoInput { display: none; }
        .accordion { margin-bottom: 8px; }
        .acc-header {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; border-radius: 8px; cursor: pointer;
            color: white; font-weight: 500; font-size: 13.5px;
            background: repeating-linear-gradient(-45deg, #1e3a5f, #1e3a5f 8px, #243b5c 8px, #243b5c 16px);
            user-select: none;
        }
        .acc-header.blue {
            background: repeating-linear-gradient(-45deg, #2563eb, #2563eb 8px, #3b82f6 8px, #3b82f6 16px);
        }
        .acc-header i.arrow { margin-left: auto; transition: transform 0.25s; font-size: 12px; }
        .acc-header.open i.arrow { transform: rotate(90deg); }
        .acc-body {
            background: white; border: 1px solid var(--border); border-top: none;
            border-radius: 0 0 8px 8px; padding: 20px; display: none;
        }
        .acc-body.open { display: block; }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 28px;
        }
        .form-grid.three { grid-template-columns: 1fr 1fr 1fr; }
        .form-group { display: flex; flex-direction: column; gap: 4px; }
        .form-group.full { grid-column: 1 / -1; }
        label { font-size: 12.5px; font-weight: 500; color: #334155; }
        label .req { color: var(--required); }
        input[type="text"], input[type="date"], input[type="number"], input[type="email"],
        select, textarea {
            height: 34px; border: 1px solid #cbd5e1; border-radius: 6px;
            padding: 0 10px; font-size: 13px; outline: none; background: #fff;
            font-family: inherit; transition: border 0.2s; width: 100%;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--accent); box-shadow: 0 0 0 2px rgba(59,130,246,0.12);
        }
        input.highlight, select.highlight { background: var(--input-bg); }
        textarea { height: 80px; padding: 8px 10px; resize: vertical; }
        .toggle-row { display: flex; align-items: center; gap: 8px; }
        .toggle {
            width: 40px; height: 22px; background: #cbd5e1; border-radius: 20px;
            position: relative; cursor: pointer; transition: 0.2s;
        }
        .toggle.on { background: #22c55e; }
        .toggle::after {
            content: ''; position: absolute; width: 18px; height: 18px;
            background: white; border-radius: 50%; top: 2px; left: 2px; transition: 0.2s;
        }
        .toggle.on::after { left: 20px; }
        .toggle.red.on { background: #ef4444; }
        .day-row {
            display: grid; grid-template-columns: 90px repeat(4, 1fr);
            gap: 8px; align-items: center; margin-bottom: 8px;
        }
        .day-row label { font-weight: 500; }
        .calc-btn {
            background: #2563eb; color: white; border: none; padding: 7px 16px;
            border-radius: 6px; font-size: 12.5px; cursor: pointer; margin-top: 6px;
        }
        .calc-btn:hover { background: #1d4ed8; }
        .add-btn {
            background: #e2e8f0; border: none; padding: 6px 14px; border-radius: 6px;
            font-size: 12px; cursor: pointer; margin-top: 6px;
        }
        @media (max-width: 700px) {
            .form-grid, .form-grid.three { grid-template-columns: 1fr; }
            .search-nav, .welcome { display: none; }
            .day-row { grid-template-columns: 70px 1fr 1fr; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="manage-employee.php" class="brand">
            <div class="brand-icon"><i class="fas fa-th-large"></i></div>
            <span>SMART HRIS <sup style="font-size:8px;opacity:0.7;">™</sup></span>
        </a>
        <a href="manage-employee.php" class="nav-btn" title="Back"><i class="fas fa-arrow-left"></i></a>
        <a href="manage-employee.php" class="nav-btn" title="Home"><i class="fas fa-home"></i></a>
        <button class="support-btn">Support <i class="fas fa-chevron-down" style="font-size:9px;"></i></button>
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

<main class="container">
    <h1 class="page-title">Employee Profile</h1>

    <!-- ===================== FORM START ===================== -->
    <form action="save_employee.php" method="POST" enctype="multipart/form-data">

        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search_emp_no" name="search_emp" placeholder="Enter Employee Number..." onkeypress="if(event.key==='Enter'){event.preventDefault();searchEmployee();}">
            </div>
            <div class="action-btns">
                <button type="button" class="btn btn-search" onclick="searchEmployee()"><i class="fas fa-search"></i>Search</button>
                <button type="submit" class="btn btn-save"><i class="fas fa-save"></i>Save</button>
                <button type="reset" class="btn btn-new"><i class="fas fa-file"></i>New</button>
            </div>
        </div>

        <!-- Photo -->
        <div class="photo-box">
            <div class="photo-preview" id="photoPreview">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <label class="file-label">
                    Choose file
                    <input type="file" name="photo" id="photoInput" accept="image/*" onchange="previewPhoto(this)">
                </label>
            </div>
        </div>

        <!-- ========== 1. Employee Master ========== -->
        <div class="accordion">
            <div class="acc-header open" onclick="toggleAcc(this)">
                <i class="fas fa-user"></i> Employee Master
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="acc-body open">
                <h3 style="margin-bottom:14px;font-size:14px;">Personal Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Emp No <span class="req">*</span></label>
                        <input type="text" name="emp_no" class="highlight" required>
                    </div>
                    <div class="form-group">
                        <label>Gender <span class="req">*</span></label>
                        <select name="gender" class="highlight" required>
                            <option value="">-select-</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Title <span class="req">*</span></label>
                        <select name="title" class="highlight" required>
                            <option value="">-select-</option>
                            <option value="Mr">Mr</option>
                            <option value="Mrs">Mrs</option>
                            <option value="Miss">Miss</option>
                            <option value="Dr">Dr</option>
                            <option value="Prof">Prof</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Initials <span class="req">*</span></label>
                        <input type="text" name="initials" class="highlight" required>
                    </div>
                    <div class="form-group full">
                        <label>Denoted by Initial</label>
                        <input type="text" name="denoted_by_initial">
                    </div>
                    <div class="form-group full">
                        <label>First Name</label>
                        <input type="text" name="first_name">
                    </div>
                    <div class="form-group full">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name">
                    </div>
                    <div class="form-group full">
                        <label>Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" class="highlight" required>
                    </div>
                    <div class="form-group full">
                        <label>Name With Initials <span class="req">*</span></label>
                        <input type="text" name="name_with_initials" class="highlight" required>
                    </div>
                    <div class="form-group full">
                        <label>Full Name</label>
                        <input type="text" name="full_name">
                    </div>
                    <div class="form-group">
                        <label>Office Use Name <span class="req">*</span></label>
                        <input type="text" name="office_use_name" class="highlight" required>
                    </div>
                    <div class="form-group">
                        <label>Language <span class="req">*</span></label>
                        <select name="language" class="highlight" required>
                            <option value="">-select-</option>
                            <option value="Sinhala">Sinhala</option>
                            <option value="Tamil">Tamil</option>
                            <option value="English">English</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Passport No</label>
                        <input type="text" name="passport_no">
                    </div>
                    <div class="form-group">
                        <label>Marital Status <span class="req">*</span></label>
                        <select name="marital_status" class="highlight" required>
                            <option value="">-select-</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Of Birth <span class="req">*</span></label>
                        <input type="date" name="date_of_birth" class="highlight" required>
                    </div>
                    <div class="form-group">
                        <label>Nationality <span class="req">*</span></label>
                        <select name="nationality" class="highlight" required>
                            <option value="">-select-</option>
                            <option value="Sri Lankan">Sri Lankan</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="text" name="age">
                    </div>
                    <div class="form-group">
                        <label>Race <span class="req">*</span></label>
                        <select name="race" class="highlight" required>
                            <option value="">-select-</option>
                            <option value="Sinhala">Sinhala</option>
                            <option value="Tamil">Tamil</option>
                            <option value="Muslim">Muslim</option>
                            <option value="Burgher">Burgher</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Birth Place</label>
                        <input type="text" name="birth_place">
                    </div>
                    <div class="form-group">
                        <label>Religion <span class="req">*</span></label>
                        <select name="religion" class="highlight" required>
                            <option value="">-select-</option>
                            <option value="Buddhism">Buddhism</option>
                            <option value="Hinduism">Hinduism</option>
                            <option value="Islam">Islam</option>
                            <option value="Christianity">Christianity</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>NIC <span class="req">*</span></label>
                        <input type="text" name="nic" class="highlight" required>
                    </div>
                    <div class="form-group">
                        <label>Blood Group <span class="req">*</span></label>
                        <select name="blood_group" class="highlight" required>
                            <option value="">-select-</option>
                            <option value="A+">A+</option><option value="A-">A-</option>
                            <option value="B+">B+</option><option value="B-">B-</option>
                            <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            <option value="O+">O+</option><option value="O-">O-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Employee ID Issue Date</label>
                        <input type="date" name="employee_id_issue_date">
                    </div>
                    <div class="form-group">
                        <label>Distance From Work Place <span class="req">*</span></label>
                        <select name="distance_from_work" class="highlight" required>
                            <option value="">-select-</option>
                            <option value="0-5 km">0-5 km</option>
                            <option value="5-10 km">5-10 km</option>
                            <option value="10-20 km">10-20 km</option>
                            <option value="20+ km">20+ km</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== 2. Official Details ========== -->
        <div class="accordion">
            <div class="acc-header blue" onclick="toggleAcc(this)">
                <i class="fas fa-user-tie"></i> Official Details
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="acc-body">
                <div class="form-grid three">
                    <div class="form-group">
                        <label>Employee Office ID No <span class="req">*</span></label>
                        <input type="text" name="employee_office_id" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Tax Payee No</label>
                        <input type="text" name="tax_payee_no">
                    </div>
                    <div class="form-group">
                        <label>Company Name <span class="req">*</span></label>
                        <select name="company_name" class="highlight">
                            <option value="">-select-</option>
                            <option value="Main Company">Main Company</option>
                            <option value="Branch A">Branch A</option>
                            <option value="Branch B">Branch B</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Proximity Card No <span class="req">*</span></label>
                        <input type="text" name="proximity_card_no" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Job Source <span class="req">*</span></label>
                        <select name="job_source" class="highlight">
                            <option value="Interviews">Interviews</option>
                            <option value="Referral">Referral</option>
                            <option value="Online">Online</option>
                            <option value="Agency">Agency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Division Name <span class="req">*</span></label>
                        <select name="division_name" class="highlight">
                            <option value="">-select-</option>
                            <option value="Operations">Operations</option>
                            <option value="HR">HR</option>
                            <option value="Finance">Finance</option>
                            <option value="IT">IT</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Attendance Account No <span class="req">*</span></label>
                        <input type="text" name="attendance_account_no" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Manpower Company <span class="req">*</span></label>
                        <select name="manpower_company" class="highlight">
                            <option value="None">None</option>
                            <option value="Company A">Company A</option>
                            <option value="Company B">Company B</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sub Division Name <span class="req">*</span></label>
                        <select name="sub_division_name" class="highlight">
                            <option value="">-select-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Is Active Employee <span class="req">*</span></label>
                        <input type="hidden" name="is_active_employee" id="is_active_employee" value="1">
                        <div class="toggle-row">
                            <div class="toggle on" onclick="toggleSwitch(this, 'is_active_employee')"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Salary Process <span class="req">*</span></label>
                        <input type="hidden" name="salary_process" id="salary_process" value="1">
                        <div class="toggle-row">
                            <div class="toggle on" onclick="toggleSwitch(this, 'salary_process')"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Department Name <span class="req">*</span></label>
                        <select name="department_name" class="highlight">
                            <option value="">-select-</option>
                            <option value="HR Department">HR Department</option>
                            <option value="Finance">Finance</option>
                            <option value="Production">Production</option>
                            <option value="Sales">Sales</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>PPF/EPF No <span class="req">*</span></label>
                        <input type="text" name="ppf_epf_no" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Salary Period <span class="req">*</span></label>
                        <select name="salary_period" class="highlight">
                            <option value="Monthly">Monthly</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Bi-Weekly">Bi-Weekly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sub Department Name <span class="req">*</span></label>
                        <select name="sub_department_name" class="highlight">
                            <option value="">-select-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Employee Fund <span class="req">*</span></label>
                        <select name="employee_fund" class="highlight">
                            <option value="EPF Applicable">EPF Applicable</option>
                            <option value="Not Applicable">Not Applicable</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Basic Salary <span class="req">*</span></label>
                        <input type="number" name="basic_salary" class="highlight" value="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Section Name <span class="req">*</span></label>
                        <select name="section_name" class="highlight">
                            <option value="">-select-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ETF No <span class="req">*</span></label>
                        <input type="text" name="etf_no" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Statute Name <span class="req">*</span></label>
                        <select name="statute_name" class="highlight">
                            <option value="Shop & Office">Shop & Office</option>
                            <option value="Wages Board">Wages Board</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sub Section Name <span class="req">*</span></label>
                        <select name="sub_section_name" class="highlight">
                            <option value="">-select-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Is Tax Payee <span class="req">*</span></label>
                        <input type="hidden" name="is_tax_payee" id="is_tax_payee" value="0">
                        <div class="toggle-row">
                            <div class="toggle red" onclick="toggleSwitch(this, 'is_tax_payee')"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Next Salary Increment Date</label>
                        <input type="date" name="next_salary_increment_date">
                    </div>
                    <div class="form-group">
                        <label>Base Customer Site</label>
                        <input type="text" name="base_customer_site">
                    </div>
                    <div class="form-group">
                        <label>ERP Code</label>
                        <input type="text" name="erp_code">
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== 3. Address Details ========== -->
        <div class="accordion">
            <div class="acc-header" onclick="toggleAcc(this)">
                <i class="fas fa-at"></i> Address Details
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="acc-body">
                <div class="form-grid three">
                    <div class="form-group full">
                        <label>Address No</label>
                        <input type="text" name="address_no">
                    </div>
                    <div class="form-group">
                        <label>Contact Type <span class="req">*</span></label>
                        <select name="contact_type" class="highlight">
                            <option value="">-select-</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Temporary">Temporary</option>
                            <option value="Official">Official</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Postal Code / Zip Code</label>
                        <input type="text" name="postal_code">
                    </div>
                    <div class="form-group">
                        <label>Work Tel</label>
                        <input type="text" name="work_tel">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city">
                    </div>
                    <div class="form-group">
                        <label>Country <span class="req">*</span></label>
                        <select name="country" class="highlight">
                            <option value="Sri Lanka">Sri Lanka</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Office Email</label>
                        <input type="email" name="office_email" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>District <span class="req">*</span></label>
                        <select name="district" class="highlight">
                            <option value="">-select-</option>
                            <option value="Colombo">Colombo</option>
                            <option value="Gampaha">Gampaha</option>
                            <option value="Kalutara">Kalutara</option>
                            <option value="Kandy">Kandy</option>
                            <option value="Galle">Galle</option>
                            <option value="Matara">Matara</option>
                            <option value="Kurunegala">Kurunegala</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tel No</label>
                        <input type="text" name="tel_no">
                    </div>
                    <div class="form-group">
                        <label>Private Email</label>
                        <input type="email" name="private_email">
                    </div>
                    <div class="form-group">
                        <label>Province <span class="req">*</span></label>
                        <select name="province" class="highlight">
                            <option value="">-select-</option>
                            <option value="Western">Western</option>
                            <option value="Central">Central</option>
                            <option value="Southern">Southern</option>
                            <option value="Northern">Northern</option>
                            <option value="Eastern">Eastern</option>
                            <option value="North Western">North Western</option>
                            <option value="North Central">North Central</option>
                            <option value="Uva">Uva</option>
                            <option value="Sabaragamuwa">Sabaragamuwa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mobile No</label>
                        <input type="text" name="mobile_no">
                    </div>
                    <div class="form-group full">
                        <label>Comments</label>
                        <textarea name="comments"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== 4. Job Details ========== -->
        <div class="accordion">
            <div class="acc-header blue" onclick="toggleAcc(this)">
                <i class="fas fa-briefcase"></i> Job Details
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="acc-body">
                <div class="form-grid three">
                    <div class="form-group">
                        <label>Designation <span class="req">*</span></label>
                        <input type="text" name="designation" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Resign Date</label>
                        <input type="date" name="resign_date">
                    </div>
                    <div class="form-group">
                        <label>Reporting Supervisor <span class="req">*</span></label>
                        <input type="text" name="reporting_supervisor" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Join Date <span class="req">*</span></label>
                        <input type="date" name="join_date" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Leaving Type</label>
                        <select name="leaving_type">
                            <option value="">-select-</option>
                            <option value="Resignation">Resignation</option>
                            <option value="Termination">Termination</option>
                            <option value="Retirement">Retirement</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Job Level <span class="req">*</span></label>
                        <select name="job_level" class="highlight">
                            <option value="Executive">Executive</option>
                            <option value="Non-Executive">Non-Executive</option>
                            <option value="Manager">Manager</option>
                            <option value="Senior Manager">Senior Manager</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date of Appointment / Contract start date <span class="req">*</span></label>
                        <input type="date" name="appointment_date" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Retirement Date</label>
                        <input type="date" name="retirement_date">
                    </div>
                    <div class="form-group">
                        <label>Rejoin Date</label>
                        <input type="date" name="rejoin_date">
                    </div>
                    <div class="form-group">
                        <label>Work Category <span class="req">*</span></label>
                        <select name="work_category" class="highlight">
                            <option value="">-select-</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Contract">Contract</option>
                            <option value="Casual">Casual</option>
                            <option value="Trainee">Trainee</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grade</label>
                        <select name="grade">
                            <option value="">-select-</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date of Confirmation/ Contract End Date</label>
                        <input type="date" name="confirmation_date" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Job Category <span class="req">*</span></label>
                        <select name="job_category" class="highlight">
                            <option value="">-select-</option>
                            <option value="Technical">Technical</option>
                            <option value="Administrative">Administrative</option>
                            <option value="Operational">Operational</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Type of Labor Cost</label>
                        <select name="labor_cost_type">
                            <option value="">-select-</option>
                            <option value="Direct">Direct</option>
                            <option value="Indirect">Indirect</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contract Period (Months) <span class="req">*</span></label>
                        <input type="number" name="contract_period_months" class="highlight">
                    </div>
                    <div class="form-group">
                        <label>Probation Period (Months) <span class="req">*</span></label>
                        <input type="number" name="probation_period_months" class="highlight">
                    </div>
                </div>
                <div class="form-group full" style="margin-top:14px;">
                    <label>Comment</label>
                    <textarea name="job_comment"></textarea>
                </div>
                <div class="form-group full" style="margin-top:12px;">
                    <label>Job Specification</label>
                    <textarea name="job_specification"></textarea>
                </div>
                <div class="form-group full" style="margin-top:12px;">
                    <label>Job Description</label>
                    <textarea name="job_description"></textarea>
                </div>
            </div>
        </div>

        <!-- ========== 5. Attendance Rules ========== -->
        <div class="accordion">
            <div class="acc-header blue" onclick="toggleAcc(this)">
                <i class="fas fa-calendar-check"></i> Attendance Rules
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="acc-body">
                <div class="form-group" style="max-width:320px;margin-bottom:12px;">
                    <label>Attendance Group</label>
                    <select name="attendance_group">
                        <option value="">-select-</option>
                        <option value="General">General</option>
                        <option value="Shift A">Shift A</option>
                        <option value="Shift B">Shift B</option>
                    </select>
                </div>
                <div class="form-grid three" style="margin-bottom:14px;">
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="from_date">
                    </div>
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="to_date">
                    </div>
                    <div class="form-group">
                        <label>Is Permanent</label>
                        <div style="margin-top:6px;"><input type="checkbox" name="is_permanent" value="1"></div>
                    </div>
                </div>
                <div class="form-group" style="max-width:320px;margin-bottom:10px;">
                    <label>Employee Work Schedule</label>
                    <select name="work_schedule">
                        <option value="">-select-</option>
                        <option value="Standard 8-5">Standard 8-5</option>
                        <option value="Shift Based">Shift Based</option>
                    </select>
                </div>

                <div class="form-grid" style="margin-top:20px;max-width:500px;">
                    <div class="form-group">
                        <label>Check By TimeZone</label>
                        <input type="hidden" name="check_by_timezone" id="check_by_timezone" value="0">
                        <div class="toggle-row"><div class="toggle red" onclick="toggleSwitch(this, 'check_by_timezone')"></div></div>
                    </div>
                    <div class="form-group">
                        <label>Allow Temporary Shifts</label>
                        <input type="hidden" name="allow_temporary_shifts" id="allow_temporary_shifts" value="0">
                        <div class="toggle-row"><div class="toggle red" onclick="toggleSwitch(this, 'allow_temporary_shifts')"></div></div>
                    </div>
                    <div class="form-group">
                        <label>CheckIn IsMust</label>
                        <input type="hidden" name="checkin_is_must" id="checkin_is_must" value="0">
                        <div class="toggle-row"><div class="toggle red" onclick="toggleSwitch(this, 'checkin_is_must')"></div></div>
                    </div>
                    <div class="form-group">
                        <label>Allow Flexy Attendance</label>
                        <input type="hidden" name="allow_flexy_attendance" id="allow_flexy_attendance" value="0">
                        <div class="toggle-row"><div class="toggle red" onclick="toggleSwitch(this, 'allow_flexy_attendance')"></div></div>
                    </div>
                    <div class="form-group">
                        <label>CheckOut IsMust</label>
                        <input type="hidden" name="checkout_is_must" id="checkout_is_must" value="0">
                        <div class="toggle-row"><div class="toggle red" onclick="toggleSwitch(this, 'checkout_is_must')"></div></div>
                    </div>
                    <div class="form-group">
                        <label>OT Applicable</label>
                        <input type="hidden" name="ot_applicable" id="ot_applicable" value="0">
                        <div class="toggle-row"><div class="toggle red" onclick="toggleSwitch(this, 'ot_applicable')"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== 6. Reporting Hierarchy ========== -->
        <div class="accordion">
            <div class="acc-header" onclick="toggleAcc(this)">
                <i class="fas fa-sitemap"></i> Reporting Hierarchy
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            <div class="acc-body">
                <p style="color:#64748b;font-size:13px;">Reporting hierarchy configuration can be added here later.</p>
            </div>
        </div>

    </form>
    <!-- ===================== FORM END ===================== -->

</main>

<script>
    function toggleAcc(header) {
        header.classList.toggle('open');
        const body = header.nextElementSibling;
        body.classList.toggle('open');
    }

    function previewPhoto(input) {
        const preview = document.getElementById('photoPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.innerHTML = `<img src="${e.target.result}" alt="Photo">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleSwitch(el, inputId) {
        el.classList.toggle('on');
        document.getElementById(inputId).value = el.classList.contains('on') ? '1' : '0';
    }

    function setToggle(id, value) {
        const input = document.getElementById(id);
        if (!input) return;
        input.value = value == 1 ? "1" : "0";
        const toggle = input.parentElement.querySelector(".toggle");
        if (toggle) {
            if (value == 1) toggle.classList.add("on");
            else toggle.classList.remove("on");
        }
    }

    function searchEmployee() {
        const empNo = document.getElementById("search_emp_no").value.trim();
        if (!empNo) {
            alert("Please enter Employee Number");
            return;
        }

        fetch("search_employee.php?emp_no=" + encodeURIComponent(empNo))
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || "Employee not found");
                    return;
                }

                const e = data.employee || {};
                const o = data.official || {};
                const a = data.address || {};
                const j = data.job || {};
                const at = data.attendance || {};

                const set = (name, value) => {
                    const el = document.querySelector('[name="' + name + '"]');
                    if (el && value !== null && value !== undefined) el.value = value;
                };

                // Personal
                set("emp_no", e.emp_no);
                set("gender", e.gender);
                set("title", e.title);
                set("initials", e.initials);
                set("denoted_by_initial", e.denoted_by_initial);
                set("first_name", e.first_name);
                set("middle_name", e.middle_name);
                set("last_name", e.last_name);
                set("name_with_initials", e.name_with_initials);
                set("full_name", e.full_name);
                set("office_use_name", e.office_use_name);
                set("language", e.language);
                set("passport_no", e.passport_no);
                set("marital_status", e.marital_status);
                set("date_of_birth", e.date_of_birth);
                set("nationality", e.nationality);
                set("age", e.age);
                set("race", e.race);
                set("birth_place", e.birth_place);
                set("religion", e.religion);
                set("nic", e.nic);
                set("blood_group", e.blood_group);
                set("employee_id_issue_date", e.employee_id_issue_date);
                set("distance_from_work", e.distance_from_work);

                if (e.photo) {
                    document.getElementById("photoPreview").innerHTML = '<img src="' + e.photo + '" alt="Photo">';
                }

                // Official
                set("employee_office_id", o.employee_office_id);
                set("tax_payee_no", o.tax_payee_no);
                set("company_name", o.company_name);
                set("proximity_card_no", o.proximity_card_no);
                set("job_source", o.job_source);
                set("division_name", o.division_name);
                set("attendance_account_no", o.attendance_account_no);
                set("manpower_company", o.manpower_company);
                set("sub_division_name", o.sub_division_name);
                set("department_name", o.department_name);
                set("ppf_epf_no", o.ppf_epf_no);
                set("salary_period", o.salary_period);
                set("sub_department_name", o.sub_department_name);
                set("employee_fund", o.employee_fund);
                set("basic_salary", o.basic_salary);
                set("section_name", o.section_name);
                set("etf_no", o.etf_no);
                set("statute_name", o.statute_name);
                set("sub_section_name", o.sub_section_name);
                set("next_salary_increment_date", o.next_salary_increment_date);
                set("base_customer_site", o.base_customer_site);
                set("erp_code", o.erp_code);

                setToggle("is_active_employee", o.is_active_employee);
                setToggle("salary_process", o.salary_process);
                setToggle("is_tax_payee", o.is_tax_payee);

                // Address
                set("address_no", a.address_no);
                set("contact_type", a.contact_type);
                set("postal_code", a.postal_code);
                set("work_tel", a.work_tel);
                set("city", a.city);
                set("country", a.country);
                set("office_email", a.office_email);
                set("district", a.district);
                set("tel_no", a.tel_no);
                set("private_email", a.private_email);
                set("province", a.province);
                set("mobile_no", a.mobile_no);
                set("comments", a.comments);

                // Job
                set("designation", j.designation);
                set("resign_date", j.resign_date);
                set("reporting_supervisor", j.reporting_supervisor);
                set("join_date", j.join_date);
                set("leaving_type", j.leaving_type);
                set("job_level", j.job_level);
                set("appointment_date", j.appointment_date);
                set("retirement_date", j.retirement_date);
                set("rejoin_date", j.rejoin_date);
                set("work_category", j.work_category);
                set("grade", j.grade);
                set("confirmation_date", j.confirmation_date);
                set("job_category", j.job_category);
                set("labor_cost_type", j.labor_cost_type);
                set("contract_period_months", j.contract_period_months);
                set("probation_period_months", j.probation_period_months);
                set("job_comment", j.comment);
                set("job_specification", j.job_specification);
                set("job_description", j.job_description);

                // Attendance
                set("attendance_group", at.attendance_group);
                set("from_date", at.from_date);
                set("to_date", at.to_date);
                set("work_schedule", at.work_schedule);
                if (at.is_permanent == 1) {
                    const cb = document.querySelector('[name="is_permanent"]');
                    if (cb) cb.checked = true;
                }
                setToggle("check_by_timezone", at.check_by_timezone);
                setToggle("allow_temporary_shifts", at.allow_temporary_shifts);
                setToggle("checkin_is_must", at.checkin_is_must);
                setToggle("allow_flexy_attendance", at.allow_flexy_attendance);
                setToggle("checkout_is_must", at.checkout_is_must);
                setToggle("ot_applicable", at.ot_applicable);

                alert("Employee details loaded successfully!");
            })
            .catch(err => {
                console.error(err);
                alert("Error searching employee");
            });
    }
</script>
</body>
</html>
