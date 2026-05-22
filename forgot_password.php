<?php
session_start();
require_once __DIR__ . '/config/database.php';

$page_title = 'Forgot Password';

$error = '';
$success = '';

$stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='password_resets'");
if (!$stmt->fetch()) {
    $db->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        token TEXT NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Please enter your email.';
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);

            $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires]);

            $reset_link = "//{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['SCRIPT_NAME']) . "/reset_password.php?token=$token";
            $success = "Click <a href=\"$reset_link\" class=\"success-link\">here</a> to reset your password. (Link valid for 1 hour)";
        } else {
            $error = 'No account found with that email.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>FitHub - <?= $page_title ?></title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-box">
        <a href="index.php"><h2>FitHub</h2></a>
        <p class="subtitle">Reset your password</p>

        <?php if ($error !== ''): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success !== ''): ?>
            <p class="success-msg"><?= $success ?></p>
        <?php else: ?>
            <form action="forgot_password.php" method="post">
                <input type="email" name="email" placeholder="Your email" required>
                <input type="submit" value="Send Reset Link">
            </form>
        <?php endif; ?>
        <p class="footer-text">
            <a href="login.php">Back to Login</a>
        </p>
    </div>
</body>
</html>
