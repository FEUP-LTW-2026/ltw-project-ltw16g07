<?php 
  declare(strict_types = 1); 
?>

<?php // Caso o treinador não seja encontrado na BD ?>
<?php function drawTrainerNotFoundError(string $trainer_name) { ?>
    <main class="trainer-error">
        <h1>Trainer Dashboard</h1>
        <p class="trainer-error-msg">
            No trainer profile found for "<?= h($trainer_name) ?>".
            Please contact an admin to link your account to a trainer profile.
        </p>
        <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    </main>
<?php } ?>

<?php // Bloco principal do Dashboard ?>
<?php function drawTrainerDashboard(array $trainer, array $specialties_arr, array $pt_sessions, array $classes, array $rosters) { ?>
<main class="trainer-page">
    <div class="page-header page-header--row">
        <div class="page-header-title">
            <h1>Trainer Dashboard</h1>
            <p>Welcome, <?= h($trainer['name']) ?></p>
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
                    <span class="tag"><?= h(trim($spec)) ?></span>
                <?php endforeach; ?>
            </div>
            <p class="trainer-bio"><?= h($trainer['bio']) ?></p>
        </div>

        <form method="POST" action="../actions/trainer_dashboard.php" id="profile-edit-form" style="display: none;">
            <label class="form-label" for="spec-input">Specialties (comma-separated)</label>
            <input type="text" name="specialties" id="spec-input" class="form-input"
                   value="<?= h($trainer['specialties']) ?>">

            <label class="form-label" for="bio-textarea">Bio</label>
            <textarea name="bio" id="bio-textarea" class="form-input form-textarea"><?= h($trainer['bio']) ?></textarea>

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
                            <td><strong><?= h($session['first_name'] . ' ' . $session['last_name']) ?></strong></td>
                            <td class="cell-muted"><?= h($session['email']) ?></td>
                            <td class="cell-muted"><?= date('M j, Y', strtotime($session['booking_date'])) ?></td>
                            <td><span class="pt-badge-slot"><?= h($session['time_slot']) ?></span></td>
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
                        <h3 class="trainer-class-title"><?= h($class['title']) ?></h3>
                        <p class="trainer-class-meta">
                            <?= h($class['day_of_week']) ?> &middot;
                            <?= h($class['time_start']) ?> &ndash; <?= h($class['time_end']) ?> &middot;
                            <?= h($class['location']) ?>
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
                                    <td><?= h($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                    <td class="cell-muted"><?= h($member['email']) ?></td>
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

<?php } ?>