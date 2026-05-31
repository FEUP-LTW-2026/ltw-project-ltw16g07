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

$flash_error = $_SESSION['flash_error'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
if ($flash_error) $error = $flash_error;
if ($flash_success) $success = $flash_success;

drawResetPasswordPage($page_title, $error, $success, $reset, $token);
?>