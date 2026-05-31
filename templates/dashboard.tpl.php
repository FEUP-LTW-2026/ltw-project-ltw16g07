<?php 
declare(strict_types = 1); 
?>

<?php function drawDashboardPage(string $name, string $role, array $stats, array $my_classes, ?array $latest_progress, array $progress_history, array $recent_users) { ?>
<div class="page-header page-header--row">
    <div class="page-header-title">
        <h1>Dashboard</h1>
        <p>Welcome back, <?= h($name) ?></p>
    </div>
    <div class="page-header-actions">
        <?php if ($role === 'member'): ?>
            <a href="classes.php"><button type="button" class="btn-primary">Browse Classes</button></a>
            <a href="profile.php"><button type="button" class="btn-outline">My Profile</button></a>
        <?php elseif ($role === 'trainer'): ?>
            <a href="trainer_dashboard.php"><button type="button" class="btn-primary">My Dashboard</button></a>
            <a href="classes.php"><button type="button" class="btn-outline">View Schedule</button></a>
        <?php elseif ($role === 'admin'): ?>
            <a href="admin.php"><button type="button" class="btn-primary">Admin Panel</button></a>
        <?php endif; ?>
    </div>
</div>

<ul class="estatisticas">
    <?php if ($role === 'member'): ?>
        <li class="stat-accent-green">
            <span class="stat-icon">&#128197;</span>
            <span class="valor"><?= $stats['my_bookings'] ?></span>
            <span class="legenda">My Bookings</span>
        </li>
        <li>
            <span class="stat-icon">&#9654;</span>
            <span class="valor"><?= $stats['available_classes'] ?></span>
            <span class="legenda">Available Classes</span>
        </li>
        <li>
            <span class="stat-icon">&#128100;</span>
            <span class="valor"><?= $stats['trainers'] ?></span>
            <span class="legenda">Expert Trainers</span>
        </li>
        <li>
            <span class="stat-icon">&#9733;</span>
            <span class="valor"><?= $stats['my_bookings'] > 0 ? 'Active' : 'New' ?></span>
            <span class="legenda">Member Status</span>
        </li>
    <?php elseif ($role === 'trainer'): ?>
        <li class="stat-accent-green">
            <span class="stat-icon">&#128218;</span>
            <span class="valor"><?= $stats['my_classes'] ?></span>
            <span class="legenda">My Classes</span>
        </li>
        <li>
            <span class="stat-icon">&#128101;</span>
            <span class="valor"><?= $stats['enrolled'] ?></span>
            <span class="legenda">Members Enrolled</span>
        </li>
        <li>
            <span class="stat-icon">&#9733;</span>
            <span class="valor"><?= $stats['rating'] ?></span>
            <span class="legenda">My Rating</span>
        </li>
        <li>
            <span class="stat-icon">&#128172;</span>
            <span class="valor"><?= $stats['reviews'] ?></span>
            <span class="legenda">Reviews</span>
        </li>
    <?php elseif ($role === 'admin'): ?>
        <li class="stat-accent-green">
            <span class="stat-icon">&#128101;</span>
            <span class="valor"><?= $stats['users'] ?></span>
            <span class="legenda">Total Users</span>
        </li>
        <li>
            <span class="stat-icon">&#128218;</span>
            <span class="valor"><?= $stats['classes'] ?></span>
            <span class="legenda">Classes</span>
        </li>
        <li>
            <span class="stat-icon">&#128188;</span>
            <span class="valor"><?= $stats['equipment'] ?></span>
            <span class="legenda">Equipment Items</span>
        </li>
        <li>
            <span class="stat-icon">&#9989;</span>
            <span class="valor"><?= $stats['bookings'] ?></span>
            <span class="legenda">Total Bookings</span>
        </li>
    <?php endif; ?>
</ul>

<?php if ($role === 'member'): ?>
<div class="member-dashboard-grid">
    <div class="member-column-left">
        <div class="up-classes" style="padding: 0;">
            <h3>My Upcoming Classes</h3>
            <a href="classes.php">Browse all &rarr;</a>
        </div>

        <?php if (empty($my_classes)): ?>
            <div class="empty-dashboard" style="padding: 2.5rem 0;">
                <p>You haven't booked any classes yet.</p>
                <a href="classes.php"><button class="btn-primary">Find a Class</button></a>
            </div>
        <?php else: ?>
            <ul class="lista-aulas-vertical" style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($my_classes as $class):
                    $remaining = $class['total_spots'] - $class['booked_spots'];
                ?>
                    <li class="cartao-aula" style="border-left: 4px solid var(--neon-green);">
                        <p class="titulo"><?= h($class['title']) ?></p>
                        <p class="info"><?= h($class['day_of_week']) ?> · <?= h($class['time_start']) ?> · <?= h($class['location']) ?></p>
                        <p class="treinador">Trainer: <?= h($class['trainer_name']) ?></p>
                        <div class="rodape">
                            <span class="vagas <?= $remaining <= 0 ? 'cheio' : '' ?>">
                                <?= $class['booked_spots'] ?>/<?= $class['total_spots'] ?> spots
                            </span>
                            <form method="POST" action="../actions/profile.php" onsubmit="return confirm('Cancel this booking?')">
                                <?php csrf_input(); ?> 
                                <input type="hidden" name="cancel_booking" value="<?= $class['booking_id'] ?>">
                                <button type="submit" class="btn-cancel-small">Cancel</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="member-column-right">
        <div class="up-classes" style="padding: 0;">
            <h3>Progress Tracker</h3>
        </div>

        <div class="progress-card">
            <?php if ($latest_progress): ?>
                <div class="progress-stats">
                    <div class="progress-stat-box">
                        <span class="progress-stat-val"><?= h($latest_progress['weight']) ?> <small>kg</small></span>
                        <span class="progress-stat-lbl">Current Weight</span>
                    </div>
                    <div class="progress-stat-box">
                        <span class="progress-stat-val">
                            <?= $latest_progress['body_fat'] ? h($latest_progress['body_fat']) . ' <small>%</small>' : '–' ?>
                        </span>
                        <span class="progress-stat-lbl">Body Fat</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-progress-state">
                    <p>No progress data recorded yet. Start tracking your fitness journey!</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($progress_history)): ?>
                <div class="progress-history-section">
                    <h4>Recent Logs</h4>
                    <ul class="progress-history-list">
                        <?php foreach ($progress_history as $log): ?>
                            <li>
                                <span class="log-date"><?= date('M j, Y', strtotime($log['recorded_at'])) ?></span>
                                <span class="log-values">
                                    <strong><?= h($log['weight']) ?> kg</strong>
                                    <?php if ($log['body_fat']): ?>
                                        · <?= h($log['body_fat']) ?>% BF
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php elseif ($role === 'trainer'): ?>
    <div class="up-classes">
        <h3>My Classes This Week</h3>
        <a href="trainer_dashboard.php">Full dashboard &rarr;</a>
    </div>

    <?php if (empty($my_classes)): ?>
        <div class="empty-dashboard">
            <p>No classes assigned to you yet. Contact an admin to set up your schedule.</p>
        </div>
    <?php else: ?>
        <ul class="lista-aulas">
            <?php foreach ($my_classes as $class):
                $remaining = $class['total_spots'] - $class['booked_spots'];
            ?>
                <li class="cartao-aula">
                    <p class="titulo"><?= h($class['title']) ?></p>
                    <p class="info"><?= h($class['day_of_week']) ?> · <?= h($class['time_start']) ?> – <?= h($class['time_end']) ?></p>
                    <p class="treinador"><?= h($class['location']) ?></p>
                    <div class="rodape">
                        <span class="vagas <?= $remaining <= 0 ? 'cheio' : '' ?>">
                            <?= $class['booked_spots'] ?>/<?= $class['total_spots'] ?> enrolled
                        </span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

