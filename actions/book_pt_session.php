<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/trainers.php');
    exit;
}

validate_csrf();

$user_id = $_SESSION['user_id'];

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'member') {
    $user_plan = get_user_plan($db, $user_id);
    if (!$user_plan['can_book_pt']) {
        $_SESSION['flash_error'] = 'Personal training requires the Pro plan or higher. Upgrade your plan to book PT sessions.';
        header('Location: ../pages/plans.php');
        exit;
    }
}

$trainer_id = (int) ($_POST['trainer_id'] ?? 0);
$b_date = $_POST['booking_date'] ?? '';
$t_slot = $_POST['time_slot'] ?? '';

if (!$trainer_id || !$b_date || !$t_slot) {
    $_SESSION['flash_error'] = 'Invalid booking data.';
    header('Location: ../pages/trainers.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM trainers WHERE id = ?");
$stmt->execute([$trainer_id]);
$trainer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trainer) {
    $_SESSION['flash_error'] = 'Trainer not found.';
    header('Location: ../pages/trainers.php');
    exit;
}

$db->exec("CREATE TABLE IF NOT EXISTS pt_bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, trainer_id INTEGER NOT NULL,
    booking_date TEXT NOT NULL, time_slot TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (trainer_id) REFERENCES trainers(id),
    UNIQUE(trainer_id, booking_date, time_slot)
);");

$day_of_week = date('l', strtotime($b_date));
$is_valid = true;
$error_message = "";

if ($trainer_id == 1) {
    $allowed_days = ['Tuesday', 'Thursday'];
    $allowed_slots = ['09:00 - 10:00', '11:00 - 12:00'];
    if (!in_array($day_of_week, $allowed_days) || !in_array($t_slot, $allowed_slots)) {
        $is_valid = false;
        $error_message = "Invalid date/time! Ana is only available on Tuesdays and Thursdays between 09:00 - 12:00.";
    }
} else if ($trainer_id == 2) {
    $allowed_days = ['Monday', 'Wednesday'];
    $allowed_slots = ['16:00 - 17:00', '18:00 - 19:00'];
    if (!in_array($day_of_week, $allowed_days) || !in_array($t_slot, $allowed_slots)) {
        $is_valid = false;
        $error_message = "Invalid date/time! Bruno is only available on Mondays and Wednesdays between 16:00 - 19:00.";
    }
}

if (!$is_valid) {
    $_SESSION['flash_error'] = $error_message;
    header('Location: ../pages/trainers.php?id=' . $trainer_id);
    exit;
}

try {
    $stmt = $db->prepare("INSERT INTO pt_bookings (user_id, trainer_id, booking_date, time_slot) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $trainer_id, $b_date, $t_slot]);
    $_SESSION['flash_success'] = "PT Session booked successfully!";
    header('Location: ../pages/trainers.php?id=' . $trainer_id);
    exit;
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "Sorry, this time slot is already taken by another member!";
    header('Location: ../pages/trainers.php?id=' . $trainer_id);
    exit;
}
