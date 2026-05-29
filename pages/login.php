<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../templates/login.tpl.php';

$current_page = 'login';
$page_title = 'Login';
$extra_css = '../css/login.css';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: ../pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    
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
            $_SESSION['user_pfp'] = $user['profile_pic'];
            
            header('Location: ../pages/dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// Como o login não usa o header.php comum (pois não tem o menu de navegação),
// chamamos a função do template que vai abrir o HTML e desenhar a página toda.
drawLoginPage($error, $page_title, $extra_css);