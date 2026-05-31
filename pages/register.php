<?php
session_start();
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../templates/register.tpl.php';

$page_title = 'Register';
$error   = '';
$success = '';

$extra_css = '../css/login.css';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$flash_error = $_SESSION['flash_error'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

if ($flash_error) $error = $flash_error;
if ($flash_success) $success = $flash_success;

drawRegisterHeader('Register'); 
drawRegisterForm($error, $success);
drawRegisterFooter();
?>
