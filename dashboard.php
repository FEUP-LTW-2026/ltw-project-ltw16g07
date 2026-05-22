<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$current_page = 'home';
$extra_css = 'index.css';

$logged_trainer_id = null;
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'trainer') {
    $stmt = $db->prepare("SELECT id FROM trainers WHERE name = ?");
    $stmt->execute([$_SESSION['user_name']]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);
    $logged_trainer_id = $t ? (int) $t['id'] : null;
}

$stats = [];

$stmt = $db->query("SELECT COUNT(*) as total FROM classes");
$stats['weekly_classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM trainers");
$stats['trainers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM equipment");
$stats['equipment'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT ROUND(AVG(rating), 1) as avg_rating FROM trainers");
$stats['avg_rating'] = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rating'];

$stmt = $db->prepare("
    SELECT c.*, t.name as trainer_name 
    FROM classes c 
    JOIN trainers t ON c.trainer_id = t.id 
    ORDER BY 
        CASE c.day_of_week 
            WHEN 'Monday' THEN 1 
            WHEN 'Tuesday' THEN 2 
            WHEN 'Wednesday' THEN 3 
            WHEN 'Thursday' THEN 4 
            WHEN 'Friday' THEN 5 
            WHEN 'Saturday' THEN 6 
            WHEN 'Sunday' THEN 7 
        END, 
        c.time_start 
    LIMIT 4
");
$stmt->execute();
$upcoming_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/partials/header.php';
?>

<h1>PREMIUM FITNESS</h1>
<h2 class="slogan">Train Smarter.</h2>
<h3 class="gray slogan">Feel Stronger.</h3>
<p class="gray marginLeft">
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        Welcome back, Admin! Manage the system from the <a href="admin.php" style="color: var(--accent);">Admin Panel</a>.
    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'trainer'): ?>
        Welcome back, Trainer! View your <a href="trainer_dashboard.php" style="color: var(--accent);">Trainer Dashboard</a>.
    <?php else: ?>
        Book classes, track progress, and connect with expert trainers.
    <?php endif; ?>
</p>
<div id="midButtons">
    <a href="classes.php"><button type="button">Browse Classes</button></a>
    <a href="trainers.php"><button type="button">Meet Trainers</button></a>
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="admin.php"><button type="button">Admin Panel</button></a>
    <?php endif; ?>
</div>

<ul id="stats">
    <li>
        <p><?= $stats['weekly_classes'] ?></p>
        <p>Weekly Classes</p>
    </li>
    <li>
        <p><?= $stats['trainers'] ?></p>
        <p>Expert Trainers</p>
    </li>
    <li>
        <p><?= $stats['equipment'] ?>+</p>
        <p>Equipment Items</p>
    </li>
    <li>
        <p><?= $stats['avg_rating'] ?></p>
        <p>Avg. Rating</p>
    </li>
</ul>

<div id="upClasses">
    <p>Upcoming Classes</p>
    <a href="classes.php">See all &rarr;</a>
</div>

<ul id="listClass">
    <?php foreach ($upcoming_classes as $class):
        $is_own_class = $logged_trainer_id && (int) $class['trainer_id'] === $logged_trainer_id;
    ?>
        <li>
            <p><?= htmlspecialchars($class['title']) ?></p>
            <p><?= htmlspecialchars($class['day_of_week']) ?> - <?= htmlspecialchars($class['time_start']) ?> - <?= htmlspecialchars($class['location']) ?></p>
            <p><?= htmlspecialchars($class['trainer_name']) ?></p>
            <div class="classEnroll">
                <?php if ($is_own_class): ?>
                    <p><?= $class['booked_spots'] ?>/<?= $class['total_spots'] ?> spots</p>
                    <span style="color: #0096ff; font-size: 0.8rem;">Your class</span>
                <?php else:
                    $remaining = $class['total_spots'] - $class['booked_spots'];
                    if ($remaining > 0):
                ?>
                    <p><?= $class['booked_spots'] ?>/<?= $class['total_spots'] ?> spots</p>
                    <form method="POST" action="book_class.php">
                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                        <button type="submit">Enroll</button>
                    </form>
                <?php else: ?>
                    <p><?= $class['booked_spots'] ?>/<?= $class['total_spots'] ?> spots</p>
                    <button type="button" disabled>Full</button>
                <?php endif; endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
