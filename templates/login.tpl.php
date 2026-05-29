<?php 
declare(strict_types = 1); 
?>

<?php function drawLoginPage(string $error, string $page_title, string $extra_css) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitHub - <?= h($page_title) ?></title>
    <link rel="icon" type="image/png" href="https://i.imgur.com/WT1OtgQ.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="<?= h($extra_css) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="login-page-body">

    <main class="login-box">
        <a href="../index.php"><h2>FitHub</h2></a>
        <p class="subtitle">Welcome back</p>

        <?php if ($error !== ''): ?>
            <p class="error-msg"><?= h($error) ?></p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <?php csrf_input(); ?>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required autocomplete="email">
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-primary btn-full-width">Login</button>
        </form>
        
        <p class="footer-text">
            Don't have an account? <a href="register.php">Register</a>
        </p>
    </main>

</body>
</html>
<?php } ?>