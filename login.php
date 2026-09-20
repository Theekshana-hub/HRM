<?php
session_start();

// Already logged in → go to dashboard
if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Hardcoded users
$users = [
    'admin'   => ['password' => 'admin123', 'name' => 'Administrator', 'role' => 'Admin'],
    'shakila' => ['password' => '1234',     'name' => 'Shakila',       'role' => 'HR'],
    'hr'      => ['password' => 'hr123',    'name' => 'HR Manager',    'role' => 'HR'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username']  = $username;
        $_SESSION['fullname']    = $users[$username]['name'];
        $_SESSION['role']      = $users[$username]['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | SMART HRIS™</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root {
    --bg: #0f172a;
    --card: #ffffff;
    --accent: #3b82f6;
    --accent-dark: #2563eb;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Inter', system-ui, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1e40af 100%);
    padding: 20px;
}
.login-wrap {
    width: 100%;
    max-width: 420px;
}
.brand {
    text-align: center;
    margin-bottom: 28px;
    color: #fff;
}
.brand-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    border-radius: 14px;
    display: inline-grid;
    place-items: center;
    font-size: 22px;
    color: #fff;
    margin-bottom: 12px;
    box-shadow: 0 8px 24px rgba(59,130,246,.35);
}
.brand h1 {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.3px;
}
.brand h1 span {
    font-size: 11px;
    opacity: .7;
    font-weight: 500;
}
.brand p {
    font-size: 13px;
    color: #94a3b8;
    margin-top: 6px;
}
.card {
    background: var(--card);
    border-radius: 16px;
    padding: 32px 28px;
    box-shadow: 0 20px 50px rgba(0,0,0,.25);
}
.card h2 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
}
.card .sub {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 22px;
}
.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block;
    font-size: 12.5px;
    font-weight: 500;
    color: #334155;
    margin-bottom: 6px;
}
.input-wrap {
    position: relative;
}
.input-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
}
.input-wrap input {
    width: 100%;
    height: 44px;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0 14px 0 40px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: border .2s, box-shadow .2s;
    background: #f8fafc;
}
.input-wrap input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    background: #fff;
}
.btn-login {
    width: 100%;
    height: 46px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: filter .2s, transform .15s;
}
.btn-login:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}
.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.demo {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    font-size: 12px;
    color: var(--muted);
}
.demo strong {
    color: #334155;
}
.demo code {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11.5px;
    color: #1e40af;
}
.footer {
    text-align: center;
    margin-top: 20px;
    font-size: 12px;
    color: #64748b;
}
</style>
</head>
<body>
<div class="login-wrap">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-th-large"></i></div>
        <h1>SMART HRIS <span>™</span></h1>
        <p>Human Resource Information System</p>
    </div>

    <div class="card">
        <h2>Sign in</h2>
        <p class="sub">Enter your credentials to access the HR panel</p>

        <?php if ($error): ?>
        <div class="error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Enter username" required autofocus
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="demo">
            <strong>Demo accounts:</strong><br>
            <code>admin</code> / <code>admin123</code> &nbsp;·&nbsp;
            <code>shakila</code> / <code>1234</code> &nbsp;·&nbsp;
            <code>hr</code> / <code>hr123</code>
        </div>
    </div>

    <p class="footer">SMART HRIS™ &copy; 2026</p>
</div>
</body>
</html>