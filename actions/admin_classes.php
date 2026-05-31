<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/admin_classes.php');
    exit;
}

validate_csrf();

function gather_class_data(): ?array
{
    $title      = trim($_POST['title'] ?? '');
    $day        = $_POST['day_of_week'] ?? '';
    $start      = $_POST['time_start'] ?? '';
    $end        = $_POST['time_end'] ?? '';
    $location   = trim($_POST['location'] ?? '');
    $trainer_id = (int) ($_POST['trainer_id'] ?? 0);
    $spots      = (int) ($_POST['total_spots'] ?? 0);
    $type       = trim($_POST['type'] ?? '');

    return ($title && $day && $start && $end && $location && $trainer_id && $spots > 0 && $type)
        ? [$title, $day, $start, $end, $location, $trainer_id, $spots, $type]
        : null;
}

if (isset($_POST['create_class'])) {
    $data = gather_class_data();
    if ($data) {
        [$title, $day, $start, $end, $location, $trainer_id, $spots, $type] = $data;
        $stmt = $db->prepare(
            "INSERT INTO classes (title, day_of_week, time_start, time_end, location, trainer_id, total_spots, type)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$title, $day, $start, $end, $location, $trainer_id, $spots, $type]);
        $_SESSION['flash_success'] = 'Class created successfully.';
    } else {
        $_SESSION['flash_error'] = 'Please fill in all fields.';
    }
}

if (isset($_POST['edit_class'])) {
    $class_id = (int) ($_POST['class_id'] ?? 0);
    $data     = gather_class_data();
    if ($class_id && $data) {
        [$title, $day, $start, $end, $location, $trainer_id, $spots, $type] = $data;
        $stmt = $db->prepare(
            "UPDATE classes SET title=?, day_of_week=?, time_start=?, time_end=?,
                location=?, trainer_id=?, total_spots=?, type=? WHERE id=?"
        );
        $stmt->execute([$title, $day, $start, $end, $location, $trainer_id, $spots, $type, $class_id]);
        $_SESSION['flash_success'] = 'Class updated successfully.';
    } else {
        $_SESSION['flash_error'] = 'Please fill in all fields.';
    }
}

if (isset($_POST['delete_class'])) {
    $class_id = (int) ($_POST['class_id'] ?? 0);
    if ($class_id) {
        try {
            $db->beginTransaction();
            $db->prepare("DELETE FROM bookings WHERE class_id = ?")->execute([$class_id]);
            $db->prepare("DELETE FROM classes   WHERE id       = ?")->execute([$class_id]);
            $db->commit();
            $_SESSION['flash_success'] = 'Class deleted successfully.';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Something went wrong while deleting the class.';
        }
    }
}

header('Location: ../pages/admin_classes.php');
exit;
