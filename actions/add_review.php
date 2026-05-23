<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please login to leave a review.';
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['trainer_id'])) {
    header('Location: ../pages/trainers.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$trainer_id = (int) $_POST['trainer_id'];
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($rating < 1 || $rating > 5) {
    $_SESSION['flash_error'] = 'Please select a rating between 1 and 5.';
    header('Location: ../pages/trainers.php?id=' . $trainer_id);
    exit;
}

if ($comment === '') {
    $_SESSION['flash_error'] = 'Please write a comment.';
    header('Location: ../pages/trainers.php?id=' . $trainer_id);
    exit;
}

$stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND trainer_id = ?");
$stmt->execute([$user_id, $trainer_id]);
if ($stmt->fetch()) {
    $_SESSION['flash_error'] = 'You have already reviewed this trainer.';
    header('Location: ../pages/trainers.php?id=' . $trainer_id);
    exit;
}

$db->beginTransaction();
try {
    $stmt = $db->prepare("INSERT INTO reviews (user_id, trainer_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $trainer_id, $rating, $comment]);

    $stmt = $db->prepare("SELECT ROUND(AVG(rating), 1) as avg, COUNT(*) as count FROM reviews WHERE trainer_id = ?");
    $stmt->execute([$trainer_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("UPDATE trainers SET rating = ?, reviews_count = ? WHERE id = ?");
    $stmt->execute([$stats['avg'], $stats['count'], $trainer_id]);

    $db->commit();
    $_SESSION['flash_success'] = 'Review submitted!';
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['flash_error'] = 'Something went wrong. Please try again.';
}

header('Location: ../pages/trainers.php?id=' . $trainer_id);
exit;
