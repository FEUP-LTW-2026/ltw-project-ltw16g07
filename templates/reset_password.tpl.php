<?php 
  declare(strict_types = 1); 
?>

<?php function drawResetPasswordPage(string $page_title, string $error, string $success, $reset, string $token) { ?>
<!DOCTYPE html>
<html>
<head>
    <title>FitHub - <?= h($page_title) ?></title>
    <link rel="icon" type="image/png" href="https://i.imgur.com/WT1OtgQ.png">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-box">
        <a href="../index.php"><h2>FitHub</h2></a>
        <p class="subtitle">New password</p>

        <?php if ($error !== ''): ?>
            <p class="error-msg"><?= h($error) ?></p>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <p class="success-msg"><?= $success ?></p>
        <?php elseif ($reset): ?>
            <form action="../actions/reset_password.php?token=<?= urlencode($token) ?>" method="post">
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <?php csrf_input(); ?>
                <input type="password" name="password" placeholder="New password (min 6 characters)" required>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                <input type="submit" value="Reset Password">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
<?php } ?>