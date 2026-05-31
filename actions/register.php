<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/register.php');
    exit;
}

validate_csrf();

$first_name       = trim($_POST['first_name']       ?? '');
$last_name        = trim($_POST['last_name']        ?? '');
$email            = trim($_POST['email']            ?? '');
$password         =      $_POST['password']         ?? '';
$confirm_password =      $_POST['confirm_password'] ?? '';

if ($first_name === '' || $last_name === '' || $email === '' || $password === '') {
    $_SESSION['flash_error'] = 'Please fill in all fields.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_error'] = 'Please enter a valid email address.';
} elseif (strlen($password) < 6) {
    $_SESSION['flash_error'] = 'Password must be at least 6 characters.';
} elseif ($password !== $confirm_password) {
    $_SESSION['flash_error'] = 'Passwords do not match.';
} else {
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['flash_error'] = 'This email is already registered.';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
        $_SESSION['flash_success'] = 'Account created successfully! You can now login.';
    }
}

header('Location: ../pages/register.php');
exit;
