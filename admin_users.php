<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_admin();

$current_page = 'admin';
$extra_css    = 'admin.css';
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
    if (isset($_POST['change_role'], $_POST['user_id'], $_POST['new_role'])) {
        $user_id  = (int) $_POST['user_id'];
        $new_role = $_POST['new_role'];

        if (in_array($new_role, $ALLOWED_ROLES)) {
            if ($user_id === (int) $_SESSION['user_id'] && $new_role !== 'admin') {
                $_SESSION['flash_error'] = 'You cannot change your own role.';
            } else {
                $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $user_id]);
                $_SESSION['flash_success'] = 'User role updated successfully.';
            }
        }
    }

    if (isset($_POST['toggle_active'], $_POST['user_id'])) {
        $user_id = (int) $_POST['user_id'];
        $error   = toggle_user_active($db, $user_id, (int) $_SESSION['user_id']);
        $_SESSION[$error ? 'flash_error' : 'flash_success'] = $error ?? 'User status updated successfully.';
    }

    header('Location: admin_users.php');
    exit;
}

$users = $db->query(
    "SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/partials/header.php';
?>

<main>
    <div class="admin-header">
        <h1>Manage Users</h1>
        <a href="admin.php" class="back-link">&larr; Back to Dashboard</a>
    </div>

    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user):
                    $is_deactivated = str_starts_with($user['email'], '!deactivated_');
                    $display_email  = $is_deactivated
                        ? substr($user['email'], strlen('!deactivated_')) . ' (deactivated)'
                        : $user['email'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td class="cell-muted"><?= htmlspecialchars($display_email) ?></td>
                        <td>
                            <span class="badge-role <?= $user['role'] ?>">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-status <?= $is_deactivated ? 'inactive' : 'active' ?>">
                                <?= $is_deactivated ? 'Inactive' : 'Active' ?>
                            </span>
                        </td>
                        <td class="cell-actions">
                            <?php if (!$is_deactivated): ?>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="new_role" class="role-select" onchange="this.form.submit()">
                                        <option value="member"  <?= $user['role'] === 'member'  ? 'selected' : '' ?>>Member</option>
                                        <option value="trainer" <?= $user['role'] === 'trainer' ? 'selected' : '' ?>>Trainer</option>
                                        <option value="admin"   <?= $user['role'] === 'admin'   ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="change_role" value="1">
                                </form>
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="toggle_active" class="btn-small btn-danger">Deactivate</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="toggle_active" class="btn-small btn-success">Reactivate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
