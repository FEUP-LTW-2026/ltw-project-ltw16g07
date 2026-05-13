<?php
session_start();
require_once __DIR__ . '/config/database.php';

$page_title = 'Register';
$extra_css = 'login.css';

$error = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($first_name === '' || $last_name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("
                INSERT INTO users (first_name, last_name, email, password) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
            
            $success = 'Account created successfully! You can now login.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>FitHub - <?= $page_title ?></title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-box">
        <a href="index.php"><h2>FitHub</h2></a>
        <p class="subtitle">Create your account</p>
        
        <?php if ($error !== ''): ?>
            <p style="color: #ef4444; font-size: 0.9em;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success !== ''): ?>
            <p style="color: #8cff9a; font-size: 0.9em;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form action="register.php" method="post">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password (min 6 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="submit" value="Create Account">
        </form>
        <p style="margin-top: 1em; color: #656565; font-size: 0.85em;">
            Already have an account? <a href="login.php" style="color: #ccff00;">Login</a>
        </p>
    </div>
</body>
</html>
