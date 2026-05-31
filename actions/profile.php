<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/profile.php');
    exit;
}

validate_csrf();

$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: ../pages/login.php');
    exit;
}

// Change password
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['flash_error'] = "The current password is incorrect.";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['flash_error'] = "The new password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['flash_error'] = "The new passwords do not match.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
        $_SESSION['flash_success'] = "Password updated successfully!";
    }
    header('Location: ../pages/profile.php');
    exit;
}

// Upload profile picture
if (isset($_POST['upload_pfp'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $newFileName = md5(time() . $user_id) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../uploads/pfp/';
            if (!is_dir($uploadFileDir)) mkdir($uploadFileDir, 0755, true);

            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                $dbRelativePath = '../uploads/pfp/' . $newFileName;
                $db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")->execute([$dbRelativePath, $user_id]);
                $_SESSION['user_pfp'] = $dbRelativePath;
                $_SESSION['flash_success'] = "Profile picture updated!";
            }
        }
    }
    header('Location: ../pages/profile.php');
    exit;
}

// Remove profile picture
if (isset($_POST['remove_pfp'])) {
    $db->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?")->execute([$user_id]);
    $_SESSION['user_pfp'] = null;
    $_SESSION['flash_success'] = "Profile picture removed.";
    header('Location: ../pages/profile.php');
    exit;
}

// Add metric
if (isset($_POST['add_metric'])) {
    $weight = floatval($_POST['weight'] ?? 0);
    $fat = !empty($_POST['body_fat']) ? floatval($_POST['body_fat']) : null;

    if ($weight > 0) {
        $stmt = $db->prepare("INSERT INTO member_progress (user_id, weight, body_fat) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $weight, $fat]);
        $_SESSION['flash_success'] = "Metrics logged successfully!";
    }
    header('Location: ../pages/profile.php');
    exit;
}

// Delete metric
if (isset($_POST['delete_metric'])) {
    $metric_id = (int)$_POST['metric_id'];
    $db->prepare("DELETE FROM member_progress WHERE id = ? AND user_id = ?")->execute([$metric_id, $user_id]);
    $_SESSION['flash_success'] = "Record deleted.";
    header('Location: ../pages/profile.php');
    exit;
}

// Cancel booking (from dashboard)
if (isset($_POST['cancel_booking'])) {
    $booking_id = (int) $_POST['cancel_booking'];
    $stmt = $db->prepare("SELECT class_id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?")->execute([$booking_id, $user_id]);
            $db->prepare("UPDATE classes SET booked_spots = booked_spots - 1 WHERE id = ?")->execute([$booking['class_id']]);
            $db->commit();
            $_SESSION['flash_success'] = "Booking cancelled successfully.";
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Something went wrong.";
        }
    }
    header('Location: ../pages/dashboard.php');
    exit;
}

header('Location: ../pages/profile.php');
exit;
