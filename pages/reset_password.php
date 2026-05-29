<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../templates/reset_password.tpl.php';

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
    validate_csrf();
    
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

// Renderização limpa através da função do template
drawResetPasswordPage($page_title, $error, $success, $reset, $token);
?>