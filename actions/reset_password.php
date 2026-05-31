<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

validate_csrf();

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($token === '') {
    header('Location: ../pages/login.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > datetime('now')");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    $_SESSION['flash_error'] = 'Invalid or expired reset link.';
    header('Location: ../pages/login.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['flash_error'] = 'Password must be at least 6 characters.';
    header('Location: ../pages/reset_password.php?token=' . urlencode($token));
    exit;
}

if ($password !== $confirm) {
    $_SESSION['flash_error'] = 'Passwords do not match.';
    header('Location: ../pages/reset_password.php?token=' . urlencode($token));
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->execute([$hashed, $reset['email']]);

$stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
$stmt->execute([$reset['email']]);

$_SESSION['flash_success'] = 'Password reset successfully!';
header('Location: ../pages/login.php');
exit;
