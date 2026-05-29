<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../templates/trainer_dashboard.tpl.php';

require_trainer();

$current_page = 'trainer_dashboard';
$extra_css    = '../css/admin.css';
$extra_js     = '../javascript/trainer_dashboard.js';
$page_title   = 'Trainer Dashboard';

$trainer_name = $_SESSION['user_name'] ?? '';

$stmt = $db->prepare("SELECT * FROM trainers WHERE name = ?");
$stmt->execute([$trainer_name]);
$trainer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trainer) {
    require_once __DIR__ . '/../templates/header.php';
    drawTrainerNotFoundError($trainer_name);
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}

$trainer_id      = $trainer['id'];
$specialties_arr = explode(',', $trainer['specialties']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $bio         = trim($_POST['bio'] ?? '');
    $specialties = trim($_POST['specialties'] ?? '');
    $db->prepare("UPDATE trainers SET bio = ?, specialties = ? WHERE id = ?")->execute([$bio, $specialties, $trainer_id]);
    $_SESSION['flash_success'] = 'Profile updated successfully.';
    header('Location: trainer_dashboard.php');
    exit;
}

$stmt = $db->prepare("
    SELECT c.* FROM classes c
    WHERE c.trainer_id = ?
    ORDER BY
        CASE c.day_of_week
            WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2
            WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4
            WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
            WHEN 'Sunday' THEN 7
        END, c.time_start
");
$stmt->execute([$trainer_id]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rosters = [];
foreach ($classes as $class) {
    $stmt = $db->prepare("
        SELECT u.first_name, u.last_name, u.email, b.created_at AS booked_date
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.class_id = ?
        ORDER BY b.created_at
    ");
    $stmt->execute([$class['id']]);
    $rosters[$class['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $db->prepare("
    SELECT pt.id, pt.booking_date, pt.time_slot, u.first_name, u.last_name, u.email
    FROM pt_bookings pt
    JOIN users u ON pt.user_id = u.id
    WHERE pt.trainer_id = ?
    ORDER BY pt.booking_date DESC, pt.time_slot ASC
");
$stmt->execute([$trainer_id]);
$pt_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';
drawTrainerDashboard($trainer, $specialties_arr, $pt_sessions, $classes, $rosters);
require_once __DIR__ . '/../templates/footer.php';
?>