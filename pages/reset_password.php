<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$page_title = 'Reset Password';

$error = '';
$success = '';

$token = $_GET['token'] ?? '';

if ($token === '') {
    header('Location: forgot_password.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > datetime('now')");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    $error = 'Invalid or expired reset link.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $reset['email']]);

        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$reset['email']]);

        $success = 'Password reset successfully! <a href="../pages/login.php" class="success-link">Login</a>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>FitHub - <?= $page_title ?></title>
    <link rel="icon" type="image/png" href="https://i.imgur.com/WT1OtgQ.png">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-box">
        <a href="../index.php"><h2>FitHub</h2></a>
        <p class="subtitle">New password</p>

        <?php if ($error !== ''): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <p class="success-msg"><?= $success ?></p>
        <?php elseif ($reset): ?>
            <form action="reset_password.php?token=<?= urlencode($token) ?>" method="post">
                <input type="password" name="password" placeholder="New password (min 6 characters)" required>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                <input type="submit" value="Reset Password">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
