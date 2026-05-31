<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/admin_users.php');
    exit;
}

validate_csrf();

$ALLOWED_ROLES = ['member', 'trainer', 'admin'];

if (isset($_POST['add_user'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $role       = $_POST['role'] ?? 'member';

    if (!in_array($role, $ALLOWED_ROLES, true)) {
        $role = 'member';
    }

    if ($first_name && $last_name && $email && strlen($password) >= 6) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $hash, $role]);

            if ($role === 'trainer') {
                $stmtTrainer = $db->prepare("INSERT INTO trainers (name, specialization, bio) VALUES (?, 'Generalist', 'No bio provided yet.')");
                $stmtTrainer->execute([$first_name . ' ' . $last_name]);
            }

            $_SESSION['flash_success'] = 'User registered successfully!';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Email already exists or database failure.';
        }
    } else {
        $_SESSION['flash_error'] = 'Invalid fields or password too short (min 6 chars).';
    }
}

if (isset($_POST['toggle_status'])) {
    $target_id = (int) ($_POST['user_id'] ?? 0);
    $current_user_id = (int)$_SESSION['user_id'];

    if ($target_id === $current_user_id) {
        $_SESSION['flash_error'] = 'You cannot deactivate your own account.';
    } else {
        $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$target_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
        } else {
            if (str_starts_with($user['email'], '!deactivated_')) {
                $clean_email = substr($user['email'], strlen('!deactivated_'));
                $db->prepare("UPDATE users SET email = ? WHERE id = ?")->execute([$clean_email, $target_id]);
            } else {
                $db->prepare("UPDATE users SET email = ? WHERE id = ?")->execute(['!deactivated_' . $user['email'], $target_id]);
            }
            $_SESSION['flash_success'] = 'User status changed successfully.';
        }
    }
}

header('Location: ../pages/admin_users.php');
exit;
