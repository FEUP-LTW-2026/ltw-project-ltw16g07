<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/equipment.php');
    exit;
}

validate_csrf();

$allowed = ['available', 'full', 'maintenance'];
if (isset($_POST['equipment_id'], $_POST['status']) && in_array($_POST['status'], $allowed)) {
    $stmt = $db->prepare("UPDATE equipment SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['equipment_id']]);
}

header('Location: ../pages/equipment.php');
exit;
