<?php 
  declare(strict_types = 1); 
?>

<?php function drawRegisterHeader(string $page_title) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitHub - <?= h($page_title) ?></title>
    <link rel="icon" type="image/png" href="https://i.imgur.com/WT1OtgQ.png">
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<?php } ?>


<?php function drawRegisterForm(string $error, string $success) { ?>
    <main class="login-box">
        <a href="../index.php"><h2>FitHub</h2></a>
        <p class="subtitle">Create your account</p>

        <?php if ($error !== ''): ?>
            <p class="error-msg"><?= h($error) ?></p>
        <?php endif; ?>
        <?php if ($success !== ''): ?>
            <p class="success-msg"><?= h($success) ?></p>
        <?php endif; ?>

        <form action="../actions/register.php" method="post">
            <?php csrf_input(); ?>
            <input type="text"     name="first_name"       placeholder="First Name"                  required>
            <input type="text"     name="last_name"        placeholder="Last Name"                   required>
            <input type="email"    name="email"            placeholder="Email"                       required>
            <input type="password" name="password"         placeholder="Password (min 6 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password"            required>
            <input type="submit" value="Create Account">
        </form>
        <p class="footer-text">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </main>
<?php } ?>


<?php function drawRegisterFooter() { ?>
</body>
</html>
<?php } ?>