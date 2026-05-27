<?php
$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $notif_count = (int) $stmt->fetchColumn();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitHub<?= isset($page_title) ? ' - ' . $page_title : '' ?></title>
    <link rel="icon" type="image/png" href="https://i.imgur.com/WT1OtgQ.png">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?= $extra_css ?>">
    <?php endif; ?>
</head>
<body>
    <header>
        <a href="../index.php"><h2>FitHub</h2></a>
        <nav>
            <ul>
                <li><a href="../pages/classes.php" class="<?= ($current_page ?? '') === 'classes' ? 'active' : '' ?>">Classes</a></li>
                <li><a href="../pages/trainers.php" class="<?= ($current_page ?? '') === 'trainers' ? 'active' : '' ?>">Trainers</a></li>
                <li><a href="../pages/equipment.php" class="<?= ($current_page ?? '') === 'equipment' ? 'active' : '' ?>">Equipment</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="../pages/admin.php" class="<?= ($current_page ?? '') === 'admin' ? 'active' : '' ?>">Admin</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'trainer'): ?>
                    <li><a href="../pages/trainer_dashboard.php" class="<?= ($current_page ?? '') === 'trainer_dashboard' ? 'active' : '' ?>">My Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="header-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="../pages/profile.php" class="user-initials"><?= htmlspecialchars($_SESSION['user_initials']) ?></a>
                <a href="../pages/notifications.php" class="notif-bell">
    🔔              <?php if ($notif_count > 0): ?>
                     <span class="notif-badge"><?= $notif_count ?></span>
                   <?php endif; ?>
                </a>
                <a href="../pages/logout.php"><button type="button" class="btn-outline">Logout</button></a>
            <?php else: ?>
                <a href="../pages/login.php"><button type="button" class="btn-outline">Login</button></a>
                <a href="../pages/register.php"><button type="button" class="btn-primary">Join Free</button></a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="flash flash-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="flash flash-error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>