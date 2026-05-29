<?php 
declare(strict_types = 1); 
?>

<?php function drawNotificationsList(array $notifications, array $icons) { ?>
<main class="notifications-page">
    <header class="notif-header">
        <div class="notif-title-group">
            <h1>Notifications</h1>
            <p class="notif-subtitle">Stay updated with your fitness journey</p>
        </div>
        <?php if (!empty($notifications)): ?>
            <form method="POST">
                <?php csrf_input(); ?>
                <button type="submit" name="clear_all" class="btn-outline">Clear all</button>
            </form>
        <?php endif; ?>
    </header>

    <?php if (empty($notifications)): ?>
        <section class="empty-state-card">
            <span class="empty-icon" aria-hidden="true">🔔</span>
            <h3>All caught up!</h3>
            <p>You don't have any notifications at the moment.</p>
            <a href="dashboard.php" class="btn-primary">Back to Dashboard</a>
        </section>
    <?php else: ?>
        <section class="notif-list">
            <?php foreach ($notifications as $n): ?>
                <article class="notif-item <?= $n['is_read'] ? '' : 'notif-unread' ?>" data-type="<?= h($n['type'] ?? 'general') ?>">
                    <span class="notif-icon" aria-hidden="true">
                        <?= $icons[$n['type']] ?? '🔔' ?>
                    </span>
                    
                    <div class="notif-body">
                        <p class="notif-message"><?= h($n['message']) ?></p>
                        <time class="notif-time" datetime="<?= $n['created_at'] ?>">
                            <?= date('M j, Y · H:i', strtotime($n['created_at'])) ?>
                        </time>
                    </div>

                    <form method="POST" class="notif-actions">
                        <?php csrf_input(); ?> 
                        <input type="hidden" name="notif_id" value="<?= $n['id'] ?>">
                        <button type="submit" name="delete_notification" class="btn-danger-small" title="Remove notification" aria-label="Delete">
                            ✕
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php } ?>