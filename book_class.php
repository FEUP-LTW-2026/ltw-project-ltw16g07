<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please login to book a class.';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['class_id'])) {
    header('Location: classes.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$class_id = (int) $_POST['class_id'];

$stmt = $db->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->execute([$class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    $_SESSION['flash_error'] = 'Class not found.';
    header('Location: classes.php');
    exit;
}

$stmt = $db->prepare("SELECT id FROM bookings WHERE user_id = ? AND class_id = ?");
$stmt->execute([$user_id, $class_id]);
if ($stmt->fetch()) {
    $_SESSION['flash_error'] = 'You are already enrolled in this class.';
    header('Location: classes.php');
    exit;
}

// Prevent trainers from enrolling in their own classes
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'trainer') {
    $stmt = $db->prepare("SELECT id FROM trainers WHERE name = ?");
    $stmt->execute([$_SESSION['user_name']]);
    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($trainer && (int) $trainer['id'] === (int) $class['trainer_id']) {
        $_SESSION['flash_error'] = 'You cannot enroll in your own class.';
        header('Location: classes.php');
        exit;
    }
}

$remaining = $class['total_spots'] - $class['booked_spots'];
if ($remaining <= 0) {
    $_SESSION['flash_error'] = 'This class is full. Please try the waitlist.';
    header('Location: classes.php');
    exit;
}

$db->beginTransaction();
try {
    $stmt = $db->prepare("INSERT INTO bookings (user_id, class_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $class_id]);

    $stmt = $db->prepare("UPDATE classes SET booked_spots = booked_spots + 1 WHERE id = ?");
    $stmt->execute([$class_id]);

    $db->commit();
    $_SESSION['flash_success'] = 'Successfully enrolled in ' . $class['title'] . '!';
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['flash_error'] = 'Something went wrong. Please try again.';
}

header('Location: classes.php');
exit;
