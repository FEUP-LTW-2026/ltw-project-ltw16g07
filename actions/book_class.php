<?php
session_start();
require_once __DIR__ . '/../config/database.php';



$db->exec("CREATE TABLE IF NOT EXISTS waitlists (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    UNIQUE(user_id, class_id)
);");



if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please login to book a class.';
    header('Location: /../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['class_id'])) {
    header('Location: /../pages/classes.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$class_id = (int) $_POST['class_id'];

$action_type = $_POST['action_type'] ?? 'enroll';

$stmt = $db->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->execute([$class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    $_SESSION['flash_error'] = 'Class not found.';
    header('Location: /../pages/classes.php');
    exit;
}

$stmt = $db->prepare("SELECT id FROM bookings WHERE user_id = ? AND class_id = ?");
$stmt->execute([$user_id, $class_id]);
if ($stmt->fetch()) {
    $_SESSION['flash_error'] = 'You are already enrolled in this class.';
    header('Location: /../pages/classes.php');
    exit;
}

$stmt = $db->prepare("SELECT id FROM waitlists WHERE user_id = ? AND class_id = ?");
$stmt->execute([$user_id, $class_id]);
if ($stmt->fetch()) {
    $_SESSION['flash_error'] = 'You are already on the waitlist for this class.';
    header('Location: /../pages/classes.php');
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

if ($action_type === 'waitlist') {
    try {
        $stmt = $db->prepare("INSERT INTO waitlists (user_id, class_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $class_id]);

        $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'booking', ?)")
        ->execute([$user_id, "You joined the waitlist for \"{$class['title']}\" on {$class['day_of_week']} at {$class['time_start']}."]);

        $_SESSION['flash_success'] = 'You have been successfully added to the waitlist for ' . $class['title'] . '!';
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Something went wrong while joining the waitlist.';
    }
    header('Location: /../pages/classes.php');
    exit;
}

$remaining = $class['total_spots'] - $class['booked_spots'];
if ($remaining <= 0) {
    $_SESSION['flash_error'] = 'This class is full. Please try the waitlist.';
    header('Location: /../pages/classes.php');
    exit;
}

$db->beginTransaction();
try {
    $stmt = $db->prepare("INSERT INTO bookings (user_id, class_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $class_id]);

    $stmt = $db->prepare("UPDATE classes SET booked_spots = booked_spots + 1 WHERE id = ?");
    $stmt->execute([$class_id]);

    $db->commit();

    $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'booking', ?)")
   ->execute([$user_id, "You enrolled in \"{$class['title']}\" on {$class['day_of_week']} at {$class['time_start']}."]);


    $_SESSION['flash_success'] = 'Successfully enrolled in ' . $class['title'] . '!';
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['flash_error'] = 'Something went wrong. Please try again.';
}

header('Location: /../pages/classes.php');
exit;
