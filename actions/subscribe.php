<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/plans.php');
    exit;
}

validate_csrf();

$user_id = (int) $_SESSION['user_id'];
$plan_id = (int) ($_POST['plan_id'] ?? 0);

$stmt = $db->prepare("SELECT id FROM plans WHERE id = ?");
$stmt->execute([$plan_id]);
if (!$stmt->fetch()) {
    $_SESSION['flash_error'] = 'Invalid plan selected.';
    header('Location: ../pages/plans.php');
    exit;
}

$stmt = $db->prepare("UPDATE user_subscriptions SET plan_id = ?, start_date = datetime('now') WHERE user_id = ?");
$stmt->execute([$plan_id, $user_id]);

$stmt = $db->prepare("SELECT name FROM plans WHERE id = ?");
$stmt->execute([$plan_id]);
$plan_name = $stmt->fetchColumn();

$_SESSION['flash_success'] = "Successfully switched to the {$plan_name} plan!";
header('Location: ../pages/plans.php');
exit;
