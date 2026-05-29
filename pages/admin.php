<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$current_page = 'admin';
$extra_css    = '../css/admin.css';
$page_title   = 'Admin Dashboard';

$stats = [];
$stats['users']     = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['admins']    = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$stats['trainers']  = $db->query("SELECT COUNT(*) FROM trainers")->fetchColumn();
$stats['classes']   = $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$stats['equipment'] = $db->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$stats['bookings']  = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

require_once __DIR__ . '/../templates/header.php';
?>

<main>
    <div class="page-header page-header--row">
        <div class="page-header-title">
            <h1>Admin Dashboard</h1>
        </div>
        <span class="admin-badge">&#9679; Admin</span>
    </div>

    <div class="admin-grid">
        <a href="admin_users.php" class="admin-card">
            <div class="admin-card-icon">&#128101;</div>
            <h3 class="admin-card-title">Manage Users</h3>
            <p class="admin-card-desc">
                <?= $stats['users'] ?> user<?= $stats['users'] !== 1 ? 's' : '' ?>
                &middot; <?= $stats['admins'] ?> admin<?= $stats['admins'] !== 1 ? 's' : '' ?>
            </p>
        </a>

        <a href="admin_classes.php" class="admin-card">
            <div class="admin-card-icon">&#128218;</div>
            <h3 class="admin-card-title">Manage Classes</h3>
            <p class="admin-card-desc">
                <?= $stats['classes'] ?> classe<?= $stats['classes'] !== 1 ? 's' : '' ?>
                &middot; <?= $stats['trainers'] ?> trainer<?= $stats['trainers'] !== 1 ? 's' : '' ?>
            </p>
        </a>

        <a href="admin_equipment.php" class="admin-card">
            <div class="admin-card-icon">&#128188;</div>
            <h3 class="admin-card-title">Manage Equipment</h3>
            <p class="admin-card-desc">
                <?= $stats['equipment'] ?> item<?= $stats['equipment'] !== 1 ? 's' : '' ?>
            </p>
        </a>

        <a href="classes.php" class="admin-card">
            <div class="admin-card-icon">&#128197;</div>
            <h3 class="admin-card-title">View Schedule</h3>
            <p class="admin-card-desc">
                <?= $stats['bookings'] ?> booking<?= $stats['bookings'] !== 1 ? 's' : '' ?>
            </p>
        </a>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
