<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../templates/admin_users.tpl.php';

require_admin();

$current_page = 'admin';
$extra_css    = '../css/admin.css';
$page_title   = 'Manage Users';

$ALLOWED_ROLES = ['member', 'trainer', 'admin'];

function toggle_user_active(PDO $db, int $user_id, int $current_user_id): ?string
{
    if ($user_id === $current_user_id) {
        return 'You cannot deactivate your own account.';
    }

    $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return 'User not found.';

    if (str_starts_with($user['email'], '!deactivated_')) {
        $clean_email = substr($user['email'], strlen('!deactivated_'));
        $db->prepare("UPDATE users SET email = ? WHERE id = ?")->execute([$clean_email, $user_id]);
        return null;
    } else {
        $db->prepare("UPDATE users SET email = ? WHERE id = ?")->execute(['!deactivated_' . $user['email'], $user_id]);
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

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

                // Se a role for trainer, registar também na tabela correspondente
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
        $err = toggle_user_active($db, $target_id, (int)$_SESSION['user_id']);
        if ($err) {
            $_SESSION['flash_error'] = $err;
        } else {
            $_SESSION['flash_success'] = 'User status changed successfully.';
        }
    }

    header('Location: admin_users.php');
    exit;
}

$users = $db->query("SELECT id, first_name, last_name, email, role FROM users ORDER BY role, first_name")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';

// Invocação da renderização isolada do template
drawAdminUsersPage($users, $ALLOWED_ROLES);

require_once __DIR__ . '/../templates/footer.php';
?>

<script>
    function showModal(id) { 
        document.getElementById(id).classList.add('show'); 
    }
    
    function hideModal(id) { 
        document.getElementById(id).classList.remove('show'); 
    }
</script>