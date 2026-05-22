<?php
session_start();
require_once __DIR__ . '/config/database.php';

$current_page = 'login';
$page_title = 'Login';
$extra_css = 'login.css';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_initials'] = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
            $_SESSION['user_role'] = $user['role'] ?? 'member';
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
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
        <p class="subtitle">Welcome back</p>
        
        <?php if ($error !== ''): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
        <p class="footer-text">
            Don't have an account? <a href="register.php">Register</a>
        </p>
        <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
    </div>
</body>
</html>