<?php elseif ($role === 'admin'): ?>
    <div class="admin-shortcuts">
        <a href="admin_users.php" class="shortcut-card">
            <span class="shortcut-icon">&#128101;</span>
            <span class="shortcut-label">Manage Users</span>
            <span class="shortcut-desc">View, edit and deactivate accounts</span>
        </a>
        <a href="admin_classes.php" class="shortcut-card">
            <span class="shortcut-icon">&#128218;</span>
            <span class="shortcut-label">Manage Classes</span>
            <span class="shortcut-desc">Create, edit and remove classes</span>
        </a>
        <a href="admin_equipment.php" class="shortcut-card">
            <span class="shortcut-icon">&#128188;</span>
            <span class="shortcut-label">Manage Equipment</span>
            <span class="shortcut-desc">Update availability and status</span>
        </a>
        <a href="classes.php" class="shortcut-card">
            <span class="shortcut-icon">&#128197;</span>
            <span class="shortcut-label">View Schedule</span>
            <span class="shortcut-desc">Browse the full class schedule</span>
        </a>
    </div>

    <div class="up-classes" style="margin-top: 2rem;">
        <h3>Recent Registrations</h3>
        <a href="admin_users.php">All users &rarr;</a>
    </div>

    <div class="recent-users-table">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_users as $u): ?>
                    <tr>
                        <td><?= h($u['first_name'] . ' ' . $u['last_name']) ?></td>
                        <td><span class="badge-role <?= $u['role'] ?>"><?= h($u['role']) ?></span></td>
                        <td class="muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php } ?>