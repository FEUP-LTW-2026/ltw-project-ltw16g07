<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_trainer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/trainer_dashboard.php');
    exit;
}

validate_csrf();

$trainer_name = $_SESSION['user_name'] ?? '';

$stmt = $db->prepare("SELECT * FROM trainers WHERE name = ?");
$stmt->execute([$trainer_name]);
$trainer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trainer) {
    $_SESSION['flash_error'] = 'Trainer profile not found.';
    header('Location: ../pages/trainer_dashboard.php');
    exit;
}

$bio         = trim($_POST['bio'] ?? '');
$specialties = trim($_POST['specialties'] ?? '');
$db->prepare("UPDATE trainers SET bio = ?, specialties = ? WHERE id = ?")->execute([$bio, $specialties, $trainer['id']]);
$_SESSION['flash_success'] = 'Profile updated successfully.';
header('Location: ../pages/trainer_dashboard.php');
exit;
