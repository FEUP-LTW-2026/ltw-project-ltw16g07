<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FitHub<?= isset($page_title) ? ' - ' . $page_title : '' ?></title>
    <link rel="icon" type="image/png" href="https://i.imgur.com/WT1OtgQ.png">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?= $extra_css ?>">
    <?php endif; ?>
</head>
<body>
    <header>
        <a href="../index.php"><h2>FitHub</h2></a>
        <ul>
            <li><a href="../pages/classes.php" class="<?= ($current_page ?? '') === 'classes' ? 'active' : '' ?>">Classes</a></li>
            <li><a href="../pages/trainers.php" class="<?= ($current_page ?? '') === 'trainers' ? 'active' : '' ?>">Trainers</a></li>
            <li><a href="../pages/equipment.php" class="<?= ($current_page ?? '') === 'equipment' ? 'active' : '' ?>">Equipment</a></li>
        </ul>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="../pages/profile.php" class="user-initials"><?= htmlspecialchars($_SESSION['user_initials']) ?></a>
                <a href="../pages/logout.php"><button type="button">Logout</button></a>
            <?php else: ?>
                <a href="../pages/login.php"><button type="button">Login</button></a>
                <a href="../pages/register.php"><button type="button" class="joinButton">Join</button></a>
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