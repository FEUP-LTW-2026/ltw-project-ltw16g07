<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_trainer();

$current_page = 'trainer_dashboard';
$extra_css    = '../css/admin.css';
$page_title   = 'Trainer Dashboard';

$trainer_name = $_SESSION['user_name'] ?? '';

$stmt = $db->prepare("SELECT * FROM trainers WHERE name = ?");
$stmt->execute([$trainer_name]);
$trainer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trainer) {
    require_once __DIR__ . '/../partials/header.php';
    ?>
    <main class="trainer-error">
        <h1>Trainer Dashboard</h1>
        <p class="trainer-error-msg">
            No trainer profile found for "<?= htmlspecialchars($trainer_name) ?>".
            Please contact an admin to link your account to a trainer profile.
        </p>
        <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    </main>
    <?php
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

$trainer_id      = $trainer['id'];
$specialties_arr = explode(',', $trainer['specialties']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $bio         = trim($_POST['bio'] ?? '');
    $specialties = trim($_POST['specialties'] ?? '');
    $db->prepare("UPDATE trainers SET bio = ?, specialties = ? WHERE id = ?")->execute([$bio, $specialties, $trainer_id]);
    $_SESSION['flash_success'] = 'Profile updated successfully.';
    header('Location: trainer_dashboard.php');
    exit;
}

$stmt = $db->prepare("
    SELECT c.* FROM classes c
    WHERE c.trainer_id = ?
    ORDER BY
        CASE c.day_of_week
            WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2
            WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4
            WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
            WHEN 'Sunday' THEN 7
        END, c.time_start
");
$stmt->execute([$trainer_id]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rosters = [];
foreach ($classes as $class) {
    $stmt = $db->prepare("
        SELECT u.first_name, u.last_name, u.email, b.created_at AS booked_date
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.class_id = ?
        ORDER BY b.created_at
    ");
    $stmt->execute([$class['id']]);
    $rosters[$class['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Procurar todas as sessões de Personal Training agendadas com este treinador
$stmt = $db->prepare("
    SELECT pt.id, pt.booking_date, pt.time_slot, u.first_name, u.last_name, u.email
    FROM pt_bookings pt
    JOIN users u ON pt.user_id = u.id
    WHERE pt.trainer_id = ?
    ORDER BY pt.booking_date DESC, pt.time_slot ASC
");
$stmt->execute([$trainer_id]);
$pt_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);


require_once __DIR__ . '/../partials/header.php';
?>

<main class="trainer-page">
    <div class="page-header page-header--row">
        <div class="page-header-title">
            <h1>Trainer Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($trainer['name']) ?></p>
        </div>
        <div class="page-header-actions">
            <span class="badge-role trainer">Trainer</span>
            <span class="trainer-rating">
                &#9733; <?= $trainer['rating'] ?>
                (<?= $trainer['reviews_count'] ?> review<?= $trainer['reviews_count'] !== 1 ? 's' : '' ?>)
            </span>
        </div>
    </div>

    <section class="trainer-card">
        <div class="trainer-card-header">
            <h2>My Profile</h2>
            <button class="btn-small" onclick="toggleEditProfile()">Edit</button>
        </div>

        <div id="profile-display">
            <div class="trainer-tags">
                <?php foreach ($specialties_arr as $spec): ?>
                    <span class="tag"><?= htmlspecialchars(trim($spec)) ?></span>
                <?php endforeach; ?>
            </div>
            <p class="trainer-bio"><?= htmlspecialchars($trainer['bio']) ?></p>
        </div>

        <form method="POST" id="profile-edit-form" style="display: none;">
            <label class="form-label" for="spec-input">Specialties (comma-separated)</label>
            <input type="text" name="specialties" id="spec-input" class="form-input"
                   value="<?= htmlspecialchars($trainer['specialties']) ?>">

            <label class="form-label" for="bio-textarea">Bio</label>
            <textarea name="bio" id="bio-textarea" class="form-input form-textarea"><?= htmlspecialchars($trainer['bio']) ?></textarea>

            <div class="form-actions">
                <button type="submit" name="update_profile" class="btn-small btn-success">Save</button>
                <button type="button" class="btn-small" onclick="toggleEditProfile()">Cancel</button>
            </div>
        </form>
    </section>

    <h2 class="section-title">Your 1-on-1 PT Sessions</h2>
    
    <?php if (empty($pt_sessions)): ?>
        <p class="empty-state">No personal training sessions booked yet.</p>
    <?php else: ?>
        <section class="trainer-class-card">
            <table class="admin-table roster-table">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Scheduled Date</th>
                        <th>Time Slot</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pt_sessions as $session): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($session['first_name'] . ' ' . $session['last_name']) ?></strong></td>
                            <td class="cell-muted"><?= htmlspecialchars($session['email']) ?></td>
                            <td class="cell-muted"><?= date('M j, Y', strtotime($session['booking_date'])) ?></td>
                            <td><span class="pt-badge-slot"><?= htmlspecialchars($session['time_slot']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>

    <h2 class="section-title">My Classes</h2>

    <?php if (empty($classes)): ?>
        <p class="empty-state">No classes assigned to you yet.</p>
    <?php else: ?>
        <?php foreach ($classes as $class):
            $remaining = $class['total_spots'] - $class['booked_spots'];
            $roster    = $rosters[$class['id']] ?? [];
        ?>
            <section class="trainer-class-card">
                <div class="trainer-class-header">
                    <div>
                        <h3 class="trainer-class-title"><?= htmlspecialchars($class['title']) ?></h3>
                        <p class="trainer-class-meta">
                            <?= htmlspecialchars($class['day_of_week']) ?> &middot;
                            <?= htmlspecialchars($class['time_start']) ?> &ndash; <?= htmlspecialchars($class['time_end']) ?> &middot;
                            <?= htmlspecialchars($class['location']) ?>
                        </p>
                    </div>
                    <span class="trainer-class-spots <?= $remaining > 0 ? 'spots-ok' : 'spots-full' ?>">
                        <?= $class['booked_spots'] ?> / <?= $class['total_spots'] ?> spots filled
                    </span>
                </div>

                <h4 class="roster-title">Enrolled Members (<?= count($roster) ?>)</h4>

                <?php if (empty($roster)): ?>
                    <p class="empty-state">No members enrolled yet.</p>
                <?php else: ?>
                    <table class="admin-table roster-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Booked</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roster as $member): ?>
                                <tr>
                                    <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                    <td class="cell-muted"><?= htmlspecialchars($member['email']) ?></td>
                                    <td class="cell-muted"><?= date('M j, Y', strtotime($member['booked_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<script>
    function toggleEditProfile() {
        const display = document.getElementById('profile-display');
        const form    = document.getElementById('profile-edit-form');
        const hidden  = form.style.display === 'none' || form.style.display === '';
        display.style.display = hidden ? 'none' : 'block';
        form.style.display    = hidden ? 'block' : 'none';
    }
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
