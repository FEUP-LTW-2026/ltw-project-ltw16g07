<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/dashboard.php');
    exit;
}

validate_csrf();

$user_id = $_SESSION['user_id'];

$weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
$body_fat = filter_input(INPUT_POST, 'body_fat', FILTER_VALIDATE_FLOAT);
$body_fat = $body_fat > 0 ? $body_fat : null;

if ($weight && $weight > 0) {
    try {
        $stmt = $db->prepare("INSERT INTO member_progress (user_id, weight, body_fat) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $weight, $body_fat]);
        $_SESSION['flash_success'] = 'Progress tracked successfully!';
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Failed to record progress: ' . $e->getMessage();
    }
} else {
    $_SESSION['flash_error'] = 'Please enter a valid weight.';
}

header('Location: ../pages/dashboard.php');
exit;
