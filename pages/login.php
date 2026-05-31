<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../templates/login.tpl.php';

$current_page = 'login';
$page_title = 'Login';
$extra_css = '../css/login.css';

$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

if (isset($_SESSION['user_id'])) {
    header('Location: ../pages/dashboard.php');
    exit;
}

drawLoginPage($error, $page_title, $extra_css);