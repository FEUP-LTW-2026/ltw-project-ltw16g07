<?php
session_start();
require_once __DIR__ . '/config/database.php';

$current_page = 'login';
$page_title = 'Login';
$extra_css = 'login.css';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
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
            
            header('Location: index.php');
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
</head>
<body>
    <div class="login-box">
        <a href="index.php"><h2>FitHub</h2></a>
        <p class="subtitle">Bem-vindo de volta</p>
        
        <?php if ($error !== ''): ?>
            <p style="color: #ef4444; font-size: 0.9em;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Entrar">
        </form>
        <p style="margin-top: 1em; color: #656565; font-size: 0.85em;">
            Don't have an account? <a href="register.php" style="color: #ccff00;">Register</a>
        </p>
        <a href="">Recuperar Password</a> 
    </div>
</body>
</html>
