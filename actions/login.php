<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

validate_csrf();

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['flash_error'] = 'Please fill in all fields.';
    header('Location: ../pages/login.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_initials'] = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
    $_SESSION['user_role'] = $user['role'] ?? 'member';
    $_SESSION['user_pfp'] = $user['profile_pic'];

    header('Location: ../pages/dashboard.php');
    exit;
} else {
    $_SESSION['flash_error'] = 'Invalid email or password.';
    header('Location: ../pages/login.php');
    exit;
}
