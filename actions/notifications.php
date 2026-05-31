<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/notifications.php');
    exit;
}

validate_csrf();

$user_id = $_SESSION['user_id'];

if (isset($_POST['delete_notification'])) {
    $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?")
       ->execute([(int) $_POST['notif_id'], $user_id]);
}

if (isset($_POST['clear_all'])) {
    $db->prepare("DELETE FROM notifications WHERE user_id = ?")
       ->execute([$user_id]);
}

header('Location: ../pages/notifications.php');
exit;
