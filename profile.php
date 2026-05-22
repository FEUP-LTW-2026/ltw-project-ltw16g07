<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_page = 'profile';
$extra_css = 'profile.css';
$page_title = 'My Profile';

$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['cancel_booking'];

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("UPDATE classes SET booked_spots = booked_spots - 1 WHERE id = (SELECT class_id FROM bookings WHERE id = ?)");
        $stmt->execute([$booking_id]);

        $stmt = $db->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
    }

    header('Location: profile.php');
    exit;
}

$stmt = $db->prepare("
    SELECT b.id as booking_id, b.created_at as booked_date,
           c.id as class_id, c.title, c.day_of_week, c.time_start, c.time_end,
           c.location, c.total_spots, c.booked_spots,
           t.name as trainer_name
    FROM bookings b
    JOIN classes c ON b.class_id = c.id
    JOIN trainers t ON c.trainer_id = t.id
    WHERE b.user_id = ?
    ORDER BY
        CASE c.day_of_week
            WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2
            WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4
            WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
            WHEN 'Sunday' THEN 7
        END, c.time_start
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/partials/header.php';
?>

<main class="profile-page">
    <section class="profile-card">
        <div class="profile-avatar"><?= htmlspecialchars($_SESSION['user_initials']) ?></div>
        <h2><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
        <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
        <p class="profile-member">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
        <p class="profile-bookings-count"><?= count($bookings) ?> class<?= count($bookings) !== 1 ? 'es' : '' ?> booked</p>
    </section>

    <section class="profile-content">
        <h2>My Bookings</h2>
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <p>You haven't booked any classes yet.</p>
                <a href="classes.php"><button class="btn-primary">Browse Classes</button></a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking):
                $remaining = $booking['total_spots'] - $booking['booked_spots'];
            ?>
                <div class="booking-card">
                    <div class="booking-info">
                        <h3><?= htmlspecialchars($booking['title']) ?></h3>
                        <p class="booking-details">
                            <?= htmlspecialchars($booking['day_of_week']) ?> ·
                            <?= htmlspecialchars($booking['time_start']) ?> – <?= htmlspecialchars($booking['time_end']) ?> ·
                            <?= htmlspecialchars($booking['location']) ?>
                        </p>
                        <p class="booking-trainer">with <?= htmlspecialchars($booking['trainer_name']) ?></p>
                        <span class="spots-badge <?= $remaining > 0 ? 'green' : 'red' ?>">
                            <?= $booking['booked_spots'] ?> / <?= $booking['total_spots'] ?> spots
                        </span>
                    </div>
                    <form method="POST" onsubmit="return confirm('Cancel this booking?')">
                        <input type="hidden" name="cancel_booking" value="<?= $booking['booking_id'] ?>">
                        <button type="submit" class="btn-cancel">Cancel</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
